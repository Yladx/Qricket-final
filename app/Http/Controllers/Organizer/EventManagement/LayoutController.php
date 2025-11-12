<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Organizer\EventManagement\Traits\DraftHelpers;

class LayoutController extends Controller
{
    use DraftHelpers;

    public function step2(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.step1')->with('error', 'Event ID is required.');
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        // Layout editing allowed for draft and rejected status
        if (!$event || !in_array($event->status, ['draft', 'rejected']) || (!$isOwner && !$isAdminEvent)) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }

        // Load event data from database
        $eventData = [
            'title' => $event->title,
            'description' => $event->description,
            'category' => $event->category,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'poster' => $event->poster,
            'thumbnail' => $event->thumbnail,
            'existing_poster_url' => $event->poster_url,
            'existing_thumbnail_url' => $event->thumbnail_url,
        ];

        // Load layout data from R2 if available
        $layoutData = null;
        $layoutPath = null;
        if ($event->layout_path) {
            $layoutData = $this->loadLayoutFromR2($event->layout_path);
            $layoutPath = $event->layout_path;
            if ($layoutData) {
                $eventData['existing_layout'] = $layoutData;
            }
        }

        // Load footer.json from R2 for autosaved footer settings
        $footerData = [];
        try {
            $disk = Storage::disk('r2');
            $footerPath = "events/{$eventId}/footer.json";
            if ($disk->exists($footerPath)) {
                $json = $disk->get($footerPath);
                $decoded = json_decode($json, true);
                if (is_array($decoded)) { $footerData = $decoded; }
            }
        } catch (\Throwable $t) {
            \Log::warning('Failed to load footer.json', ['event_id' => $eventId, 'error' => $t->getMessage()]);
        }

        // Check if user has active subscription (for premium layouts)
        $hasActiveSubscription = Auth::guard('web')->user()?->hasActiveSubscription() ?? false;

        return response()
            ->view('organizer.events.create.step2', compact('event', 'eventData', 'layoutData', 'layoutPath', 'eventId', 'footerData', 'hasActiveSubscription'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function step2Store(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.step1')->with('error', 'Event ID is required.');
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        // Layout editing only allowed for draft status (not pending_approval)
        if (!$event || $event->status !== 'draft' || (!$isOwner && !$isAdminEvent)) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }

        // Handle preview request - save layout data first
        if ($request->has('preview') && $request->preview === 'true') {
            $layoutData = $request->input('layout_data');
            if ($layoutData) {
                // Persist layout JSON to R2 immediately for preview
                try {
                    $eventDir = "events/{$eventId}";
                    $layoutJson = is_string($layoutData) ? $layoutData : json_encode($layoutData);
                    Storage::disk('r2')->put($eventDir . '/layout.json', $layoutJson);
                    
                    // Update event with layout path
                    $event->update(['layout_path' => $eventDir . '/layout.json']);
                } catch (\Exception $e) {
                    \Log::error('Failed to save layout for preview: ' . $e->getMessage());
                }
            }
            
            return redirect()->route('organizer.events.layout.preview', ['id' => $eventId]);
        }

        // Handle layout data
        $layoutData = $request->input('layout_data');
        if ($layoutData) {
            try {
                $eventDir = "events/{$eventId}";
                $layoutJson = is_string($layoutData) ? $layoutData : json_encode($layoutData);
                Storage::disk('r2')->put($eventDir . '/layout.json', $layoutJson);
                $event->update(['layout_path' => $eventDir . '/layout.json']);
            } catch (\Exception $e) {
                \Log::error('Failed to save layout: ' . $e->getMessage());
            }
        }

        return redirect()->route('organizer.events.create.ticket', ['id' => $eventId]);
    }

    /**
     * Universal layout preview - works for both create/edit/manage layouts
     */
    public function layoutPreview(Request $request, $id = null)
    {
        // Support both route parameter and query parameter
        $eventId = $id ?? $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.index')->with('error', 'Event ID is required.');
        }

        $event = Event::find($eventId);
        if (!$event) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found.');
        }

        // Check access - allow owner, admin, or staff
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            return redirect()->route('organizer.events.index')->with('error', 'Unauthorized access to this event.');
        }

        // Load event data from database
        $eventData = [
            'title' => $event->title,
            'description' => $event->description,
            'category' => $event->category,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'poster' => $event->poster_url,  // Use URL accessor
            'thumbnail' => $event->thumbnail_url,  // Use URL accessor
            'existing_poster_url' => $event->poster_url,
            'existing_thumbnail_url' => $event->thumbnail_url,
        ];

        // Load layout data from R2 if available
        $layoutData = null;
        if ($event->layout_path) {
            $layoutData = $this->loadLayoutFromR2($event->layout_path);
        }

        // Ensure previewData exists to prevent undefined variable in view
        $previewData = [];

        return view('organizer.events.preview.event-preview', compact('eventData', 'layoutData', 'previewData', 'eventId'));
    }

    public function step2PreviewSave(Request $request)
    {
        $eventData = session('event_data', []);
        
        if (empty($eventData['step1'])) {
            return redirect()->route('organizer.events.step1')
                ->with('error', 'Please complete Step 1 first.');
        }

        // Update session with any changes from preview
        $layoutData = $request->input('layout_data');
        if ($layoutData) {
            $request->session()->put('event_data.step2.layout_data', $layoutData);
            $request->session()->put('event_data.step2.updated_at', now()->toISOString());
        }

        return redirect()->route('organizer.events.create.layout')
            ->with('success', 'Layout saved successfully.');
    }

    public function step2PreviewLoad(Request $request, $cloudPath)
    {
        try {
            $disk = Storage::disk('r2');
            
            if (!$disk->exists($cloudPath)) {
                return response()->json(['error' => 'File not found'], 404);
            }
            
            $content = $disk->get($cloudPath);
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Invalid JSON'], 400);
            }
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            \Log::error('Failed to load layout from R2: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load layout'], 500);
        }
    }

    public function renderLayout(Request $request, $layout)
    {
        try {
            \Log::info("renderLayout - Starting to render layout: {$layout}");
            
            $eventData = $request->input('eventData', []);
            $details = $request->input('details', []);
            
            \Log::info("renderLayout - Request data:", [
                'layout' => $layout,
                'eventData' => $eventData,
                'details' => $details,
                'details_keys' => is_array($details) ? array_keys($details) : 'Not array',
                'details_content' => isset($details['content']) ? $details['content'] : 'No content key',
                'details_image' => isset($details['image']) ? $details['image'] : 'No image key',
                'request_id' => $request->get('id'),
                'all_request_data' => $request->all()
            ]);
            
            // Create a mock event object for the layout templates
            $event = new \stdClass();
            $event->title = $eventData['title'] ?? 'Event Title';
            $event->description = $eventData['description'] ?? 'Event description';
            $event->location = $eventData['location'] ?? 'Event Location';
            $event->category = $eventData['category'] ?? 'Event Category';
            $event->start_date = isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date']) : \Carbon\Carbon::now();
            $event->end_date = isset($eventData['end_date']) ? \Carbon\Carbon::parse($eventData['end_date']) : \Carbon\Carbon::now()->addHours(2);
            // Handle poster and thumbnail URLs (they should already be full R2 paths)
            $event->poster_url = $eventData['poster'] ?? null;
            $event->thumbnail_url = $eventData['thumbnail'] ?? null;
            
            \Log::info("renderLayout - Poster and thumbnail URLs:", [
                'poster_raw' => $eventData['poster'] ?? 'null',
                'poster_url' => $event->poster_url,
                'thumbnail_raw' => $eventData['thumbnail'] ?? 'null',
                'thumbnail_url' => $event->thumbnail_url
            ]);
            
            // Convert layout image filenames to full R2 URLs (same logic as Event model)
            $eventId = $request->get('id');
            
            // Handle single image field
            if (isset($details['image']) && $details['image']) {
                $details['image'] = $this->convertImagePathToUrl($details['image'], $eventId);
            }
            
            // Handle about-two-images component (image1, image2)
            if (isset($details['image1']) && $details['image1']) {
                $details['image1'] = $this->convertImagePathToUrl($details['image1'], $eventId);
            }
            
            if (isset($details['image2']) && $details['image2']) {
                $details['image2'] = $this->convertImagePathToUrl($details['image2'], $eventId);
            }
            
            // Handle photo-highlights component (photos array)
            if (isset($details['photos']) && is_array($details['photos'])) {
                foreach ($details['photos'] as &$photo) {
                    if (isset($photo['url']) && $photo['url']) {
                        $photo['url'] = $this->convertImagePathToUrl($photo['url'], $eventId);
                    }
                }
            }
            
            // Handle hero carousel component (slides array)
            if (isset($details['slides']) && is_array($details['slides'])) {
                foreach ($details['slides'] as &$slide) {
                    if (isset($slide['background_image']) && $slide['background_image']) {
                        $slide['background_image'] = $this->convertImagePathToUrl($slide['background_image'], $eventId);
                    }
                }
            }
            
            // Handle flattened hero carousel format (background_image_0, background_image_1, etc.)
            foreach ($details as $key => &$value) {
                if (str_starts_with($key, 'background_image_') && $value) {
                    $value = $this->convertImagePathToUrl($value, $eventId);
                }
            }
            
            // Handle background_image and background_image_path for premium-hero-spotlight
            if (isset($details['background_image']) && $details['background_image']) {
                $details['background_image'] = $this->convertImagePathToUrl($details['background_image'], $eventId);
            }
            if (isset($details['background_image_path']) && $details['background_image_path']) {
                $details['background_image'] = $this->convertImagePathToUrl($details['background_image_path'], $eventId);
            }
            
            // Handle flattened speaker image format (speakers_0_image, speakers_1_image, etc.) FIRST
            // This maps flattened format back to speakers array if needed
            foreach ($details as $key => $value) {
                if (preg_match('/^speakers_(\d+)_image(_path)?$/', $key, $matches) && $value) {
                    $index = (int) $matches[1];
                    if (!isset($details['speakers']) || !is_array($details['speakers'])) {
                        $details['speakers'] = [];
                    }
                    if (!isset($details['speakers'][$index]) || !is_array($details['speakers'][$index])) {
                        $details['speakers'][$index] = [];
                    }
                    // Convert and store in speakers array
                    $convertedUrl = $this->convertImagePathToUrl($value, $eventId);
                    if ($convertedUrl) {
                        $details['speakers'][$index]['image'] = $convertedUrl;
                    }
                }
            }
            
            // Handle speakers array images for speakers-grid (after handling flattened format)
            if (isset($details['speakers']) && is_array($details['speakers'])) {
                foreach ($details['speakers'] as &$speaker) {
                    if (is_array($speaker)) {
                        // Convert image_path to image if image_path exists but image doesn't
                        if (isset($speaker['image_path']) && $speaker['image_path'] && !isset($speaker['image'])) {
                            $speaker['image'] = $this->convertImagePathToUrl($speaker['image_path'], $eventId);
                        }
                        // Convert image if it's a relative path (not already a URL)
                        if (isset($speaker['image']) && $speaker['image'] && !str_starts_with($speaker['image'], 'http')) {
                            $speaker['image'] = $this->convertImagePathToUrl($speaker['image'], $eventId);
                        }
                        // Also handle image_url if present
                        if (isset($speaker['image_url']) && $speaker['image_url'] && !str_starts_with($speaker['image_url'], 'http')) {
                            $speaker['image'] = $this->convertImagePathToUrl($speaker['image_url'], $eventId);
                        }
                    }
                }
            }
            
            // Premium Sponsors Grid: convert each sponsor.logo path to signed/public URL
            if (isset($details['sponsors']) && is_array($details['sponsors'])) {
                foreach ($details['sponsors'] as &$sponsor) {
                    if (is_array($sponsor)) {
                        // Convert logo_path to logo if logo_path exists but logo doesn't
                        if (isset($sponsor['logo_path']) && $sponsor['logo_path'] && !isset($sponsor['logo'])) {
                            // Ensure logo_path is a string
                            if (is_string($sponsor['logo_path'])) {
                                $sponsor['logo'] = $this->convertImagePathToUrl($sponsor['logo_path'], $eventId);
                            }
                        }
                        // Convert logo if it's a relative path (not already a URL)
                        // Check that logo is a string before using str_starts_with
                        if (isset($sponsor['logo']) && is_string($sponsor['logo']) && $sponsor['logo'] && !str_starts_with($sponsor['logo'], 'http')) {
                            $sponsor['logo'] = $this->convertImagePathToUrl($sponsor['logo'], $eventId);
                        } elseif (isset($sponsor['logo']) && !is_string($sponsor['logo'])) {
                            // If logo is not a string (could be array or object), log and skip conversion
                            \Log::warning("renderLayout - Sponsor logo is not a string", [
                                'sponsor' => $sponsor,
                                'logo_type' => gettype($sponsor['logo']),
                                'logo_value' => is_array($sponsor['logo']) ? 'array' : (is_object($sponsor['logo']) ? 'object' : 'other')
                            ]);
                            // Try to extract string path if it's an array with common keys
                            if (is_array($sponsor['logo'])) {
                                $logoPath = $sponsor['logo']['path'] ?? $sponsor['logo']['url'] ?? $sponsor['logo'][0] ?? null;
                                if ($logoPath && is_string($logoPath)) {
                                    $sponsor['logo'] = $this->convertImagePathToUrl($logoPath, $eventId);
                                } else {
                                    unset($sponsor['logo']); // Remove invalid logo data
                                }
                            } else {
                                unset($sponsor['logo']); // Remove invalid logo data
                            }
                        }
                    } elseif (is_string($sponsor) && !str_starts_with($sponsor, 'http')) {
                        // Legacy format: just a string path
                        $sponsor = $this->convertImagePathToUrl($sponsor, $eventId);
                    }
                }
            }
            
            // Handle flattened sponsor logo format (sponsor_0_logo, sponsor_1_logo, etc.)
            foreach ($details as $key => $value) {
                if (preg_match('/^sponsor_(\d+)_logo(_path)?$/', $key, $matches) && $value) {
                    // Only process if value is a string (not an array)
                    if (!is_string($value)) {
                        \Log::warning("renderLayout - Flattened sponsor logo value is not a string", [
                            'key' => $key,
                            'value_type' => gettype($value),
                            'value' => is_array($value) ? 'array' : (is_object($value) ? 'object' : 'other')
                        ]);
                        continue;
                    }
                    $index = (int) $matches[1];
                    if (!isset($details['sponsors']) || !is_array($details['sponsors'])) {
                        $details['sponsors'] = [];
                    }
                    if (!isset($details['sponsors'][$index]) || !is_array($details['sponsors'][$index])) {
                        $details['sponsors'][$index] = [];
                    }
                    // Convert and store in sponsors array
                    $convertedUrl = $this->convertImagePathToUrl($value, $eventId);
                    if ($convertedUrl) {
                        // Only set if current logo doesn't exist or is not already a URL
                        $currentLogo = $details['sponsors'][$index]['logo'] ?? null;
                        if (!$currentLogo || (is_string($currentLogo) && !str_starts_with($currentLogo, 'http'))) {
                            $details['sponsors'][$index]['logo'] = $convertedUrl;
                        }
                    }
                }
            }
            
            // Handle gallery field for photo-highlights component
            if (isset($details['gallery']) && $details['gallery']) {
                // Check if it's already a full URL
                if (str_starts_with($details['gallery'], 'http')) {
                    $details['gallery_url'] = $details['gallery'];
                    \Log::info("renderLayout - Gallery is already a full URL:", [
                        'gallery_url' => $details['gallery_url']
                    ]);
                } else {
                    // Get event ID from URL parameter
                    $eventId = $request->get('id');
                    if (!$eventId) {
                        \Log::warning("renderLayout - No event ID found in request for gallery");
                        $details['gallery_url'] = null;
                    } else {
                        // Check if it's already a full path or just a filename
                        if (str_contains($details['gallery'], 'events/')) {
                            // Already a full path, use it directly
                            $imagePath = $details['gallery'];
                        } else {
                            // Just a filename, construct the full path
                            $imagePath = "events/{$eventId}/layoutimages/{$details['gallery']}";
                        }
                        
                        $details['gallery_url'] = $this->getR2Url($imagePath);
                        \Log::info("renderLayout - Gallery URL conversion:", [
                            'eventId' => $eventId,
                            'original_gallery' => $details['gallery'],
                            'image_path' => $imagePath,
                            'gallery_url' => $details['gallery_url']
                        ]);
                    }
                }
            }
            
            \Log::info("renderLayout - About to render layout template", [
                'layout' => $layout,
                'event_title' => $event->title,
                'details_content' => $details['content'] ?? 'No content',
                'details_image' => $details['image'] ?? 'No image',
                'details_image_url' => $details['image_url'] ?? 'No image URL'
            ]);
            
            // For tickets-pricing, include real event model when available so the Blade can render DB tickets
            if ($layout === 'tickets-pricing') {
                $eventId = (int) $request->get('id');
                if ($eventId) {
                    $eventModel = Event::with('eventTickets')->find($eventId);
                    if ($eventModel) { $event = $eventModel; }
                }
            }
            // Map premium aliases to paths under premium/
            $template = "events.layouts.{$layout}";
            if (str_starts_with($layout, 'premium-')) {
                $map = [
                    'premium-hero-spotlight' => 'events.layouts.premium.hero-spotlight',
                    'premium-gallery-mosaic' => 'events.layouts.premium.gallery-mosaic',
                    'premium-schedule-timeline' => 'events.layouts.premium.schedule-timeline',
                    'premium-testimonials-slider' => 'events.layouts.premium.testimonials-slider',
                    'premium-sponsors-grid' => 'events.layouts.premium.sponsors-grid',
                ];
                if (isset($map[$layout])) {
                    $template = $map[$layout];
                }
            }
            \Log::info("renderLayout - About to render template", [
                'layout' => $layout,
                'template' => $template,
                'details_keys' => array_keys($details),
                'details_structure' => [
                    'has_timeline' => isset($details['timeline']),
                    'timeline_count' => isset($details['timeline']) && is_array($details['timeline']) ? count($details['timeline']) : 0,
                    'has_testimonials' => isset($details['testimonials']),
                    'testimonials_count' => isset($details['testimonials']) && is_array($details['testimonials']) ? count($details['testimonials']) : 0,
                    'has_sponsors' => isset($details['sponsors']),
                    'sponsors_count' => isset($details['sponsors']) && is_array($details['sponsors']) ? count($details['sponsors']) : 0,
                ]
            ]);
            
            return view($template, compact('event', 'details'));
            
        } catch (\Exception $e) {
            \Log::error("renderLayout - Error rendering layout {$layout}: " . $e->getMessage(), [
                'layout' => $layout,
                'template' => $template ?? 'unknown',
                'details_keys' => isset($details) ? array_keys($details) : 'no details',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a proper HTML error message instead of JSON for preview compatibility
            return response(
                '<div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Failed to Load Component</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Could not load the ' . str_replace('-', ' ', $layout) . ' component.</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">' . htmlspecialchars($e->getMessage()) . '</p>
                </div>',
                500
            )->header('Content-Type', 'text/html');
        }
    }

    public function uploadLayoutImage(Request $request)
    {
        try {
            \Log::info('uploadLayoutImage called', [
                'all_input_keys' => array_keys($request->all()),
                'has_image' => $request->hasFile('image'),
                'component_id' => $request->input('component_id'),
                'event_id' => $request->input('id'),
                'files' => array_keys($request->allFiles())
            ]);
            
            try {
                $request->validate([
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
                    'component_id' => 'required|string'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Validation failed', [
                    'errors' => $e->errors(),
                    'all_input' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . json_encode($e->errors())
                ], 400);
            }

            $image = $request->file('image');
            $componentId = $request->input('component_id');
            
            // Get event ID from request
            $eventId = $request->input('id');
            if (!$eventId) {
                \Log::warning('Event ID missing in upload request');
                return response()->json([
                    'success' => false,
                    'message' => 'Event ID is required'
                ], 400);
            }

            // Verify event exists (skip role checks to allow autosave uploads for owner/admin/staff uniformly)
            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            
            // Generate random filename with 15-character random string
            $extension = $image->getClientOriginalExtension();
            $randomString = $this->generateRandomString(15);
            $filename = 'img_' . $randomString . '_' . time() . '.' . $extension;
            
            // Create the full path for storage
            $filePath = "events/{$eventId}/layoutimages/{$filename}";
            
            // Upload to R2 using the full path
            $uploaded = Storage::disk('r2')->putFileAs(
                "events/{$eventId}/layoutimages",
                $image,
                $filename
            );
            
            if ($uploaded) {
                // Get public URL using the uploaded path
                $imageUrl = $this->getR2Url($uploaded);
                
                \Log::info('Layout image uploaded successfully:', [
                    'component_id' => $componentId,
                    'file_path' => $uploaded,
                    'image_url' => $imageUrl
                ]);
                
                return response()->json([
                    'success' => true,
                    'image_path' => $uploaded,
                    'image_url' => $imageUrl,
                    'filename' => $filename,
                    'display_name' => 'Uploaded Image'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload image'
                ], 500);
            }
            
        } catch (\Exception $e) {
            \Log::error('Layout image upload failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSessionLayout(Request $request)
    {
        try {
            $layoutData = $request->input('layout_data');
            $updatedAt = $request->input('updated_at');
            $footerData = $request->input('footer_data');
            
            if ($layoutData) {
                $request->session()->put('event_data.step2.layout_data', $layoutData);
                if ($updatedAt) {
                    $request->session()->put('event_data.step2.updated_at', $updatedAt);
                } else {
                    $request->session()->put('event_data.step2.updated_at', now()->toISOString());
                }
                
                \Log::info('Session layout data updated:', [
                    'layout_data' => $layoutData
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Session updated successfully'
                ]);
            }

            // Footer data save path (store footer.json under event directory)
            if ($footerData) {
                $eventId = $request->input('id') ?? $request->query('id');
                if (!$eventId) {
                    return response()->json(['success' => false, 'message' => 'Event ID required'], 400);
                }
                $disk = \Storage::disk('r2');
                $eventDir = "events/{$eventId}";
                $disk->put($eventDir . '/footer.json', is_string($footerData) ? $footerData : json_encode($footerData));
                return response()->json(['success' => true, 'message' => 'Footer saved']);
            }

            return response()->json([
                'success' => false,
                'message' => 'No layout data provided'
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('Error updating session layout data:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function autosaveStep2(Request $request)
    {
        try {
            $eventId = $request->input('id') ?? $request->query('id');
            
            if (!$eventId) {
                return response()->json(['success' => false, 'message' => 'Event ID is required'], 400);
            }

            $event = Event::find($eventId);
            $isAdmin = Auth::guard('admin')->check();
            $isOwner = $event && $event->user_id === Auth::id();
            $isAdminEvent = $event && $event->user_id === null && $isAdmin;
            $isStaff = $event ? $event->staff()->where('user_id', Auth::id())->exists() : false;

            // Check if event exists and user has access (owner, staff, or admin)
            if (!$event) {
                return response()->json(['success' => false, 'message' => 'Event not found'], 404);
            }
            
            // Verify user has access to this event
            if (!$isAdmin && !$isOwner && !$isAdminEvent && !$isStaff) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to this event'], 403);
            }

            $updated = [];
            $disk = \Storage::disk('r2');
            $eventDir = "events/{$eventId}";

            // Handle any other form fields that might be submitted
            $allowedFields = ['title', 'description', 'location', 'start_date', 'end_date'];
            foreach ($allowedFields as $field) {
                if ($request->has($field)) {
                    $event->{$field} = $request->input($field);
                    $updated[] = $field;
                }
            }

            // Layout JSON (accept multiple keys from different clients)
            $layoutData = $request->input('layout_data')
                ?? $request->input('layout_json')
                ?? $request->input('layout');
            if ($layoutData) {
                // Load previous layout (if any) before overwriting
                $oldLayoutData = null;
                if ($event->layout_path && $disk->exists($event->layout_path)) {
                    try { $oldLayoutData = json_decode($disk->get($event->layout_path), true); } catch (\Throwable $t) {}
                }

                $layoutJson = is_string($layoutData) ? $layoutData : json_encode($layoutData);
                $disk->put($eventDir . '/layout.json', $layoutJson);
                $event->layout_path = $eventDir . '/layout.json';
                $updated[] = 'layout_path';

                // Cleanup images that are no longer referenced
                try {
                    $newDecoded = is_string($layoutData) ? json_decode($layoutData, true) : $layoutData;
                    if (is_array($newDecoded)) {
                        $this->cleanupUnusedLayoutImages((int)$eventId, $oldLayoutData, $newDecoded);
                    }
                } catch (\Throwable $t) {}
            }

            // Poster upload
            if ($request->hasFile('poster')) {
                $poster = $request->file('poster');
                $posterName = 'poster.' . $poster->getClientOriginalExtension();
                
                // Delete old poster if exists
                if ($event->poster && $disk->exists($event->poster)) {
                    $disk->delete($event->poster);
                }
                
                $disk->putFileAs($eventDir, $poster, $posterName);
                $event->poster = $eventDir . '/' . $posterName;
                $updated[] = 'poster';
            }
            if ($request->boolean('poster_remove')) {
                if ($event->poster && $disk->exists($event->poster)) { $disk->delete($event->poster); }
                $event->poster = null;
                $updated[] = 'poster';
            }

            // Thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $thumb = $request->file('thumbnail');
                $thumbName = 'thumbnail.' . $thumb->getClientOriginalExtension();
                
                // Delete old thumbnail if exists
                if ($event->thumbnail && $disk->exists($event->thumbnail)) {
                    $disk->delete($event->thumbnail);
                }
                
                $disk->putFileAs($eventDir, $thumb, $thumbName);
                $event->thumbnail = $eventDir . '/' . $thumbName;
                $updated[] = 'thumbnail';
            }
            if ($request->boolean('thumbnail_remove')) {
                if ($event->thumbnail && $disk->exists($event->thumbnail)) { $disk->delete($event->thumbnail); }
                $event->thumbnail = null;
                $updated[] = 'thumbnail';
            }

            if (!empty($updated)) {
                $event->save();
            }

            return response()->json([
                'success' => true,
                'updated' => $updated,
                'poster_url' => $event->poster_url, // Accessor that generates R2 URL
                'thumbnail_url' => $event->thumbnail_url, // Accessor that generates R2 URL
            ]);

        } catch (\Exception $e) {
            \Log::error('Autosave step 2 failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Autosave failed: ' . $e->getMessage()], 500);
        }
    }

    private function updateDraftWithStepData($stepNumber, $stepData)
    {
        try {
            $draftId = session('event_data.draft_id');
            if (!$draftId) return;

            $draft = Event::find($draftId);
            if (!$draft || $draft->status !== 'draft') return;

            switch ($stepNumber) {
                case 2:
                    $draft->update([
                        'layout_path' => $stepData['layout_path'] ?? null,
                        'poster' => $stepData['poster'] ?? null,
                        'thumbnail' => $stepData['thumbnail'] ?? null,
                    ]);
                    break;
            }

        } catch (\Exception $e) {
            \Log::error('Failed to update draft with step data: ' . $e->getMessage());
        }
    }

    private function storeLayoutToR2($layoutData)
    {
        try {
            $draftId = session('event_data.draft_id');
            $userId = Auth::id();
            
            // Create event-specific directory structure: events/{event_id}/layout.json
            $eventDir = $draftId ? "events/{$draftId}" : "events/temp/{$userId}";
            $filename = 'layout.json';
            
            // Parse layout data if it's a string
            if (is_string($layoutData)) {
                $layoutData = json_decode($layoutData, true);
            }

            // Store just the layout data directly (simplified format)
            $path = Storage::disk('r2')->put(
                $eventDir . '/' . $filename,
                json_encode($layoutData, JSON_PRETTY_PRINT)
            );

            if ($path) {
                \Log::info("Layout JSON stored to R2: {$path}");
                return $path;
            } else {
                throw new \Exception('Failed to store layout to R2');
            }

        } catch (\Exception $e) {
            \Log::error('Failed to store layout to R2: ' . $e->getMessage());
            throw new \Exception('Failed to store layout to cloud storage');
        }
    }

    private function loadLayoutFromR2($path)
    {
        try {
            \Log::info("loadLayoutFromR2 - Starting to load layout from path: {$path}");
            
            $disk = Storage::disk('r2');
            
            // Check if file exists
            if (!$disk->exists($path)) {
                \Log::warning("Layout file not found: {$path}");
                return null;
            }
            
            \Log::info("loadLayoutFromR2 - File exists, reading content");
            $content = $disk->get($path);
            
            if ($content) {
                \Log::info("loadLayoutFromR2 - Content loaded, length: " . strlen($content));
                \Log::info("loadLayoutFromR2 - Raw content: " . substr($content, 0, 500) . "...");
                
                $data = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Log::error("loadLayoutFromR2 - JSON decode error: " . json_last_error_msg());
                    return null;
                }
                
                \Log::info("loadLayoutFromR2 - JSON decoded successfully", [
                    'data_type' => gettype($data),
                    'data_is_array' => is_array($data),
                    'data_keys' => is_array($data) ? array_keys($data) : 'N/A',
                    'data_count' => is_array($data) ? count($data) : 'N/A'
                ]);
                
                return $data;
            } else {
                \Log::warning("loadLayoutFromR2 - No content found in file: {$path}");
                return null;
            }
            
        } catch (\Exception $e) {
            \Log::error("loadLayoutFromR2 - Error loading layout from R2: " . $e->getMessage(), [
                'path' => $path,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function generateRandomString($length = 15)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    private function convertImagePathToUrl($imagePath, $eventId)
    {
        // Check if it's already a full URL
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }
        
        if (!$eventId) {
            \Log::warning("convertImagePathToUrl - No event ID provided");
            return null;
        }
        
        // Check if it's already a full path or just a filename
        if (str_contains($imagePath, 'events/')) {
            // Already a full path, use it directly
            $fullPath = $imagePath;
        } else {
            // Just a filename, construct the full path
            $fullPath = "events/{$eventId}/layoutimages/{$imagePath}";
        }
        
        $imageUrl = $this->getR2Url($fullPath);
        \Log::info("convertImagePathToUrl - Image URL conversion:", [
            'eventId' => $eventId,
            'original_image' => $imagePath,
            'full_path' => $fullPath,
            'image_url' => $imageUrl
        ]);
        
        return $imageUrl;
    }

    private function getR2Url($path)
    {
        try {
            $disk = Storage::disk('r2');
            
            \Log::info("getR2Url called:", [
                'path' => $path,
                'r2_config_url' => config('filesystems.disks.r2.url')
            ]);
            
            // Check if file exists
            if (!$disk->exists($path)) {
                \Log::warning("R2 file not found: {$path}");
                return null;
            }
            
            \Log::info("R2 file exists, generating URL for: {$path}");
            
            // Try to get signed URL first (for private files)
            try {
                $signedUrl = $disk->temporaryUrl($path, now()->addMinutes(60));
                \Log::info("Signed URL generated successfully: {$signedUrl}");
                return $signedUrl;
            } catch (\Exception $e) {
                \Log::info("Signed URL failed for {$path}, trying public URL", [
                    'error' => $e->getMessage()
                ]);
                
                // Fallback to public URL
                $publicUrl = config('filesystems.disks.r2.url') . '/' . $path;
                \Log::info("Using public URL: {$publicUrl}");
                return $publicUrl;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get R2 URL: ' . $e->getMessage(), [
                'path' => $path,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function cleanupUnusedLayoutImages(int $eventId, ?array $oldLayoutData, array $newLayoutData): void
    {
        if (empty($oldLayoutData)) { return; }

        $oldSet = $this->extractReferencedLayoutImages($oldLayoutData, $eventId);
        $newSet = $this->extractReferencedLayoutImages($newLayoutData, $eventId);
        $toDelete = array_diff($oldSet, $newSet);

        if (empty($toDelete)) { return; }

        $disk = \Storage::disk('r2');
        foreach ($toDelete as $path) {
            try {
                if ($disk->exists($path)) {
                    $disk->delete($path);
                    \Log::info('Autosave cleanup: deleted unreferenced layout image', ['file' => $path]);
                }
            } catch (\Throwable $t) {
                \Log::warning('Autosave cleanup: failed to delete image', ['file' => $path, 'error' => $t->getMessage()]);
            }
        }
    }

    /**
     * Generate fresh signed URL from relative path
     */
    public function getFreshImageUrl(Request $request)
    {
        try {
            $request->validate([
                'path' => 'required|string',
                'event_id' => 'required|integer'
            ]);

            $path = $request->input('path');
            $eventId = $request->input('event_id');

            \Log::info('getFreshImageUrl called:', [
                'path' => $path,
                'event_id' => $eventId,
                'user_id' => Auth::id()
            ]);

            // Verify event exists and user has access (owner/admin/staff);
            // allow for any event status so images also load in edit/manage views
            $event = Event::find($eventId);
            $isAdmin = Auth::guard('admin')->check();
            $isOwner = $event && $event->user_id === Auth::id();
            $isAdminEvent = $event && $event->user_id === null && $isAdmin;
            $isStaff = $event ? $event->staff()->where('user_id', Auth::id())->exists() : false;
            
            // Admins can access images for any event (e.g., for approval pages)
            if (!$event || (!$isOwner && !$isAdminEvent && !$isStaff && !$isAdmin)) {
                \Log::warning('Event not found or not authorized:', [
                    'event_id' => $eventId,
                    'event_found' => (bool) $event,
                    'event_status' => $event ? $event->status : 'not_found',
                    'event_user_id' => $event ? $event->user_id : 'not_found',
                    'auth_user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found or not authorized'
                ], 404);
            }

            \Log::info('Event found, checking R2 file:', [
                'event_id' => $eventId,
                'event_status' => $event->status,
                'path' => $path
            ]);

            // Generate fresh signed URL
            $imageUrl = $this->getR2Url($path);
            
            \Log::info('getR2Url result:', [
                'path' => $path,
                'image_url' => $imageUrl ? 'generated' : 'failed'
            ]);
            
            if ($imageUrl) {
                return response()->json([
                    'success' => true,
                    'image_url' => $imageUrl
                ]);
            } else {
                \Log::warning('Failed to generate image URL for path:', ['path' => $path]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate image URL'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to generate fresh image URL:', [
                'error' => $e->getMessage(),
                'path' => $request->input('path'),
                'event_id' => $request->input('event_id'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate image URL: ' . $e->getMessage()
            ], 500);
        }
    }

    private function extractReferencedLayoutImages(array $layoutData, int $eventId): array
    {
        $paths = [];
        $prefix = "events/{$eventId}/layoutimages/";

        $walker = function ($value) use (&$paths, &$walker, $prefix) {
            if (is_array($value)) {
                foreach ($value as $v) { $walker($v); }
                return;
            }
            if (!is_string($value)) { return; }

            $lower = strtolower($value);
            $isImage = str_ends_with($lower, '.jpg') || str_ends_with($lower, '.jpeg') || str_ends_with($lower, '.png') || str_ends_with($lower, '.webp') || str_ends_with($lower, '.gif');
            if (!$isImage) { return; }

            if (str_contains($value, 'events/')) {
                $paths[] = $value;
            } else {
                $paths[] = $prefix . ltrim($value, '/');
            }
        };

        foreach ($layoutData as $node) { $walker($node); }
        return array_values(array_unique($paths));
    }
}


