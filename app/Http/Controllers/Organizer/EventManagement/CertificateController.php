<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class CertificateController extends Controller
{
    private function shouldLog(Event $event): bool
    {
        try {
            return in_array($event->status, ['draft','published','approved','pending_approval','scheduled'], true)
                && $event->created_at
                && $event->created_at->diffInMinutes(now()) >= 5; // skip initial creation window
        } catch (\Throwable $t) {
            return true;
        }
    }

    private function diffCertificate(array $old, array $new): array
    {
        $fields = [
            'font_family','font_size','font_color','name_x_position','name_y_position','preview_text',
            'auto_generate_certificates','send_certificates_email','certificate_path','certificate_url',
            // New step4 fields
            'org_line1','org_line2','org_school','recipient_name','event_title','event_when','event_given',
            'sig_left_name','sig_left_title','sig_right_name','sig_right_title','logo_left_url','logo_right_url',
            'signature_left_path','signature_right_path','certificate_theme'
        ];
        $changes = [];
        foreach ($fields as $f) {
            $ov = $old[$f] ?? null;
            $nv = $new[$f] ?? null;
            if (json_encode($ov) !== json_encode($nv)) {
                $changes[$f] = ['from' => $ov, 'to' => $nv];
            }
        }
        return $changes;
    }

    private function logCertificateEdit(Event $event, int $userId, array $changes, string $fallback='Certificate updated'): void
    {
        if (empty($changes)) return;
        $friendly = [
            'font_family' => 'Font family',
            'font_size' => 'Font size',
            'font_color' => 'Font color',
            'name_x_position' => 'Name X',
            'name_y_position' => 'Name Y',
            'preview_text' => 'Preview text',
            'auto_generate_certificates' => 'Auto-generate',
            'send_certificates_email' => 'Send cert emails',
            'certificate_path' => 'Template path',
            'certificate_url' => 'Template URL',
        ];
        $parts = [];
        foreach ($changes as $field => $diff) {
            $label = $friendly[$field] ?? ucfirst(str_replace('_',' ', $field));
            $from = is_scalar($diff['from'] ?? null) ? (string)$diff['from'] : json_encode($diff['from'] ?? null);
            $to = is_scalar($diff['to'] ?? null) ? (string)$diff['to'] : json_encode($diff['to'] ?? null);
            $parts[] = "$label $from → $to";
        }
        $description = Str::limit('Updated: '.implode(', ', $parts), 255, '…');
        \App\Models\EventActivityLog::log(
            $event->id,
            $userId,
            'certificate_edited',
            $description,
            [
                'event_title' => $event->title,
                'changes' => $changes,
            ]
        );
    }
    public function step4(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.step1')->with('error', 'Event ID is required.');
        }

        // Load existing event data
        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || !in_array($event->status, ['draft', 'rejected', 'pending_approval']) || (!$isOwner && !$isAdminEvent)) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }

        // Load certificate data from R2
        $certificateData = $this->loadCertificateDataFromR2($eventId);

        // Generate fallback values for event date and location
        $fallbackWhen = $event && $event->start_date 
            ? 'held on ' . \Carbon\Carbon::parse($event->start_date)->format('F j, Y \a\t g:i A') . 
              ($event->location ? ' at ' . $event->location : '') . '.'
            : 'Event Date';
            
        $fallbackGiven = $event && $event->start_date 
            ? 'Given this ' . \Carbon\Carbon::parse($event->start_date)->format('jS') . ' day of ' .
              \Carbon\Carbon::parse($event->start_date)->format('F, Y') . '.'
            : 'Event Location';

        return view('organizer.events.create.step4', compact('event', 'eventId', 'certificateData', 'fallbackWhen', 'fallbackGiven'));
    }

    public function step4Store(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.step1')->with('error', 'Event ID is required.');
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || !in_array($event->status, ['draft', 'rejected', 'pending_approval']) || (!$isOwner && !$isAdminEvent)) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }

        // Build certificate data from request or provided JSON payload
        $existing = $this->loadCertificateDataFromR2($eventId);
        $incoming = [];

        if ($request->filled('certificate_data_json')) {
            $decoded = json_decode($request->input('certificate_data_json'), true);
            if (is_array($decoded)) {
                $incoming = $decoded;
            }
        }

        $incoming = array_merge($incoming, [
            'font_family' => $request->input('font_family', $incoming['font_family'] ?? 'Inter'),
            'font_size' => (int) $request->input('font_size', $incoming['font_size'] ?? 24),
            'font_color' => $request->input('font_color', $incoming['font_color'] ?? '#000000'),
            'name_x_position' => (int) $request->input('name_x_position', $incoming['name_x_position'] ?? 100),
            'name_y_position' => (int) $request->input('name_y_position', $incoming['name_y_position'] ?? 200),
            'preview_text' => $request->input('preview_text', $incoming['preview_text'] ?? 'John Doe'),
            'auto_generate_certificates' => $request->boolean('auto_generate_certificates', $incoming['auto_generate_certificates'] ?? false),
            'send_certificates_email' => $request->boolean('send_certificates_email', $incoming['send_certificates_email'] ?? false),
        ]);

        // Keep path/url if provided in request (e.g., from previous upload)
        if ($request->filled('certificate_path')) {
            $incoming['certificate_path'] = $request->input('certificate_path');
        }
        if ($request->filled('certificate_url')) {
            $incoming['certificate_url'] = $request->input('certificate_url');
        }

        $merged = array_merge($existing, $incoming);
        $this->storeCertificateDataInR2($eventId, $merged);

        // Update certificate_enabled in the event table
        $certificateEnabled = !$request->boolean('skip_certificate', false);
        $event->update(['certificate_enabled' => $certificateEnabled]);

        // Log edits when outside creation window
        if ($this->shouldLog($event)) {
            $changes = $this->diffCertificate($existing, $merged);
            $this->logCertificateEdit($event, Auth::id(), $changes);
        }

        return redirect()->route('organizer.events.create.staff', ['id' => $eventId])
            ->with('success', 'Certificate settings saved successfully!');
    }

    public function uploadCertificate(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return response()->json(['error' => 'Event ID is required.'], 400);
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || !in_array($event->status, ['draft', 'pending_approval']) || (!$isOwner && !$isAdminEvent)) {
            return response()->json(['error' => 'Event not found or not authorized.'], 403);
        }

        $request->validate([
            'certificate_template' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        $file = $request->file('certificate_template');
        $fileName = 'template.pdf';
        $filePath = "events/{$eventId}/certificate/{$fileName}";

        // Replace old file if exists to keep a single authoritative template
        try {
            if (Storage::disk('r2')->exists($filePath)) {
                Storage::disk('r2')->delete($filePath);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to delete existing certificate before upload', ['event_id' => $eventId, 'error' => $e->getMessage()]);
        }

        // Store file in R2
        $storedPath = Storage::disk('r2')->putFileAs(
            "events/{$eventId}/certificate",
            $file,
            $fileName
        );

        // Prefer stable public URL for preview to avoid signed URL auth errors
        $certificateUrl = Storage::disk('r2')->url($storedPath);

        \Log::info('Certificate uploaded successfully', [
            'event_id' => $eventId,
            'file_path' => $storedPath,
            'certificate_url' => $certificateUrl,
            'file_size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName()
        ]);

        // Persist URL and path to certificate_data.json and ensure style/position defaults are present
        $existing = $this->loadCertificateDataFromR2($eventId);
        $existing['certificate_path'] = $storedPath;
        $existing['certificate_url'] = $certificateUrl; // Always persist the public URL (not a temporary/signed one)

        // Initialize defaults if missing so preview pages have full data
        $existing['font_family'] = $existing['font_family'] ?? 'Inter';
        $existing['font_size'] = isset($existing['font_size']) ? (int)$existing['font_size'] : 24;
        $existing['font_color'] = $existing['font_color'] ?? '#000000';
        $existing['name_x_position'] = isset($existing['name_x_position']) ? (int)$existing['name_x_position'] : 100;
        $existing['name_y_position'] = isset($existing['name_y_position']) ? (int)$existing['name_y_position'] : 200;
        $existing['preview_text'] = $existing['preview_text'] ?? 'John Doe';

        $this->storeCertificateDataInR2($eventId, $existing);

        if ($this->shouldLog($event)) {
            $this->logCertificateEdit($event, Auth::id(), [
                'certificate_path' => ['from' => null, 'to' => $storedPath],
                'certificate_url' => ['from' => null, 'to' => $certificateUrl],
            ], 'Certificate template uploaded');
        }

        return response()->json([
            'success' => true,
            'certificate_url' => $certificateUrl,
            'certificate_path' => $storedPath
        ]);
    }

    public function certificatePreview(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.step1')->with('error', 'Event ID is required.');
        }

        // Load existing event data
        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || (!$isOwner && !$isAdminEvent) || !in_array($event->status, ['draft', 'rejected', 'pending_approval'])) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }

        // Load certificate data from R2
        $certificateData = $this->loadCertificateDataFromR2($eventId);
        
        \Log::info('Certificate preview accessed', [
            'event_id' => $eventId,
            'event_status' => $event->status,
            'user_id' => Auth::id(),
            'is_admin' => $isAdmin,
            'certificate_data_keys' => array_keys($certificateData),
            'has_certificate_data' => !empty($certificateData)
        ]);

        // Prepare event data for the preview
        $eventData = [
            'title' => $event->title,
            'description' => $event->description,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'certificate_path' => $certificateData['certificate_path'] ?? null,
            'certificate_url' => $certificateData['certificate_url'] ?? null,
            'name_x_position' => $certificateData['name_x_position'] ?? 100,
            'name_y_position' => $certificateData['name_y_position'] ?? 200,
            'font_family' => $certificateData['font_family'] ?? 'Arial',
            'font_size' => $certificateData['font_size'] ?? 24,
            'font_color' => $certificateData['font_color'] ?? '#000000',
        ];

        // Prepare fallback data
        $fallbackWhen = $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('M j, Y') : 'Date TBD';
        $fallbackGiven = $event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('M j, Y') : 'Date TBD';

        return view('organizer.events.preview.certificate-preview', compact('eventData', 'eventId', 'certificateData', 'event', 'fallbackWhen', 'fallbackGiven'));
    }

    // Standalone print-only page that renders just the certificate template
    public function certificatePrint(Request $request)
    {
        $eventId = (int) $request->get('id');
        if (!$eventId) {
            abort(400, 'Event ID is required.');
        }

        $event = Event::find($eventId);
        $isAdmin = \Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === \Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        if (!$event || (!$isOwner && !$isAdminEvent)) {
            abort(403, 'Not authorized');
        }

        $certificateData = $this->loadCertificateDataFromR2($eventId);

        $fallbackWhen = $event && $event->start_date 
            ? 'held on ' . \Carbon\Carbon::parse($event->start_date)->format('F j, Y \\a\\t g:i A') .
              ($event->location ? ' at ' . $event->location : '') . '.'
            : 'Event Date';
        $fallbackGiven = $event && $event->start_date 
            ? 'Given this ' . \Carbon\Carbon::parse($event->start_date)->format('jS') . ' day of ' .
              \Carbon\Carbon::parse($event->start_date)->format('F, Y') . '.'
            : 'Event Location';

        return view('organizer.events.preview.certificate-print', [
            'event' => $event,
            'eventId' => $eventId,
            'certificateData' => $certificateData,
            'fallbackWhen' => $fallbackWhen,
            'fallbackGiven' => $fallbackGiven,
        ]);
    }

    public function removeCertificate(Request $request)
    {
        $eventId = $request->get('id');
        if (!$eventId) {
            return response()->json(['success' => false, 'message' => 'Event ID is required.'], 400);
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || !in_array($event->status, ['draft', 'pending_approval']) || (!$isOwner && !$isAdminEvent)) {
            return response()->json(['success' => false, 'message' => 'Event not found or not authorized.'], 403);
        }

        try {
            $path = "events/{$eventId}/certificate_template.pdf";
            if (Storage::disk('r2')->exists($path)) {
                Storage::disk('r2')->delete($path);
            }

            $data = $this->loadCertificateDataFromR2($eventId);
            unset($data['certificate_path'], $data['certificate_url']);
            $this->storeCertificateDataInR2($eventId, $data);

            if ($this->shouldLog($event)) {
                $this->logCertificateEdit($event, Auth::id(), [
                    'certificate_path' => ['from' => "events/{$eventId}/certificate_template.pdf", 'to' => null],
                    'certificate_url' => ['from' => 'set', 'to' => null],
                ], 'Certificate template removed');
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Failed to remove certificate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to remove certificate.'], 500);
        }
    }

    public function autosaveCertificate(Request $request)
    {
        $eventId = $request->get('id');
        if (!$eventId) {
            return response()->json(['success' => false, 'message' => 'Event ID is required.'], 400);
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || (!$isOwner && !$isAdminEvent)) {
            return response()->json(['success' => false, 'message' => 'Event not found or not authorized.'], 403);
        }

        // Accept either discrete fields or a JSON blob
        $incoming = $request->has('certificate_data') && is_array($request->certificate_data)
            ? $request->certificate_data
            : json_decode($request->input('certificate_data_json', '{}'), true);

        if (!is_array($incoming)) { $incoming = []; }

        // Normalize and whitelist keys (base)
        $payload = [
            'font_family' => $incoming['font_family'] ?? $request->input('font_family'),
            'font_size' => isset($incoming['font_size']) ? (int)$incoming['font_size'] : (int)$request->input('font_size', 24),
            'font_color' => $incoming['font_color'] ?? $request->input('font_color', '#000000'),
            'name_x_position' => isset($incoming['name_x_position']) ? (int)$incoming['name_x_position'] : (int)$request->input('name_x_position', 100),
            'name_y_position' => isset($incoming['name_y_position']) ? (int)$incoming['name_y_position'] : (int)$request->input('name_y_position', 200),
            'preview_text' => $incoming['preview_text'] ?? $request->input('preview_text', 'John Doe'),
            // Step4 fields
            'org_line1' => $incoming['org_line1'] ?? $request->input('org_line1'),
            'org_line2' => $incoming['org_line2'] ?? $request->input('org_line2'),
            'org_school' => $incoming['org_school'] ?? $request->input('org_school'),
            'recipient_name' => $incoming['recipient_name'] ?? $request->input('recipient_name'),
            'event_title' => $incoming['event_title'] ?? $request->input('event_title'),
            'event_when' => $incoming['event_when'] ?? $request->input('event_when'),
            'event_given' => $incoming['event_given'] ?? $request->input('event_given'),
            // Theme colors
            'theme_primary' => $incoming['theme_primary'] ?? $request->input('theme_primary'),
            'theme_secondary' => $incoming['theme_secondary'] ?? $request->input('theme_secondary'),
            'theme_border' => $incoming['theme_border'] ?? $request->input('theme_border'),
            // legacy keys kept for backward compatibility
            'sig_left_name' => $incoming['sig_left_name'] ?? $request->input('sig_left_name'),
            'sig_left_title' => $incoming['sig_left_title'] ?? $request->input('sig_left_title'),
            'sig_right_name' => $incoming['sig_right_name'] ?? $request->input('sig_right_name'),
            'sig_right_title' => $incoming['sig_right_title'] ?? $request->input('sig_right_title'),
        ];

        // Dynamic signatories v2 (1..4)
        // Only include signatory data that exists in incoming - this ensures deletions are respected
        for ($i = 1; $i <= 4; $i++) {
            $nameKey = "sig_{$i}_name";
            $titleKey = "sig_{$i}_title";
            $sigPathKey = "sig_{$i}_signature";
            if (array_key_exists($nameKey, $incoming) || $request->filled($nameKey)) {
                $payload[$nameKey] = $incoming[$nameKey] ?? $request->input($nameKey);
            }
            if (array_key_exists($titleKey, $incoming) || $request->filled($titleKey)) {
                $payload[$titleKey] = $incoming[$titleKey] ?? $request->input($titleKey);
            }
            if (array_key_exists($sigPathKey, $incoming) || $request->filled($sigPathKey)) {
                $payload[$sigPathKey] = $incoming[$sigPathKey] ?? $request->input($sigPathKey);
            }
        }

        // Optional template references
        if (!empty($incoming['certificate_path']) || $request->filled('certificate_path')) {
            $payload['certificate_path'] = $incoming['certificate_path'] ?? $request->input('certificate_path');
        }
        if (!empty($incoming['certificate_url']) || $request->filled('certificate_url')) {
            $payload['certificate_url'] = $incoming['certificate_url'] ?? $request->input('certificate_url');
        }

        $existing = $this->loadCertificateDataFromR2($eventId);
        
        // Remove ALL old signatory fields (1-4) from existing data before merging
        // This ensures deleted signatories are completely removed from R2 storage
        for ($i = 1; $i <= 4; $i++) {
            unset($existing["sig_{$i}_name"]);
            unset($existing["sig_{$i}_title"]);
            unset($existing["sig_{$i}_signature"]);
        }
        
        // Also remove legacy signatory fields
        unset($existing['sig_left_name']);
        unset($existing['sig_left_title']);
        unset($existing['sig_right_name']);
        unset($existing['sig_right_title']);
        unset($existing['signature_left_path']);
        unset($existing['signature_right_path']);
        
        $filteredPayload = array_filter($payload, function($v){ return $v !== null && $v !== ''; });
        
        // Merge: existing (with signatories removed) + new payload (only active signatories)
        $merged = array_merge($existing, $filteredPayload);
        
        // Final cleanup: explicitly remove any signatory fields that are empty/null in payload
        // This handles cases where a signatory was explicitly set to empty
        for ($i = 1; $i <= 4; $i++) {
            $nameKey = "sig_{$i}_name";
            $titleKey = "sig_{$i}_title";
            $sigPathKey = "sig_{$i}_signature";
            
            // If none of the fields have data, remove all of them
            if (empty($merged[$nameKey]) && empty($merged[$titleKey]) && empty($merged[$sigPathKey])) {
                unset($merged[$nameKey]);
                unset($merged[$titleKey]);
                unset($merged[$sigPathKey]);
            }
        }
        
        $this->storeCertificateDataInR2($eventId, $merged);

        // Update certificate_enabled in the event table if provided
        if ($request->has('certificate_enabled')) {
            $rawValue = $request->input('certificate_enabled');
            // Convert string '0'/'1' or boolean to proper boolean
            $certificateEnabled = ($rawValue === '1' || $rawValue === 1 || $rawValue === true);
            
            // Force update the event table
            $event->certificate_enabled = $certificateEnabled;
            $event->save();
            
            // Verify the update worked
            $updatedEvent = Event::find($eventId);
            
            \Log::info('Certificate autosave - Updated certificate_enabled', [
                'event_id' => $eventId,
                'raw_value' => $rawValue,
                'converted_value' => $certificateEnabled,
                'event_certificate_enabled_before' => $event->getOriginal('certificate_enabled'),
                'event_certificate_enabled_after' => $updatedEvent->certificate_enabled,
                'update_successful' => $updatedEvent->certificate_enabled === $certificateEnabled
            ]);
        }

        if ($this->shouldLog($event)) {
            $changes = $this->diffCertificate($existing, $merged);
            $this->logCertificateEdit($event, Auth::id(), $changes);
        }

        return response()->json(['success' => true]);
    }

    public function getCertificateStatus(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return response()->json(['success' => false, 'message' => 'Event ID is required.'], 400);
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || (!$isOwner && !$isAdminEvent)) {
            return response()->json(['success' => false, 'message' => 'Event not found or not authorized.'], 403);
        }

        $certificateEnabled = $event->certificate_enabled ?? true;
        
        return response()->json([
            'success' => true,
            'certificate_enabled' => $certificateEnabled
        ]);
    }

    public function updateCertificateStatus(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return response()->json(['success' => false, 'message' => 'Event ID is required.'], 400);
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || (!$isOwner && !$isAdminEvent)) {
            return response()->json(['success' => false, 'message' => 'Event not found or not authorized.'], 403);
        }

        $certificateEnabled = $request->boolean('certificate_enabled', true);
        
        $event->update(['certificate_enabled' => $certificateEnabled]);
        
        return response()->json([
            'success' => true,
            'certificate_enabled' => $certificateEnabled
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'side' => 'required|in:left,right',
            'image' => 'required|image|max:3072',
        ]);
        $eventId = (int) $request->input('id');
        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || (!$isOwner && !$isAdminEvent)) {
            return response()->json(['success' => false, 'message' => 'Not authorized'], 403);
        }
        $filename = $request->input('side') === 'left' ? 'logo_left.png' : 'logo_right.png';
        $path = Storage::disk('r2')->putFileAs("events/{$eventId}/certificate", $request->file('image'), $filename);
        // Serve via Laravel to avoid DNS/CORS problems (absolute URL)
        $url = route('organizer.events.certificate.asset', ['file' => $filename, 'id' => $eventId]);
        $data = $this->loadCertificateDataFromR2($eventId);
        if ($request->input('side') === 'left') {
            $data['logo_left_url'] = $url;
        } else {
            $data['logo_right_url'] = $url;
        }
        $this->storeCertificateDataInR2($eventId, $data);
        return response()->json(['success' => true, 'url' => $url]);
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer',
            'signatory_num' => 'required|integer|min:1|max:4',
            'signature' => 'required|image|max:2048',
        ]);
        
        $eventId = (int) $request->input('event_id');
        $signatoryNum = (int) $request->input('signatory_num');
        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || (!$isOwner && !$isAdminEvent)) {
            return response()->json(['success' => false, 'message' => 'Not authorized'], 403);
        }
        
        try {
            $raw = file_get_contents($request->file('signature')->getRealPath());
            $encrypted = Crypt::encryptString(base64_encode($raw));
            $filename = "signature_{$signatoryNum}.enc";
            $path = "events/{$eventId}/certificate/{$filename}";
            Storage::disk('r2')->put($path, $encrypted);
            
            $data = $this->loadCertificateDataFromR2($eventId);
            $data["sig_{$signatoryNum}_signature"] = $path;
            $this->storeCertificateDataInR2($eventId, $data);
            
            return response()->json([
                'success' => true, 
                'signature_path' => $path,
                'message' => 'Signature uploaded successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Signature upload failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Upload failed'], 500);
        }
    }

    // Stream certificate assets (logos/signatures if image) to avoid external DNS issues
    public function serveAsset(Request $request, string $file)
    {
        $eventId = (int) $request->get('id');
        if (!$eventId) { 
            return response()->json(['error' => 'Event ID is required'], 400);
        }
        
        // Allow specific files and dynamic signature files (signature_1.enc through signature_4.enc)
        $allowed = ['logo_left.png','logo_right.png','template.pdf','signature_left.enc','signature_right.enc'];
        $isDynamicSignature = preg_match('/^signature_\d+\.enc$/', $file);
        
        if (!in_array($file, $allowed, true) && !$isDynamicSignature) { 
            abort(404, 'File not allowed'); 
        }
        
        $path = "events/{$eventId}/certificate/{$file}";
        if (!Storage::disk('r2')->exists($path)) {
            // For images, return a transparent 1x1 PNG instead of 404
            if (in_array($file, ['logo_left.png', 'logo_right.png']) || preg_match('/^signature_\d+\.enc$/', $file)) {
                // Return a transparent 1x1 PNG for missing images
                $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
                return response($transparentPng, 200, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate'
                ]);
            }
            abort(404, 'File not found'); 
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // Decrypt and stream encrypted signatures with correct mime type
        if ($ext === 'enc') {
            $encrypted = Storage::disk('r2')->get($path);
            try {
                $decodedBase64 = \Illuminate\Support\Facades\Crypt::decryptString($encrypted);
                $raw = base64_decode($decodedBase64, true);
                if ($raw === false) { abort(500, 'Failed to decode signature image'); }
                $finfo = new \finfo(\FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($raw) ?: 'application/octet-stream';
                return response($raw, 200, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=31536000'
                ]);
            } catch (\Throwable $e) {
                abort(500, 'Failed to decrypt signature image');
            }
        }

        $mime = match($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
        $stream = Storage::disk('r2')->readStream($path);
        return response()->stream(function() use ($stream) { fpassthru($stream); }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }

    private function storeCertificateDataInR2($eventId, $certificateData)
    {
        try {
            $eventDir = "events/{$eventId}/certificate";
            $certificateDataPath = $eventDir . '/certificate.json';
            
            // Ensure the data is properly formatted
            $formattedData = array_merge($this->getDefaultCertificateData(), $certificateData);
            
            // Store the file - R2 will create the directory structure automatically
            Storage::disk('r2')->put($certificateDataPath, json_encode($formattedData, JSON_PRETTY_PRINT));
            
            \Log::info('Certificate data stored in R2', [
                'path' => $certificateDataPath,
                'event_id' => $eventId,
                'data_keys' => array_keys($formattedData)
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to store certificate data in R2: ' . $e->getMessage(), [
                'event_id' => $eventId,
                'path' => $certificateDataPath ?? 'unknown'
            ]);
        }
    }

    private function loadCertificateDataFromR2($eventId)
    {
        try {
            // Try multiple possible paths for backward compatibility
            $candidates = [
                "events/{$eventId}/certificate/certificate.json",
                "events/{$eventId}/certificate_data.json",
                "events/{$eventId}/certificate/certificate_data.json",
                "events/{$eventId}/certificate.json",
            ];
            
            foreach ($candidates as $certificateDataPath) {
                if (Storage::disk('r2')->exists($certificateDataPath)) {
                    $certificateDataJson = Storage::disk('r2')->get($certificateDataPath);
                    $data = json_decode($certificateDataJson, true) ?? [];
                    \Log::info('Certificate data loaded from R2', ['event_id' => $eventId, 'path' => $certificateDataPath]);
                    return $this->normalizeCertificateUrls((int)$eventId, $data);
                }
            }
            
            // If no file exists, create default data and save it
            \Log::info('No certificate data found, creating default data', ['event_id' => $eventId]);
            $defaultData = $this->getDefaultCertificateData();
            $this->storeCertificateDataInR2($eventId, $defaultData);
            return $this->normalizeCertificateUrls((int)$eventId, $defaultData);
            
        } catch (\Exception $e) {
            \Log::error('Failed to load certificate data from R2: ' . $e->getMessage(), ['event_id' => $eventId]);
            // Return default data even if there's an error
            return $this->getDefaultCertificateData();
        }
    }

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
            'theme_border' => '#0b2f6b',
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
            'signature_left_path' => null,
            'signature_right_path' => null,
        ];
    }

    private function normalizeCertificateUrls(int $eventId, array $data): array
    {
        try {
            // Normalize logo URLs - convert R2 URLs to Laravel routes
            $needsLeft = !empty($data['logo_left_url']) && (str_contains($data['logo_left_url'], 'r2.dev') || str_contains($data['logo_left_url'], 'cloudflarestorage.com') || parse_url($data['logo_left_url'], PHP_URL_SCHEME) === null);
            $needsRight = !empty($data['logo_right_url']) && (str_contains($data['logo_right_url'], 'r2.dev') || str_contains($data['logo_right_url'], 'cloudflarestorage.com') || parse_url($data['logo_right_url'], PHP_URL_SCHEME) === null);
            if ($needsLeft) {
                $data['logo_left_url'] = route('organizer.events.certificate.asset', ['file' => 'logo_left.png', 'id' => $eventId]);
            }
            if ($needsRight) {
                $data['logo_right_url'] = route('organizer.events.certificate.asset', ['file' => 'logo_right.png', 'id' => $eventId]);
            }
            
            // Ensure signature paths are preserved (they're stored as paths, not URLs)
            // The template will handle converting paths to routes using the event ID
            // But we need to ensure the paths exist if they're set
            for ($i = 1; $i <= 4; $i++) {
                $sigPathKey = "sig_{$i}_signature";
                // If signature path exists, verify it's valid (path format, not URL)
                if (!empty($data[$sigPathKey]) && str_starts_with($data[$sigPathKey], 'http')) {
                    // If it's a URL, we need to extract the path or mark it as needing route generation
                    // For now, we'll keep the path format - template will handle it
                }
            }
            
            // Also handle legacy signature paths
            if (!empty($data['signature_left_path']) && !str_starts_with($data['signature_left_path'], 'http')) {
                // Path is already correct format
            }
            if (!empty($data['signature_right_path']) && !str_starts_with($data['signature_right_path'], 'http')) {
                // Path is already correct format
            }
        } catch (\Throwable $t) { 
            \Log::warning('Failed to normalize certificate URLs', ['event_id' => $eventId, 'error' => $t->getMessage()]);
        }
        return $data;
    }

    // Proxy/stream the certificate PDF through Laravel to avoid DNS/CORS issues
    public function streamCertificate(Request $request)
    {
        $eventId = $request->get('id');
        if (!$eventId) {
            abort(400, 'Event ID is required.');
        }

        $path = "events/{$eventId}/certificate_template.pdf";
        if (!Storage::disk('r2')->exists($path)) {
            abort(404, 'Certificate not found');
        }

        $stream = Storage::disk('r2')->readStream($path);
        return response()->stream(function() use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="certificate_template.pdf"',
            // Disable caching so newly uploaded templates are always fetched
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}