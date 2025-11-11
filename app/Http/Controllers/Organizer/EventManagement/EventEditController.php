<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventEditController extends Controller
{
    /**
     * Preview event (similar to admin approval but for organizers)
     */
    public function preview(Event $event)
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

        // Get event data for preview with fallback defaults
        // Initialize all variables to ensure they're always defined
        $layoutData = [];
        $ticketTypes = [];
        $ticketSettings = [];
        $staffData = ['members' => [], 'settings' => []];
        $certificateData = [];
        $certificateEnabled = false;
        
        try {
            // Try to load from R2 storage
            $layoutData = $this->loadLayoutDataFromR2($event->id);
            
            // Ensure layoutData is always an array, never null
            if (!is_array($layoutData)) {
                $layoutData = [];
            }
            
            // Load ticket types from database (preferred) or R2 fallback
            try {
                $ticketTypes = $event->eventTickets()->orderBy('id')->get()->map(function($t){
                    return [
                        'id' => $t->id,
                        'name' => $t->name,
                        'price' => $t->price,
                        'description' => $t->description,
                        'quantity' => $t->quantity,
                        'sold_count' => $t->sold_count ?? 0,
                        'is_active' => $t->is_active ?? true,
                        'is_featured' => $t->is_featured ?? false,
                        'popular' => (bool)($t->is_featured ?? false),
                    ];
                })->toArray();
                
                \Log::info('Loaded ticket types from database', [
                    'event_id' => $event->id,
                    'ticket_count' => count($ticketTypes),
                    'tickets' => $ticketTypes
                ]);
            } catch (\Throwable $th) {
                \Log::warning('Failed to load ticket types from DB, falling back to R2', [
                    'event_id' => $event->id, 
                    'error' => $th->getMessage()
                ]);
                $ticketTypes = $this->loadTicketTypesFromR2($event->id);
            }
            
            $ticketSettings = $this->loadTicketSettingsFromR2($event->id);
            
            // Ensure ticketSettings is always an array
            if (!is_array($ticketSettings)) {
                $ticketSettings = [];
            }
            
            // Ensure ticket_colors always exists in ticketSettings
            if (!isset($ticketSettings['ticket_colors']) || empty($ticketSettings['ticket_colors']) || !is_array($ticketSettings['ticket_colors'])) {
                $ticketSettings['ticket_colors'] = [
                    'primary' => '#10b981',
                    'secondary' => '#f59e0b',
                    'accent' => '#059669',
                ];
            }
            
            \Log::info('Loaded ticket settings from R2', [
                'event_id' => $event->id,
                'ticket_settings' => $ticketSettings,
                'ticket_colors' => $ticketSettings['ticket_colors'] ?? 'missing'
            ]);
            
            // Load staff data from database (preferred) or R2 fallback
            try {
                $staffMembers = $event->staff()->with('user:id,first_name,last_name,email')->get()->map(function($staff) {
                    return [
                        'id' => $staff->user_id,
                        'name' => trim(($staff->user->first_name ?? '') . ' ' . ($staff->user->last_name ?? '')) ?: $staff->user->email,
                        'email' => $staff->user->email,
                        'role' => $staff->role,
                        'can_manage_event' => $staff->can_manage_event,
                        'can_manage_staff' => $staff->can_manage_staff,
                        'can_manage_ticket' => $staff->can_manage_ticket,
                        'can_manage_news' => $staff->can_manage_news,
                        'can_manage_attendance' => $staff->can_manage_attendance,
                        'can_manage_sales' => $staff->can_manage_sales,
                    ];
                })->toArray();
                
                $staffSettings = $this->loadStaffSettingsFromR2($event->id);
                
                $staffData = [
                    'members' => $staffMembers,
                    'settings' => $staffSettings
                ];
                
                \Log::info('Loaded staff data from database', [
                    'event_id' => $event->id,
                    'staff_count' => count($staffMembers),
                    'staff_members' => $staffMembers,
                    'staff_settings' => $staffSettings
                ]);
            } catch (\Throwable $th) {
                \Log::warning('Failed to load staff data from DB, falling back to R2', [
                    'event_id' => $event->id, 
                    'error' => $th->getMessage()
                ]);
                $staffData = $this->loadStaffDataFromR2($event->id);
            }
            
            // Load certificate data; consider DB event flag and R2 flag for enablement
            $certificateData = $this->loadCertificateDataFromR2($event->id);
            // Load certificate settings (colors, toggles) and merge over data for preview
            $certificateSettings = $this->loadCertificateSettingsFromR2($event->id);
            if (!empty($certificateSettings)) {
                $certificateData = array_merge($certificateSettings, $certificateData ?? []);
            }
            // Resolve logo paths from R2 to signed/public URLs for preview
            try {
                $disk = \Storage::disk('r2');
                $leftCandidatePaths = [];
                $rightCandidatePaths = [];
                // Common filenames stored by Step 4
                $leftCandidatePaths[] = "events/{$event->id}/certificate/logo_left.png";
                $rightCandidatePaths[] = "events/{$event->id}/certificate/logo_right.png";
                // If paths exist in JSON, respect them
                foreach (['logo_left_path','logo_left'] as $k) {
                    if (!empty($certificateData[$k])) { $leftCandidatePaths[] = $certificateData[$k]; }
                }
                foreach (['logo_right_path','logo_right'] as $k) {
                    if (!empty($certificateData[$k])) { $rightCandidatePaths[] = $certificateData[$k]; }
                }

                $resolvePath = function(array $candidates) use ($disk) {
                    foreach ($candidates as $p) {
                        if (is_string($p)) {
                            if (str_starts_with($p, 'http')) { return $p; }
                            $path = str_contains($p, 'events/') ? $p : null;
                            if ($path && $disk->exists($path)) { return $this->getR2Url($path); }
                        }
                    }
                    return null;
                };

                $leftUrl = $resolvePath($leftCandidatePaths);
                $rightUrl = $resolvePath($rightCandidatePaths);

                if ($leftUrl) { $certificateData['logo_left_url'] = $leftUrl; }
                if ($rightUrl) { $certificateData['logo_right_url'] = $rightUrl; }

            } catch (\Throwable $t) {
                \Log::warning('Failed resolving certificate logo URLs', ['event_id' => $event->id, 'error' => $t->getMessage()]);
            }
            // Only enabled if DB column is explicitly true (respect database setting)
            $certificateEnabled = (bool)($event->certificate_enabled ?? false);
            
            \Log::info('Certificate loading status', [
                'event_id' => $event->id,
                'certificate_enabled' => $certificateEnabled,
                'has_certificate_data' => !empty($certificateData),
                'certificate_data_keys' => array_keys($certificateData ?? [])
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load event data for preview: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'event_status' => $event->status,
                'trace' => $e->getTraceAsString()
            ]);
            // Use empty arrays as fallback - ensure all variables are still initialized
            $layoutData = is_array($layoutData) ? $layoutData : [];
            $ticketTypes = is_array($ticketTypes) ? $ticketTypes : [];
            $ticketSettings = is_array($ticketSettings) ? $ticketSettings : [];
            $staffData = is_array($staffData) && isset($staffData['members']) ? $staffData : ['members' => [], 'settings' => []];
            $certificateData = is_array($certificateData) ? $certificateData : [];
        }

        // Ensure all variables are properly initialized before passing to view
        // This prevents undefined variable errors in the view
        $layoutData = is_array($layoutData) ? $layoutData : [];
        $ticketTypes = is_array($ticketTypes) ? $ticketTypes : [];
        $ticketSettings = is_array($ticketSettings) ? $ticketSettings : [];
        $staffData = is_array($staffData) && isset($staffData['members']) ? $staffData : ['members' => [], 'settings' => []];
        $certificateData = is_array($certificateData) ? $certificateData : [];
        $certificateEnabled = isset($certificateEnabled) ? (bool)$certificateEnabled : false;

        // Log preview data for debugging
        \Log::info('Preview data prepared', [
            'event_id' => $event->id,
            'event_status' => $event->status,
            'has_layout_data' => !empty($layoutData),
            'layout_data_count' => count($layoutData),
            'has_ticket_types' => !empty($ticketTypes),
            'ticket_types_count' => count($ticketTypes),
            'has_staff_data' => !empty($staffData['members']),
            'staff_count' => count($staffData['members'] ?? []),
        ]);

        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.preview.index', compact(
            'event', 
            'layoutData', 
            'ticketTypes', 
            'ticketSettings', 
            'certificateData', 
            'certificateEnabled',
            'staffData',
            'layoutType'
        ));
    }

    /**
     * Edit basic event information
     */
    public function editBasic(Event $event)
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

        // Check if event has ended - if so, show view-only mode
        $eventEnded = $event->end_date && now()->gt($event->end_date);
        if ($eventEnded) {
            $layoutType = $isAdmin ? 'admin' : 'organizer';
            return view('organizer.events.info.basic', compact('event', 'layoutType'));
        }

        // Check if user can manage event or is admin
        // Allow staff members to manage events even without explicit can_manage_event permission
        $isAdmin = Auth::guard('admin')->check();
        $user = Auth::user();
        $isOwner = $user && (int)$event->user_id === (int)$user->id;
        $staff = $event->staff()->where('user_id', $user?->id)->first();
        // Allow any staff member to manage event (not just those with can_manage_event permission)
        $canManageEvent = $isOwner || $isAdminEvent || ($staff !== null) || ($staff?->can_manage_event ?? false) || ($staff?->role === 'organizer');

        // Allow editing if: admin created this event (user_id is null) OR user can manage event OR is staff
        // Block editing if: admin viewing someone else's event OR regular user without access
        if (!$isAdminEvent && !$canManageEvent) {
            $layoutType = $isAdmin ? 'admin' : 'organizer';
            return view('organizer.events.info.basic', compact('event', 'layoutType'));
        }

        // Prepare event data for the form
        $eventData = [
            'title' => $event->title,
            'description' => $event->description,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'location_url' => $event->location_url,
            'max_participants' => $event->max_participants,
            'category' => $event->category,
            'thumbnail_url' => $event->thumbnail_url,
            'status' => $event->status,
            'is_free' => $event->is_free ?? false,
            'tags' => $event->tags,
        ];

        // Determine layout type
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.edit.basic', compact('event', 'eventData', 'isAdmin', 'layoutType'));
    }

    /**
     * Edit event layout
     */
    public function editLayout(Event $event)
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

        // Check if event has ended - if so, show info view instead (clean and working)
        $eventEnded = $event->end_date && $event->end_date->isPast();
        if ($eventEnded) {
            $layoutType = $isAdmin ? 'admin' : 'organizer';
            
            // Load layout data for display only
            $layoutData = null;
            $layoutPath = $event->layout_path ?? null;
            try {
                $layoutData = $this->loadLayoutDataFromR2($event->id);
                \Log::info('Event ended - Layout data loaded for event ' . $event->id, [
                    'event_id' => $event->id,
                    'end_date' => $event->end_date,
                    'layout_path' => $layoutPath,
                    'layout_data_exists' => !empty($layoutData),
                    'layout_data_count' => is_array($layoutData) ? count($layoutData) : 0,
                    'layout_data_preview' => is_array($layoutData) ? array_slice($layoutData, 0, 2) : null
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to load layout data for ended event: ' . $e->getMessage(), [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            return view('organizer.events.info.layout', compact('event', 'layoutData', 'layoutPath', 'layoutType'));
        }

        // Check if event is upcoming (not ended)
        $isUpcoming = !$event->end_date || now()->lt($event->end_date);
        
        // User is already owner (checked above), so allow editing if event is upcoming
        // Also allow admin-created events or staff with manage permissions
        // Include 'rejected' status so users can fix and resubmit
        $canEditLayout = ($isOwner && $isUpcoming && in_array($event->status, ['draft', 'rejected', 'published', 'pending_approval', 'approved'])) 
                        || ($isAdminEvent && $isUpcoming)
                        || ($isStaff && $isUpcoming);
                        
        if (!$canEditLayout) {
            $layoutType = $isAdmin ? 'admin' : 'organizer';
            
            // Load layout data for preview
            $layoutData = null;
            try {
                $layoutData = $this->loadLayoutDataFromR2($event->id);
                \Log::info('Layout data loaded for event ' . $event->id . ': ' . json_encode($layoutData));
            } catch (\Exception $e) {
                \Log::error('Failed to load layout data for preview: ' . $e->getMessage());
            }
            
            return view('organizer.events.info.layout', compact('event', 'layoutData', 'layoutType'));
        }

        // Determine layout type
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        // Load event data for the form
        $eventData = [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'category' => $event->category,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'location_url' => $event->location_url,
            'tags' => $event->tags,
            'poster' => $event->poster,
            'thumbnail' => $event->thumbnail,
            'existing_poster_url' => $event->poster_url,
            'existing_thumbnail_url' => $event->thumbnail_url,
        ];

        // Load layout data from R2 if available
        $layoutData = null;
        $layoutPath = null;
        try {
            if ($event->layout_path) {
                $layoutPath = $event->layout_path;
                $layoutData = $this->loadLayoutDataFromR2($event->id);
                \Log::info('Layout data loaded for event ' . $event->id . ': ' . json_encode($layoutData));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load layout data: ' . $e->getMessage());
        }

        // Load footer.json from R2 for autosaved footer settings
        $footerData = [];
        try {
            $disk = Storage::disk('r2');
            $footerPath = "events/{$event->id}/footer.json";
            if ($disk->exists($footerPath)) {
                $json = $disk->get($footerPath);
                $decoded = json_decode($json, true);
                if (is_array($decoded)) { $footerData = $decoded; }
            }
        } catch (\Throwable $t) {
            \Log::warning('Failed to load footer.json', ['event_id' => $event->id, 'error' => $t->getMessage()]);
        }

        return view('organizer.events.edit.layout', compact('event', 'eventData', 'layoutData', 'layoutPath', 'layoutType', 'footerData'));
    }

    /**
     * Edit event certificate
     */
    public function editCertificate(Event $event)
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

        // Check if event has ended - if so, show view-only mode
        $eventEnded = $event->end_date && now()->gt($event->end_date);
        if ($eventEnded) {
            $layoutType = $isAdmin ? 'admin' : 'organizer';
            
            // Load certificate data for info view
            $certificateData = [];
            try {
                $candidates = [
                    "events/{$event->id}/certificate/certificate.json",
                    "events/{$event->id}/certificate_data.json",
                    "events/{$event->id}/certificate/certificate_data.json",
                    "events/{$event->id}/certificate.json",
                ];
                
                foreach ($candidates as $certificateDataPath) {
                    if (\Storage::disk('r2')->exists($certificateDataPath)) {
                        $certificateDataJson = \Storage::disk('r2')->get($certificateDataPath);
                        $data = json_decode($certificateDataJson, true) ?? [];
                        $certificateData = $this->normalizeCertificateUrls($event->id, $data);
                        break;
                    }
                }
            } catch (\Throwable $t) {
                \Log::warning('Failed to load certificate.json for info view', ['event_id' => $event->id, 'error' => $t->getMessage()]);
            }
            
            // Generate fallback values for event date and location
            $fallbackWhen = $event && $event->start_date 
                ? 'held on ' . \Carbon\Carbon::parse($event->start_date)->format('F j, Y \a\t g:i A') . 
                  ($event->location ? ' at ' . $event->location : '') . '.'
                : 'Event Date';
                
            $fallbackGiven = $event && $event->start_date 
                ? 'Given this ' . \Carbon\Carbon::parse($event->start_date)->format('jS') . ' day of ' .
                  \Carbon\Carbon::parse($event->start_date)->format('F, Y') . '.'
                : 'Event Location';
            
            // Redirect to info view for certificate
            return view('organizer.events.info.certificate', compact('event', 'layoutType', 'certificateData', 'fallbackWhen', 'fallbackGiven'));
        }

        // Load certificate data from R2 storage using the same method as CertificateController
        $certificateData = [];
        try {
            $candidates = [
                "events/{$event->id}/certificate/certificate.json",
                "events/{$event->id}/certificate_data.json",
                "events/{$event->id}/certificate/certificate_data.json",
                "events/{$event->id}/certificate.json",
            ];
            
            foreach ($candidates as $certificateDataPath) {
                if (\Storage::disk('r2')->exists($certificateDataPath)) {
                    $certificateDataJson = \Storage::disk('r2')->get($certificateDataPath);
                    $data = json_decode($certificateDataJson, true) ?? [];
                    \Log::info('Certificate data loaded from R2', ['event_id' => $event->id, 'path' => $certificateDataPath]);
                    $certificateData = $this->normalizeCertificateUrls($event->id, $data);
                    break;
                }
            }
            
            // If no file exists, create default data
            if (empty($certificateData)) {
                \Log::info('No certificate data found, creating default data', ['event_id' => $event->id]);
                $defaultData = $this->getDefaultCertificateData();
                $certificateData = $this->normalizeCertificateUrls($event->id, $defaultData);
            }
        } catch (\Throwable $t) {
            \Log::warning('Failed to load certificate.json', ['event_id' => $event->id, 'error' => $t->getMessage()]);
            $certificateData = $this->getDefaultCertificateData();
        }

        $eventId = $event->id;

        // Generate fallback values for event date and location
        $fallbackWhen = $event && $event->start_date 
            ? 'held on ' . \Carbon\Carbon::parse($event->start_date)->format('F j, Y \a\t g:i A') . 
              ($event->location ? ' at ' . $event->location : '') . '.'
            : 'Event Date';
            
        $fallbackGiven = $event && $event->start_date 
            ? 'Given this ' . \Carbon\Carbon::parse($event->start_date)->format('jS') . ' day of ' .
              \Carbon\Carbon::parse($event->start_date)->format('F, Y') . '.'
            : 'Event Location';

        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.edit.certificate', compact('event', 'certificateData', 'eventId', 'isAdmin', 'layoutType', 'fallbackWhen', 'fallbackGiven'));
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

    /**
     * Normalize certificate URLs to use Laravel routes instead of direct R2 URLs
     */
    private function normalizeCertificateUrls(int $eventId, array $data): array
    {
        try {
            $needsLeft = !empty($data['logo_left_url']) && str_contains($data['logo_left_url'], 'r2.dev');
            $needsRight = !empty($data['logo_right_url']) && str_contains($data['logo_right_url'], 'r2.dev');
            if ($needsLeft) {
                $data['logo_left_url'] = route('organizer.events.certificate.asset', ['file' => 'logo_left.png', 'id' => $eventId]);
            }
            if ($needsRight) {
                $data['logo_right_url'] = route('organizer.events.certificate.asset', ['file' => 'logo_right.png', 'id' => $eventId]);
            }
        } catch (\Throwable $t) { /* ignore */ }
        return $data;
    }

    /**
     * Update event certificate
     */
    public function updateCertificate(Request $request, Event $event)
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

        try {
            // Save certificate data to R2 storage
            $disk = \Storage::disk('r2');
            $certificatePath = "events/{$event->id}/certificate/certificate.json";
            
            $certificateData = $request->except(['_token', '_method']);
            $disk->put($certificatePath, json_encode($certificateData, JSON_PRETTY_PRINT));
            
            return redirect()->back()->with('success', 'Certificate settings saved successfully.');
        } catch (\Throwable $t) {
            \Log::error('Failed to save certificate data', ['event_id' => $event->id, 'error' => $t->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save certificate settings.');
        }
    }

    /**
     * Autosave certificate data
     */
    public function autosaveCertificate(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return response()->json(['success' => false, 'message' => 'Event ID is required'], 400);
        }

        try {
            $event = Event::findOrFail($eventId);
            
            // Check if user has access to this event (admin bypass)
            $hasAccess = Auth::guard('admin')->check() || $event->user_id === Auth::id() || 
                        $event->staff()->where('user_id', Auth::id())->exists();
            
            if (!$hasAccess) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }

            // Save certificate data to R2 storage
            $disk = \Storage::disk('r2');
            $certificatePath = "events/{$eventId}/certificate/certificate.json";
            
            $certificateDataJson = $request->get('certificate_data_json');
            $certificateData = json_decode($certificateDataJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            
            $disk->put($certificatePath, json_encode($certificateData, JSON_PRETTY_PRINT));
            
            return response()->json(['success' => true, 'message' => 'Certificate data saved']);
        } catch (\Throwable $t) {
            \Log::error('Failed to autosave certificate data', ['event_id' => $eventId, 'error' => $t->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to save certificate data'], 500);
        }
    }

    /**
     * Manage event tickets
     */
    public function tickets(Event $event)
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

        // Check if user can manage tickets or is admin
        $isAdmin = Auth::guard('admin')->check();
        $user = Auth::user();
        $isOwner = $user && (int)$event->user_id === (int)$user->id;
        $staff = $event->staff()->where('user_id', $user?->id)->first();
        $canManageTicket = $isAdmin || $isOwner || ($staff?->can_manage_ticket ?? false) || ($staff?->role === 'organizer');

        // If admin or user can't manage tickets, show info view instead
        if ($isAdmin || !$canManageTicket) {
            $layoutType = $isAdmin ? 'admin' : 'organizer';
            
            // Load ticket data for preview
            $ticketTypes = $event->eventTickets()->get()->toArray();
            $ticketSettings = $this->loadTicketSettingsFromR2($event->id);
            
            return view('organizer.events.info.tickets', compact('event', 'ticketTypes', 'ticketSettings', 'layoutType'));
        }

        // Load ticket data from event with purchased count and delete guard
        $ticketTypes = $event->eventTickets()->orderBy('id')->get()->map(function($t){
            $sold = (int)($t->sold_count ?? 0);
            try {
                $sold = max($sold, (int)$t->ticketInformation()->count());
            } catch (\Throwable $e) {}
            return [
                'id' => $t->id,
                'name' => $t->name,
                'description' => $t->description,
                'price' => $t->price,
                'quantity' => $t->quantity,
                'sale_start' => optional($t->sale_start)->format('Y-m-d H:i'),
                'sale_end' => optional($t->sale_end)->format('Y-m-d H:i'),
                'purchased_count' => $sold,
                'can_delete' => $sold === 0,
                'is_active' => (bool)($t->is_active ?? true),
            ];
        })->toArray();
        
        // If event is free, normalize all ticket prices to 0
        if ($event->is_free ?? false) {
            foreach ($ticketTypes as &$ticket) {
                $ticket['price'] = 0;
            }
            unset($ticket);
        }
        
        // Load ticket settings from R2 storage
        $ticketSettings = $this->loadTicketSettingsFromR2($event->id);
        
        $eventData = [
            'ticket_types' => $ticketTypes,
            'ticket_settings' => $ticketSettings,
            'existing_thumbnail_url' => $event->thumbnail_url,
            'title' => $event->title,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'is_free' => $event->is_free ?? false,
        ];

        // Determine layout type
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.tickets', compact('event', 'eventData', 'isAdmin', 'layoutType'));
    }

    /**
     * Manage event staff
     */
    public function staff(Event $event)
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

        // Check if user can manage staff or is admin
        $isAdmin = Auth::guard('admin')->check();
        $user = Auth::user();
        $isOwner = $user && (int)$event->user_id === (int)$user->id;
        $staff = $event->staff()->where('user_id', $user?->id)->first();
        $canManageStaff = $isAdmin || $isOwner || ($staff?->can_manage_staff ?? false) || ($staff?->role === 'organizer');

        // If admin or user can't manage staff, show info view instead
        if ($isAdmin || !$canManageStaff) {
            $layoutType = $isAdmin ? 'admin' : 'organizer';
            return view('organizer.events.info.staff', compact('event', 'layoutType'));
        }

        return view('organizer.events.staff', compact('event'));
    }

    /**
     * Generate QR code
     */
    public function qrCode(Event $event)
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

        return view('organizer.events.qr', compact('event'));
    }

    /**
     * Load staff settings from R2 storage
     */
    private function loadStaffSettingsFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $staffSettingsPath = $eventDir . '/staff_settings.json';
            
            if (Storage::disk('r2')->exists($staffSettingsPath)) {
                $staffSettingsJson = Storage::disk('r2')->get($staffSettingsPath);
                return json_decode($staffSettingsJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load staff settings from R2: ' . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Load ticket settings from R2 storage
     */
    private function loadTicketSettingsFromR2(int $eventId): array
    {
        try {
            $eventDir = "events/{$eventId}";
            $settingsPath = $eventDir . '/ticket_settings.json';
            
            if (\Storage::disk('r2')->exists($settingsPath)) {
                $json = \Storage::disk('r2')->get($settingsPath);
                $data = json_decode($json, true);
                
                if (is_array($data)) {
                    // Ensure ticket_colors are present with defaults if missing
                    if (!isset($data['ticket_colors']) || empty($data['ticket_colors']) || !is_array($data['ticket_colors'])) {
                        $data['ticket_colors'] = [
                            'primary' => '#10b981',
                            'secondary' => '#f59e0b',
                            'accent' => '#059669',
                        ];
                    }
                    // Ensure all color keys exist
                    $defaultColors = [
                        'primary' => '#10b981',
                        'secondary' => '#f59e0b',
                        'accent' => '#059669',
                    ];
                    $data['ticket_colors'] = array_merge($defaultColors, $data['ticket_colors'] ?? []);
                    return $data;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load ticket settings from R2', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }
        
        // Return default settings if file doesn't exist or parsing fails
        return [
            'max_tickets_per_order' => 1,
            'refund_policy' => '',
            'allow_refunds' => false,
            'require_approval' => false,
            'allow_share_ticket' => false,
            'limit_per_user' => false,
            'max_tickets_per_user' => null,
            'ticket_colors' => [
                'primary' => '#10b981',
                'secondary' => '#f59e0b',
                'accent' => '#059669',
            ],
        ];
    }

    private function loadTicketTypesFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $ticketTypesPath = $eventDir . '/ticket_types.json';
            
            if (Storage::disk('r2')->exists($ticketTypesPath)) {
                $ticketTypesJson = Storage::disk('r2')->get($ticketTypesPath);
                return json_decode($ticketTypesJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load ticket types from R2: ' . $e->getMessage());
        }
        
        return [];
    }

    private function loadStaffDataFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $staffDataPath = $eventDir . '/staff_data.json';
            
            if (Storage::disk('r2')->exists($staffDataPath)) {
                $staffDataJson = Storage::disk('r2')->get($staffDataPath);
                return json_decode($staffDataJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load staff data from R2: ' . $e->getMessage());
        }
        
        return [];
    }

    private function loadCertificateDataFromR2($eventId)
    {
        try {
            // Support multiple historical locations for the certificate data file
            $candidates = [
                "events/{$eventId}/certificate/certificate.json",
                "events/{$eventId}/certificate_data.json",
                "events/{$eventId}/certificate/certificate_data.json",
                "events/{$eventId}/certificate.json",
            ];
            foreach ($candidates as $path) {
                if (Storage::disk('r2')->exists($path)) {
                    $json = Storage::disk('r2')->get($path);
                    return json_decode($json, true) ?? [];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load certificate data from R2: ' . $e->getMessage());
        }
        return [];
    }

    private function isCertificateEnabled($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $certificateSettingsPath = $eventDir . '/certificate_settings.json';
            
            if (Storage::disk('r2')->exists($certificateSettingsPath)) {
                $certificateSettingsJson = Storage::disk('r2')->get($certificateSettingsPath);
                $settings = json_decode($certificateSettingsJson, true) ?? [];
                return (bool)($settings['enabled'] ?? false);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load certificate settings from R2: ' . $e->getMessage());
        }
        
        return false;
    }

    private function loadCertificateSettingsFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $certificateSettingsPath = $eventDir . '/certificate_settings.json';
            if (Storage::disk('r2')->exists($certificateSettingsPath)) {
                $json = Storage::disk('r2')->get($certificateSettingsPath);
                return json_decode($json, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load certificate settings from R2: ' . $e->getMessage());
        }
        return [];
    }

    private function loadLayoutDataFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $layoutPath = $eventDir . '/layout.json';

            if (Storage::disk('r2')->exists($layoutPath)) {
                $layoutJson = Storage::disk('r2')->get($layoutPath);
                return json_decode($layoutJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load layout from R2: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Generate a fresh signed URL for an object in R2, with public fallback
     */
    private function getR2Url(string $path): ?string
    {
        try {
            $disk = \Storage::disk('r2');
            if (!$disk->exists($path)) {
                \Log::warning('getR2Url: R2 path not found', ['path' => $path]);
                return null;
            }
            try {
                return $disk->temporaryUrl($path, now()->addMinutes(60));
            } catch (\Throwable $t) {
                return rtrim(config('filesystems.disks.r2.url'), '/') . '/' . ltrim($path, '/');
            }
        } catch (\Throwable $t) {
            \Log::error('getR2Url failed', ['path' => $path, 'error' => $t->getMessage()]);
            return null;
        }
    }
}

