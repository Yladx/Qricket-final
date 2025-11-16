<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use App\Models\EventTicketInformation;
use App\Services\XlsxGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificateDeliveryMail;

class EventAttendanceController extends Controller
{
    /**
     * Show attendance management page
     */
    public function attendance(Event $event)
    {
        // Check if user has access to this event (creator, staff, or admin)
        $isAdmin = $this->isAdminSession();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $staff = $event->staff()->where('user_id', Auth::id())->first();
        $isStaff = $staff && ($staff->can_manage_attendance ?? false);
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Get checked-in attendees
        $attendees = EventTicketInformation::where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->with(['user', 'ticketPurchase'])
            ->get();

        // Calculate attendance statistics
        $checkedInTickets = $attendees->count();
        $totalTickets = EventTicketInformation::where('event_id', $event->id)
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->count();
        
        $attendanceRate = $totalTickets > 0 ? ($checkedInTickets / $totalTickets) * 100 : 0;

        // Get all tickets for the event (both checked-in and pending)
        $allTickets = EventTicketInformation::where('event_id', $event->id)
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->with(['user', 'ticketPurchase'])
            ->get()
            ->map(function($ticket) {
                // Get ticket type by matching price
                $ticketType = \App\Models\EventTicket::where('event_id', $ticket->event_id)
                    ->where('price', $ticket->ticketPurchase->unit_price)
                    ->first();
                
                return [
                    'id' => $ticket->id, // Primary key for forms and actions
                    'checked_in' => !is_null($ticket->checked_in_at),
                    'checked_in_at' => $ticket->checked_in_at,
                    'certificate_given' => $ticket->certificate_given ?? false,
                    'ticket_type' => $ticketType->name ?? 'Unknown',
                    'user_name' => trim(($ticket->user->first_name ?? '') . ' ' . ($ticket->user->last_name ?? '')) ?: 'Unknown' ?? 'Unknown',
                    'user_email' => $ticket->user->email ?? '',
                    'user_profile_picture' => $ticket->user->profile_picture_url ?? null,
                    'purchase_id' => $ticket->ticketPurchase->purchase_id ?? '',
                    'ticket_id' => $ticket->ticket_id,
                    'purchase_date' => $ticket->ticketPurchase->created_at ?? $ticket->created_at,
                ];
            });

        // Get unique ticket types for the filter dropdown
        $ticketTypes = $allTickets->pluck('ticket_type')->unique()->values()->toArray();

        // Get total activity count (in case it's needed by shared components)
        $totalActivityCount = $event->activityLogs()->count();

        // Calculate peak attendance data (by date and time)
        $peakAttendanceData = $this->calculatePeakAttendance($event);
        
        // Calculate audience behavior data (check-in time vs event start)
        $audienceBehaviorData = $this->calculateAudienceBehavior($event);

        // Determine layout type
        $isAdmin = $this->isAdminSession();
        $layoutType = $isAdmin ? 'admin' : 'organizer';
        
        // Check if certificate is enabled for this event
        $certificateEnabled = $event->certificate_enabled ?? true;
        
        return view('organizer.events.attendance', compact(
            'event', 
            'attendees', 
            'totalTickets',
            'checkedInTickets',
            'attendanceRate',
            'ticketTypes',
            'totalActivityCount',
            'isAdmin',
            'layoutType',
            'certificateEnabled',
            'peakAttendanceData',
            'audienceBehaviorData'
        ))->with('tickets', $allTickets);
    }

    /**
     * Generate certificates for checked-in attendees
     */
    public function generateCertificates(Request $request, Event $event)
    {
        // Check if user has access to this event (creator, staff, or admin)
        $isAdmin = $this->isAdminSession();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $staff = $event->staff()->where('user_id', Auth::id())->first();
        $isStaff = $staff && ($staff->can_manage_attendance ?? false);
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this event.'
                ], 403);
            }
            abort(403, 'Unauthorized access to this event.');
        }

        try {
            // Get all checked-in attendees
            $checkedInTickets = EventTicketInformation::where('event_id', $event->id)
                ->whereNotNull('checked_in_at')
                ->where('certificate_given', false) // Only generate certificates for those who haven't received them
                ->with(['user', 'ticketPurchase'])
                ->get();

            if ($checkedInTickets->isEmpty()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No checked-in attendees found to generate certificates for.'
                    ]);
                }
                return redirect()->back()->with('error', 'No checked-in attendees found to generate certificates for.');
            }

            $certificatesGenerated = 0;
            $emailsSent = 0;
            $errors = [];

            foreach ($checkedInTickets as $ticket) {
                try {
                    // Mark certificate as given
                    $ticket->update(['certificate_given' => true]);
                $certificatesGenerated++;
                
                // Send email with certificate link
                    if ($ticket->user && $ticket->user->email) {
                    $certificateUrl = route('events.certificate.public', $event) . '?ticket=' . $ticket->ticket_id;

                    // Use the new CertificateDeliveryMail
                    Mail::to($ticket->user->email)->send(new CertificateDeliveryMail($event, $ticket->user, $certificateUrl));
                    
                    $emailsSent++;
                    
                    \Log::info('Certificate email sent', [
                        'event_id' => $event->id,
                        'user_email' => $ticket->user->email,
                        'ticket_id' => $ticket->id
                    ]);
                    } else {
                        $errors[] = "No email address for ticket #{$ticket->ticket_id}";
                    }
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to process certificate for ticket', [
                        'event_id' => $event->id,
                        'ticket_id' => $ticket->id,
                        'user_email' => $ticket->user->email ?? 'N/A',
                        'error' => $e->getMessage()
                    ]);
                    $errors[] = "Failed to process certificate for ticket #{$ticket->ticket_id}: " . $e->getMessage();
                }
            }

            $message = "Generated {$certificatesGenerated} certificates and sent {$emailsSent} emails.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'certificates_generated' => $certificatesGenerated,
                    'emails_sent' => $emailsSent,
                    'errors' => $errors
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Certificate generation failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Failed to generate certificates: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            }
            
            return redirect()->back()->with('error', $errorMessage);
        }
    }

    /**
     * Export attendance data as XLSX with colors
     */
    public function exportAttendance(Event $event)
    {
        // Check if user has access to this event (creator, staff, or admin)
        $isAdmin = $this->isAdminSession();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $staff = $event->staff()->where('user_id', Auth::id())->first();
        $isStaff = $staff && ($staff->can_manage_attendance ?? false);
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        try {
            // Get all tickets for the event
            $tickets = EventTicketInformation::where('event_id', $event->id)
                ->whereHas('ticketPurchase', function($query) {
                    $query->where('status', 'paid');
                })
                ->with(['user', 'ticketPurchase'])
                ->get();

            // Generate XLSX file
            $xlsxFile = XlsxGenerator::generateAttendanceReport($event, $tickets);
            
            // Get filename from the generated file
            $filename = basename($xlsxFile);

            // Return XLS file download
            return response()->download($xlsxFile, $filename, [
                'Content-Type' => 'application/vnd.ms-excel',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('XLSX export failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Export failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Show QR scanner page
     */
    public function scanner(Event $event)
    {
        // Check if user has access to this event (creator, staff, or admin)
        $isAdmin = $this->isAdminSession();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        return view('organizer.events.scanner', compact('event'));
    }

    /**
     * Process QR code scan
     */
    public function processQRScan(Request $request, Event $event)
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

        $request->validate([
            'qr_data' => 'required|string'
        ]);

        try {
            // Parse QR data - handle encrypted format
            $qrData = trim($request->qr_data);
            $ticketInfo = null;
            
            \Log::info('QR code scan attempt', [
                'event_id' => $event->id,
                'qr_data_length' => strlen($qrData),
                'qr_data_preview' => substr($qrData, 0, 100) . '...'
            ]);
            
            // First, try to validate using the new encrypted QR code system
            $ticketInfo = \App\Models\EventTicketInformation::validateQRCode($qrData, $event->id);
            
            if ($ticketInfo) {
                \Log::info('QR code validated successfully with encrypted format', [
                    'event_id' => $event->id,
                    'ticket_id' => $ticketInfo->ticket_id,
                    'ticket_information_id' => $ticketInfo->id
                ]);
            } else {
                \Log::info('Encrypted QR validation failed, trying legacy format', [
                    'event_id' => $event->id,
                    'qr_data_preview' => substr($qrData, 0, 50) . '...'
                ]);
            }
            
            // If not found with encrypted format, try legacy/plain formats (for backward compatibility)
            if (!$ticketInfo) {
                \Log::info('Trying legacy format parsing', [
                    'event_id' => $event->id,
                    'qr_data_preview' => substr($qrData, 0, 100) . '...'
                ]);
                
                // Try to decode as base64 JSON (old format)
                try {
                    $decodedData = json_decode(base64_decode($qrData), true);
                    
                    if ($decodedData && isset($decodedData['ticket_information_id'])) {
                        \Log::info('Legacy QR code decoded successfully', [
                            'ticket_information_id' => $decodedData['ticket_information_id'],
                            'ticket_id' => $decodedData['ticket_id'] ?? 'missing',
                            'event_id' => $decodedData['event_id'] ?? 'missing'
                        ]);
                        
                        // Verify event ID matches
                        if (isset($decodedData['event_id']) && $decodedData['event_id'] !== $event->id) {
                            \Log::warning('Legacy QR code event ID mismatch', [
                                'expected_event_id' => $event->id,
                                'qr_event_id' => $decodedData['event_id']
                            ]);
                            return response()->json([
                                'success' => false,
                                'message' => 'This QR code is for a different event.'
                            ], 400);
                        }
                        
                        // Find ticket by ticket_information_id
                        $ticketInfo = EventTicketInformation::where('id', $decodedData['ticket_information_id'])
                            ->where('event_id', $event->id)
                            ->whereHas('ticketPurchase', function($query) {
                                $query->where('status', 'paid');
                            })
                            ->with(['user', 'ticketPurchase'])
                            ->first();
                            
                        if ($ticketInfo) {
                            \Log::info('Ticket found with legacy format by ticket_information_id', [
                                'event_id' => $event->id,
                                'ticket_id' => $ticketInfo->ticket_id,
                                'ticket_information_id' => $ticketInfo->id,
                                'purchase_status' => $ticketInfo->ticketPurchase->status
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::info('Legacy base64 decode failed, trying simple numeric parsing', [
                        'error' => $e->getMessage()
                    ]);
                }
                
                // If still not found, first try direct ticket_id string (e.g., 36_ABC12345)
                if (!$ticketInfo) {
                    if (preg_match('/^\d+_[A-Za-z0-9]+$/', $qrData)) {
                        // Full format: EVENTID_UNIQUEID
                        $ticketInfo = EventTicketInformation::where('ticket_id', $qrData)
                            ->where('event_id', $event->id)
                            ->whereHas('ticketPurchase', function($query) {
                                $query->where('status', 'paid');
                            })
                            ->with(['user', 'ticketPurchase'])
                            ->first();
                        if ($ticketInfo) {
                            \Log::info('Ticket found by ticket_id direct match', [
                                'event_id' => $event->id,
                                'ticket_id' => $ticketInfo->ticket_id,
                                'ticket_information_id' => $ticketInfo->id,
                            ]);
                        }
                    } elseif (preg_match('/^[A-Z0-9]{8}$/', strtoupper(trim($qrData)))) {
                        // Partial format: Just the unique 8-character identifier (e.g., A1B2C3D4)
                        // Search for tickets ending with this unique ID within the current event
                        $uniqueId = strtoupper(trim($qrData));
                        $ticketInfo = EventTicketInformation::where('event_id', $event->id)
                            ->where('ticket_id', 'like', '%_' . $uniqueId)
                            ->whereHas('ticketPurchase', function($query) {
                                $query->where('status', 'paid');
                            })
                            ->with(['user', 'ticketPurchase'])
                            ->first();
                        if ($ticketInfo) {
                            \Log::info('Ticket found by unique identifier (partial match)', [
                                'event_id' => $event->id,
                                'unique_id' => $uniqueId,
                                'ticket_id' => $ticketInfo->ticket_id,
                                'ticket_information_id' => $ticketInfo->id,
                            ]);
                        }
                    }
                }

                // If still not found, try simple numeric internal ID parsing
                if (!$ticketInfo) {
                    $ticketId = null;
            
            // Extract ticket ID from various QR code formats
            if (strpos($qrData, 'ticket:') === 0) {
                $ticketId = str_replace('ticket:', '', $qrData);
            } elseif (strpos($qrData, 'id=') !== false) {
                $ticketId = substr($qrData, strpos($qrData, 'id=') + 3);
            } elseif (strpos($qrData, 'ticket_id=') !== false) {
                $ticketId = substr($qrData, strpos($qrData, 'ticket_id=') + 10);
            } elseif (preg_match('/\d+/', $qrData, $matches)) {
                // Extract first number found in the string
                $ticketId = $matches[0];
            } else {
                // Assume it's just the ticket ID
                $ticketId = $qrData;
            }
                
                \Log::info('Legacy format parsing', [
                    'extracted_ticket_id' => $ticketId,
                    'original_qr_data' => $qrData
                ]);
            
            // Clean up ticket ID - keep numeric only for internal ID search
            $ticketId = preg_replace('/[^0-9]/', '', $ticketId);
            
            if (empty($ticketId) || !is_numeric($ticketId)) {
                    \Log::warning('Invalid QR code format', [
                        'event_id' => $event->id,
                        'cleaned_ticket_id' => $ticketId,
                        'original_qr_data' => $qrData
                    ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format. Please scan a valid ticket QR code.'
                ], 400);
            }

                // Find the ticket information record by ID (legacy format)
            $ticketInfo = EventTicketInformation::where('id', $ticketId)
                ->where('event_id', $event->id)
                ->whereHas('ticketPurchase', function($query) {
                    $query->where('status', 'paid');
                })
                ->with(['user', 'ticketPurchase'])
                ->first();
                    
                if ($ticketInfo) {
                    \Log::info('Ticket found with legacy format', [
                        'event_id' => $event->id,
                        'ticket_id' => $ticketInfo->ticket_id,
                        'ticket_information_id' => $ticketInfo->id,
                        'purchase_status' => $ticketInfo->ticketPurchase->status
                    ]);
                } else {
                    \Log::warning('Ticket not found with legacy format', [
                        'event_id' => $event->id,
                        'searched_ticket_id' => $ticketId,
                        'total_tickets_for_event' => EventTicketInformation::where('event_id', $event->id)->count(),
                        'paid_tickets_for_event' => EventTicketInformation::where('event_id', $event->id)
                            ->whereHas('ticketPurchase', function($query) {
                                $query->where('status', 'paid');
                            })->count()
                    ]);
                }
                }
            }

            if (!$ticketInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found for this event or payment not completed.'
                ], 404);
            }

            // Check if already checked in
            if ($ticketInfo->checked_in_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket #' . $ticketInfo->ticket_id . ' has already been checked in at ' . $ticketInfo->checked_in_at->format('M j, Y g:i A')
                ], 400);
            }

            // Perform check-in
            $ticketInfo->update([
                'checked_in_at' => now(),
                'checked_in_by' => Auth::id()
            ]);

            // Create database notification record
            $userName = trim(($ticketInfo->user->first_name ?? '') . ' ' . ($ticketInfo->user->last_name ?? '')) ?: 'Unknown';
            $checkInMessage = "You have been checked in to \"{$event->title}\". Thank you for attending!";
            
            try {
                \App\Models\Notification::create([
                    'user_id' => $ticketInfo->user_id,
                    'admin_id' => null,
                    'type' => 'event',
                    'action' => 'attended',
                    'reference_id' => $event->id,
                    'title' => 'Checked In Successfully',
                    'message' => $checkInMessage,
                    'is_read' => false,
                    'data' => [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'ticket_id' => $ticketInfo->id,
                        'ticket_information_id' => $ticketInfo->id,
                        'checked_in_at' => now()->toISOString(),
                        'user_name' => $userName,
                    ],
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create check-in notification', [
                    'user_id' => $ticketInfo->user_id,
                    'event_id' => $event->id,
                    'ticket_id' => $ticketInfo->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Send real-time notification
            \App\Services\NotificationService::sendUserNotification(
                $ticketInfo->user_id,
                $checkInMessage,
                'success',
                'Checked In Successfully'
            );

            // Log the activity
            \App\Models\EventActivityLog::log(
                $event->id,
                $this->getActivityLogUserId(),
                'check_in',
                'QR scanned and checked in ticket #' . $ticketInfo->ticket_id . ' for ' . $userName,
                [
                    'ticket_id' => $ticketInfo->id,
                    'user_name' => $userName,
                    'user_email' => $ticketInfo->user->email,
                    'qr_data' => $qrData
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Successfully checked in ticket #' . $ticketInfo->ticket_id,
                'data' => [
                    'id' => $ticketInfo->id,
                    'ticket_id' => $ticketInfo->ticket_id,
                    'user_name' => trim(($ticketInfo->user->first_name ?? '') . ' ' . ($ticketInfo->user->last_name ?? '')) ?: 'Unknown',
                    'user_email' => $ticketInfo->user->email,
                    'ticket_type' => $ticketInfo->ticketPurchase->ticketType->name ?? 'Unknown',
                    'check_in_time' => $ticketInfo->checked_in_at->format('M j, Y g:i A'),
                    'checked_in_at' => $ticketInfo->checked_in_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('QR scan check-in failed', [
                'event_id' => $event->id,
                'qr_data' => $request->qr_data,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'QR scan failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Manual check-in
     */
    public function manualCheckIn(Request $request, Event $event)
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

        $request->validate([
            'ticket_id' => 'required|integer'
        ]);

        try {
            // Find the ticket information record
            $ticketInfo = EventTicketInformation::where('id', $request->ticket_id)
                ->where('event_id', $event->id)
                ->whereHas('ticketPurchase', function($query) {
                    $query->where('status', 'paid');
                })
                ->with(['user', 'ticketPurchase'])
                ->first();

            if (!$ticketInfo) {
                return redirect()->route('organizer.events.attendance', $event)
                    ->with('error', 'Ticket not found or not paid');
            }

            // Check if already checked in
            if ($ticketInfo->checked_in_at) {
                return redirect()->route('organizer.events.attendance', $event)
                    ->with('error', 'Ticket already checked in');
            }

            // Perform check-in
            $ticketInfo->update([
                'checked_in_at' => now(),
                'checked_in_by' => Auth::id()
            ]);

            // Create database notification record
            $userName = trim(($ticketInfo->user->first_name ?? '') . ' ' . ($ticketInfo->user->last_name ?? '')) ?: 'Unknown';
            $checkInMessage = "You have been checked in to \"{$event->title}\". Thank you for attending!";
            
            try {
                \App\Models\Notification::create([
                    'user_id' => $ticketInfo->user_id,
                    'admin_id' => null,
                    'type' => 'event',
                    'action' => 'attended',
                    'reference_id' => $event->id,
                    'title' => 'Checked In Successfully',
                    'message' => $checkInMessage,
                    'is_read' => false,
                    'data' => [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'ticket_id' => $ticketInfo->id,
                        'ticket_information_id' => $ticketInfo->id,
                        'checked_in_at' => now()->toISOString(),
                        'user_name' => $userName,
                    ],
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create check-in notification', [
                    'user_id' => $ticketInfo->user_id,
                    'event_id' => $event->id,
                    'ticket_id' => $ticketInfo->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Send real-time notification
            \App\Services\NotificationService::sendUserNotification(
                $ticketInfo->user_id,
                $checkInMessage,
                'success',
                'Checked In Successfully'
            );

            // Log the activity
            \App\Models\EventActivityLog::log(
                $event->id,
                $this->getActivityLogUserId(),
                'check_in',
                'Checked in ticket #' . $ticketInfo->ticket_id . ' for ' . $userName,
                [
                    'ticket_id' => $ticketInfo->id,
                    'user_name' => $userName,
                    'user_email' => $ticketInfo->user->email
                ]
            );
            return redirect()->route('organizer.events.attendance', $event)
                ->with('success', "Successfully checked in {$userName} (Ticket #{$ticketInfo->ticket_id})");

        } catch (\Exception $e) {
            \Log::error('Manual check-in failed', [
                'event_id' => $event->id,
                'ticket_id' => $request->ticket_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('organizer.events.attendance', $event)
                ->with('error', 'Check-in failed. Please try again.');
        }
    }

    /**
     * Search attendees
     */
    public function searchAttendees(Request $request, Event $event)
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

        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json(['attendees' => []]);
        }

        try {
            $attendees = EventTicketInformation::where('event_id', $event->id)
                ->whereHas('ticketPurchase', function($queryBuilder) {
                    $queryBuilder->where('status', 'paid');
                })
                ->whereHas('user', function($queryBuilder) use ($query) {
                    $queryBuilder->where('name', 'like', "%{$query}%")
                                ->orWhere('email', 'like', "%{$query}%");
                })
                ->orWhere('ticket_id', 'like', "%{$query}%")
                ->with(['user', 'ticketPurchase'])
                ->limit(10)
                ->get()
                ->map(function($ticket) {
                    return [
                        'id' => $ticket->id,
                        'checked_in' => !is_null($ticket->checked_in_at),
                        'checked_in_at' => $ticket->checked_in_at,
                        'ticket_type' => $ticket->ticketPurchase->ticketType->name ?? 'Unknown',
                        'user_name' => trim(($ticket->user->first_name ?? '') . ' ' . ($ticket->user->last_name ?? '')) ?: 'Unknown' ?? 'Unknown',
                        'user_email' => $ticket->user->email ?? '',
                        'purchase_id' => $ticket->ticketPurchase->purchase_id ?? '',
                        'ticket_id' => $ticket->ticket_id,
                        'purchase_date' => $ticket->ticketPurchase->created_at ?? $ticket->created_at,
                    ];
                });

            return response()->json(['attendees' => $attendees]);

        } catch (\Exception $e) {
            \Log::error('Search attendees failed', [
                'event_id' => $event->id,
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return response()->json(['attendees' => []]);
    }
}

    /**
     * Get the correct user ID for activity logging.
     * Returns null for admin users, regular user ID for regular users.
     */
    private function getActivityLogUserId()
    {
        // If admin is logged in, return null (admin-created events have null user_id)
        if ($this->isAdminSession()) {
            return null;
        }
        
        // Return regular user ID
        return Auth::id();
    }

    /**
     * Calculate peak attendance data grouped by date and time
     */
    private function calculatePeakAttendance(Event $event)
    {
        $checkedInTickets = EventTicketInformation::where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->get();
        
        // Group by date and hour
        $hourlyData = [];
        $dailyData = [];
        
        foreach ($checkedInTickets as $ticket) {
            if (!$ticket->checked_in_at) continue;
            
            $date = $ticket->checked_in_at->format('Y-m-d');
            $hour = $ticket->checked_in_at->format('H:00');
            $dateHourKey = $date . ' ' . $hour;
            
            // Hourly data
            if (!isset($hourlyData[$dateHourKey])) {
                $hourlyData[$dateHourKey] = [
                    'date' => $date,
                    'hour' => $hour,
                    'count' => 0
                ];
            }
            $hourlyData[$dateHourKey]['count']++;
            
            // Daily data
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'date' => $date,
                    'count' => 0
                ];
            }
            $dailyData[$date]['count']++;
        }
        
        // Sort by date and hour
        ksort($hourlyData);
        ksort($dailyData);
        
        return [
            'hourly' => array_values($hourlyData),
            'daily' => array_values($dailyData),
            'labels' => array_map(function($item) {
                return $item['date'] . ' ' . $item['hour'];
            }, array_values($hourlyData)),
            'data' => array_map(function($item) {
                return $item['count'];
            }, array_values($hourlyData)),
        ];
    }
    
    /**
     * Calculate audience behavior based on check-in time vs event start time
     * Returns roadmap-style timeline data
     */
    private function calculateAudienceBehavior(Event $event)
    {
        $checkedInTickets = EventTicketInformation::where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->get();
        
        if (!$event->start_date || $checkedInTickets->isEmpty()) {
            return [
                'timeline_data' => [],
                'labels' => [],
                'checkin_counts' => [],
                'event_start_time' => null,
                'total_checkins' => 0,
            ];
        }
        
        $eventStart = $event->start_date;
        
        // Group check-ins by time intervals (15-minute intervals)
        $timelineData = [];
        $minMinutes = null;
        $maxMinutes = null;
        
        foreach ($checkedInTickets as $ticket) {
            if (!$ticket->checked_in_at) continue;
            
            $checkInTime = $ticket->checked_in_at;
            $minutesDiff = $checkInTime->diffInMinutes($eventStart, false); // negative if before start
            
            // Round to nearest 15-minute interval
            $intervalMinutes = round($minutesDiff / 15) * 15;
            
            if (!isset($timelineData[$intervalMinutes])) {
                $timelineData[$intervalMinutes] = 0;
            }
            $timelineData[$intervalMinutes]++;
            
            if ($minMinutes === null || $intervalMinutes < $minMinutes) {
                $minMinutes = $intervalMinutes;
            }
            if ($maxMinutes === null || $intervalMinutes > $maxMinutes) {
                $maxMinutes = $intervalMinutes;
            }
        }
        
        // Create complete timeline from min to max with 15-minute intervals
        $labels = [];
        $checkinCounts = [];
        
        // Extend range to show at least 2 hours before and 4 hours after event start
        $minRange = min($minMinutes ?? 0, -120); // At least 2 hours before
        $maxRange = max($maxMinutes ?? 0, 240); // At least 4 hours after
        
        for ($minutes = $minRange; $minutes <= $maxRange; $minutes += 15) {
            $count = $timelineData[$minutes] ?? 0;
            
            // Format label based on minutes difference
            if ($minutes < 0) {
                $hours = abs(floor($minutes / 60));
                $mins = abs($minutes % 60);
                if ($hours > 0) {
                    $labels[] = "-{$hours}h {$mins}m";
                } else {
                    $labels[] = "-{$mins}m";
                }
            } elseif ($minutes == 0) {
                $labels[] = 'Event Start';
            } else {
                $hours = floor($minutes / 60);
                $mins = $minutes % 60;
                if ($hours > 0) {
                    $labels[] = "+{$hours}h {$mins}m";
                } else {
                    $labels[] = "+{$mins}m";
                }
            }
            
            $checkinCounts[] = $count;
        }
        
        return [
            'timeline_data' => $timelineData,
            'labels' => $labels,
            'checkin_counts' => $checkinCounts,
            'event_start_time' => $eventStart->format('Y-m-d H:i:s'),
            'event_start_label' => $eventStart->format('M d, Y g:i A'),
            'total_checkins' => $checkedInTickets->count(),
        ];
    }

    /**
     * Robust admin session detector: requires both admin guard and session role.
     * Prevents stale admin login causing organizer views to behave like admin.
     */
    private function isAdminSession(): bool
    {
        if (!Auth::guard('admin')->check()) {
            return false;
        }
        $role = session('admin_role');
        return in_array($role, ['admin', 'superadmin'], true);
    }
}
