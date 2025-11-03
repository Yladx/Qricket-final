<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use App\Models\EventTicketInformation;
use App\Models\TicketPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificateDeliveryMail;

class EventCertificateController extends Controller
{
    /**
     * Generate certificates for checked-in attendees
     */
    public function generateCertificates(Request $request, Event $event)
    {
        // Check if user has access to this event (creator, staff, or admin)
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Get all checked-in tickets for this event
        $checkedInTickets = EventTicketInformation::where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->where('certificate_given', false)
            ->with(['user', 'ticketPurchase'])
            ->get();

        $certificatesGenerated = 0;
        $emailsSent = 0;

        foreach ($checkedInTickets as $ticket) {
            // Mark certificate as given
            $ticket->update(['certificate_given' => true]);
            $certificatesGenerated++;

            // Send email if requested
            if ($request->boolean('send_email', true) && $ticket->user) {
                try {
                    $this->sendCertificateEmail($ticket, $event);
                    $emailsSent++;
                } catch (\Exception $e) {
                    \Log::warning('Failed to send certificate email', [
                        'ticket_id' => $ticket->id,
                        'user_id' => $ticket->user_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Generated {$certificatesGenerated} certificates and sent {$emailsSent} emails.",
            'certificates_generated' => $certificatesGenerated,
            'emails_sent' => $emailsSent
        ]);
    }

    /**
     * Public certificate page - accessible without login using ticket ID
     */
    public function publicCertificatePage(Request $request, Event $event)
    {
        $ticketId = $request->get('ticket');
        
        if (!$ticketId) {
            abort(404, 'Ticket not found.');
        }

        // Find the ticket
        $ticket = EventTicketInformation::where('ticket_id', $ticketId)
            ->where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->where('certificate_given', true)
            ->with(['user', 'ticketPurchase'])
            ->first();

        if (!$ticket) {
            abort(404, 'Certificate not found or ticket not eligible.');
        }

        // Load certificate data
        $certificateData = $this->loadCertificateDataFromR2($event->id);
        
        // Map the data for the template
        $certificateData = $this->mapCertificateData($certificateData);
        
        // Set recipient name
        $userName = trim(($ticket->user->first_name ?? '') . ' ' . ($ticket->user->last_name ?? '')) ?: 'Participant';
        $certificateData['recipient_name'] = $userName;
        
        // Set ticket ID for display
        $certificateData['ticket_id_preview'] = $ticketId;
        
        // Set fallback date - format: "Event Title - Nov 21-23, 2023" or "Event Title - Nov 21, 2023"
        $eventTitle = $event->title ?? '';
        $fallbackWhen = '';
        
        if ($event->start_date) {
            $startDate = \Carbon\Carbon::parse($event->start_date);
            
            if ($event->end_date) {
                $endDate = \Carbon\Carbon::parse($event->end_date);
                
                if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
                    // Same date: "Event Title - held on Nov 21, 2023"
                    $fallbackWhen = $eventTitle . ($eventTitle ? ' - held on ' : 'held on ') . $startDate->format('M j, Y');
                } else {
                    // Different dates: "Event Title - held on Nov 21-23, 2023"
                    $startMonth = $startDate->format('M');
                    $endMonth = $endDate->format('M');
                    
                    if ($startMonth === $endMonth && $startDate->format('Y') === $endDate->format('Y')) {
                        // Same month and year: "Nov 21-23, 2023"
                        $fallbackWhen = $eventTitle . ($eventTitle ? ' - held on ' : 'held on ') . $startDate->format('M j') . '-' . $endDate->format('j, Y');
                    } else {
                        // Different months or years: "Nov 21 - Dec 5, 2023"
                        $fallbackWhen = $eventTitle . ($eventTitle ? ' - held on ' : 'held on ') . $startDate->format('M j') . ' - ' . $endDate->format('M j, Y');
                    }
                }
            } else {
                // Only start date: "Event Title - held on Nov 21, 2023"
                $fallbackWhen = $eventTitle . ($eventTitle ? ' - held on ' : 'held on ') . $startDate->format('M j, Y');
            }
        }
        
        // Set fallback for location - show complete location text
        $fallbackGiven = $event->location ?? '';

        return view('events.certificate-clean', compact(
            'event', 
            'certificateData', 
            'fallbackWhen', 
            'fallbackGiven'
        ));
    }

    /**
     * User certificate page - requires authentication
     */
    public function userCertificatePage(Request $request, Event $event)
    {
        // Find all eligible tickets for this user and event
        $tickets = EventTicketInformation::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('checked_in_at')
            ->where('certificate_given', true)
            ->whereHas('ticketPurchase', function($q){ $q->where('status','paid'); })
            ->orderBy('checked_in_at', 'asc')
            ->get(['ticket_id', 'created_at']);

        if ($tickets->isEmpty()) {
            abort(404, 'Certificate not found or ticket not eligible.');
        }

        // If only one ticket, redirect straight to the public certificate page
        if ($tickets->count() === 1) {
            return redirect()->route('events.certificate.public', $event)->withInput(['ticket' => $tickets->first()->ticket_id]);
        }

        // Multiple eligible tickets – show selection page
        return view('events.certificate-select', [
            'event' => $event,
            'tickets' => $tickets,
        ]);
    }

    /**
     * Stream certificate as PDF (for download)
     */
    public function userStreamCertificate(Request $request, Event $event)
    {
        // Find user's checked-in ticket for this event
        $ticket = EventTicketInformation::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('checked_in_at')
            ->where('certificate_given', true)
            ->with(['user', 'ticketPurchase'])
            ->first();

        if (!$ticket) {
            abort(404, 'Certificate not found or ticket not eligible.');
        }

        // For now, redirect to the certificate page
        // TODO: Implement PDF generation if needed
        return redirect()->route('events.certificate.view', $event);
    }

    /**
     * Send certificate email to ticket holder
     */
    private function sendCertificateEmail(EventTicketInformation $ticket, Event $event)
    {
        if (!$ticket->user) {
            return;
        }

        $publicCertUrl = route('events.certificate.public', $event) . '?ticket=' . $ticket->ticket_id;

        // Use the new CertificateDeliveryMail
        Mail::to($ticket->user->email)->send(new CertificateDeliveryMail($event, $ticket->user, $publicCertUrl));

        \Log::info('Certificate email sent', [
            'ticket_id' => $ticket->id,
            'user_email' => $ticket->user->email,
            'certificate_url' => $publicCertUrl
        ]);
    }

    /**
     * Load certificate data from R2 storage
     */
    private function loadCertificateDataFromR2(int $eventId): array
    {
        try {
            $candidates = [
                "events/{$eventId}/certificate/certificate.json",
                "events/{$eventId}/certificate_data.json",
                "events/{$eventId}/certificate/certificate_data.json",
                "events/{$eventId}/certificate.json",
            ];
            
            foreach ($candidates as $path) {
                if (Storage::disk('r2')->exists($path)) {
                    $json = Storage::disk('r2')->get($path);
                    $data = json_decode($json, true);
                    
                    if (is_array($data)) {
                        return $this->mapCertificateData($data);
                    }
                }
            }
            
            return $this->getDefaultCertificateData();
        } catch (\Throwable $e) {
            \Log::warning('loadCertificateDataFromR2 failed', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
            return $this->getDefaultCertificateData();
        }
    }

    /**
     * Map certificate data to match template expectations
     */
    private function mapCertificateData(array $data): array
    {
        // Map preview_text to recipient_name if recipient_name is empty
        if (empty($data['recipient_name']) && !empty($data['preview_text'])) {
            $data['recipient_name'] = $data['preview_text'];
        }
        
        // Set default organization lines if they're missing
        if (empty($data['org_line1'])) {
            $data['org_line1'] = '';
        }
        if (empty($data['org_line2'])) {
            $data['org_line2'] = '';
        }
        
        return $data;
    }

    /**
     * Get default certificate data
     */
    private function getDefaultCertificateData(): array
    {
        return [
            'org_line1' => '',
            'org_line2' => '',
            'org_school' => '',
            'recipient_name' => 'John Doe',
            'event_title' => '',
            'event_when' => '',
            'event_given' => '',
            'sig_left_name' => '',
            'sig_left_title' => '',
            'sig_right_name' => '',
            'sig_right_title' => '',
            'theme_primary' => '#0b2f6b',
            'theme_secondary' => '#092557',
            'font_family' => 'Inter',
            'font_size' => 24,
            'font_color' => '#000000',
            'name_x_position' => 100,
            'name_y_position' => 200,
            'preview_text' => 'John Doe',
            'auto_generate_certificates' => false,
            'send_certificates_email' => false,
            'certificate_path' => null,
            'certificate_url' => null,
            'logo_left_url' => null,
            'logo_right_url' => null,
        ];
    }
}
