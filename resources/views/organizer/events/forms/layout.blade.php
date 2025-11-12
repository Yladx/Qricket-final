<div class="space-y-8">
    <!-- Event Header Preview (matches event header design) -->
    @php
    $thumbUrl = $eventData['existing_thumbnail_url'] ?? (isset($event) ? $event->thumbnail_url : null);
    $posterUrl = $eventData['existing_poster_url'] ?? (isset($event) ? $event->poster_url : null);

    // Handle tags - can be string or array
    $tagsValue = $eventData['tags'] ?? (isset($event) ? $event->tags : null);
    $tagsArray = null;
    if ($tagsValue) {
    if (is_array($tagsValue)) {
    $tagsArray = $tagsValue;
    } elseif (is_string($tagsValue)) {
    $tagsArray = array_filter(array_map('trim', explode(',', $tagsValue)));
    }
    }

    $eventObj = (object) [
    'id' => $eventData['id'] ?? (isset($event) ? $event->id : null),
    'title' => $eventData['title'] ?? (isset($event) ? $event->title : 'Untitled Event'),
    'thumbnail_url' => $thumbUrl,
    'poster_url' => $posterUrl,
    'category' => $eventData['category'] ?? (isset($event) ? $event->category : 'event'),
    'start_date' => $eventData['start_date'] ?? (isset($event) ? $event->start_date : null),
    'end_date' => $eventData['end_date'] ?? (isset($event) ? $event->end_date : null),
    'location' => $eventData['location'] ?? (isset($event) ? $event->location : null),
    'description' => $eventData['description'] ?? (isset($event) ? $event->description : ''),
    'status' => $eventData['status'] ?? (isset($event) ? $event->status : null),
    'user_id' => $eventData['user_id'] ?? (isset($event) ? $event->user_id : null),
    'user' => isset($event) ? $event->user : null, // Add user relationship for event-header component
    'tags' => $tagsValue, // String format
    'tags_array' => $tagsArray, // Array format for event-header component
    ];

    // Ensure start_date and end_date are Carbon instances if they exist
    if ($eventObj->start_date && is_string($eventObj->start_date)) {
    try {
    $eventObj->start_date = \Carbon\Carbon::parse($eventObj->start_date);
    } catch (\Exception $e) {
    // Keep as string if parsing fails
    }
    }
    if ($eventObj->end_date && is_string($eventObj->end_date)) {
    try {
    $eventObj->end_date = \Carbon\Carbon::parse($eventObj->end_date);
    } catch (\Exception $e) {
    // Keep as string if parsing fails
    }
    }
    @endphp
    <!-- Event Header Preview -->
    <div class="w-full relative mb-6 -mx-4 sm:-mx-6 md:-mx-8 lg:mx-0">
        <div class="relative w-full">
            <x-events.event-header :event="$eventObj" :section="'info'" :hideActions="true" :previewPosterUrl="$posterUrl" :previewThumbnailUrl="$thumbUrl" :previewMode="true" />
        </div>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Poster</label>
            <input id="poster-input" type="file" name="poster" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-qricket-500 file:text-white hover:file:bg-qricket-600 dark:file:bg-qricket-600 dark:hover:file:bg-qricket-700" onchange="previewPoster(this)">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG, or WEBP • up to 5MB • Landscape orientation recommended</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thumbnail</label>
            <input id="thumbnail-input" type="file" name="thumbnail" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-qricket-500 file:text-white hover:file:bg-qricket-600 dark:file:bg-qricket-600 dark:hover:file:bg-qricket-700" onchange="previewThumbnail(this)">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Square image recommended (1:1 ratio) • up to 5MB</p>
        </div>
    </div>

    <!-- Current Event ID for JS uploads/hydration -->
    @if(isset($eventObj) && isset($eventObj->id) && $eventObj->id)
    <meta name="event-id" content="{{ $eventObj->id }}">
    <script>
        window.currentEventId = {
            {
                (int) $eventObj - > id
            }
        };
    </script>
    @endif

    <!-- Hidden layout JSON field for submission/autosave -->
    <input type="hidden" id="layout_data_input" name="layout_json" value="{{ old('layout_json', isset($layoutData)?json_encode($layoutData):'[]') }}" />

    <!-- Layout Builder Surface -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Layout Components</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Add custom sections to enhance your event page. Each component can display different types of content like schedules, speakers, sponsors, or custom information blocks.</p>
        </div>
        <div id="layoutComponents" class="space-y-4 min-h-[240px]">
            <!-- Components will render here; scripts read/write #layout_json -->
        </div>
    </div>

    <!-- Floating Add Layout Button -->
    <div class="fixed bottom-6 right-6 z-40">
        <button type="button" onclick="openLayoutModal()" class="bg-qricket-500 hover:bg-qricket-600 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center group">
            <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span class="ml-2 text-sm font-medium hidden sm:inline">Add Layout</span>
        </button>
    </div>

    <!-- Layout Manager Component -->
    <x-layout-manager />

    <!-- Layout Selection Modal -->
    @include('organizer.events.components.layout-modal')

    <!-- Footer Configuration (required) -->
    <div class="mt-10 border-t border-gray-200 dark:border-gray-700 pt-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Footer Settings</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Configure the footer that appears at the bottom of your event pages. This section helps attendees contact you and find you on social media.</p>

        <!-- Basic Information (Title/Description removed) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Email</label>
                <input type="email" id="footer_email" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="info@example.com" value="{{ ($footerData['email'] ?? '') }}">
            </div>
        </div>

        <!-- Social Media Links -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Social Media Links</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Facebook URL</label>
                    <input type="url" id="footer_facebook" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="https://facebook.com/yourpage" value="{{ ($footerData['facebook'] ?? '') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Twitter/X URL</label>
                    <input type="url" id="footer_twitter" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="https://x.com/yourpage" value="{{ ($footerData['twitter'] ?? '') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instagram URL</label>
                    <input type="url" id="footer_instagram" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="https://instagram.com/yourpage" value="{{ ($footerData['instagram'] ?? '') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">YouTube URL</label>
                    <input type="url" id="footer_youtube" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="https://youtube.com/yourchannel" value="{{ ($footerData['youtube'] ?? '') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">TikTok URL</label>
                    <input type="url" id="footer_tiktok" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="https://tiktok.com/@yourusername" value="{{ ($footerData['tiktok'] ?? '') }}">
                </div>
            </div>
        </div>

        <div class="mt-4">
            <span class="text-xs text-gray-500 dark:text-gray-400">Footer settings auto-save.</span>
            <span id="save_footer_status" class="ml-2 text-xs text-gray-500 dark:text-gray-400"></span>
        </div>
    </div>
</div>
