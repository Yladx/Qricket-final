@php
    $sponsors = isset($details['sponsors']) && is_array($details['sponsors']) ? $details['sponsors'] : [];
    $title = $details['title'] ?? 'Sponsors';
    // Sponsors should already have converted URLs from the controller
    // But we'll handle cases where they might still be paths
    $toUrl = function ($path) {
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        // If it's already a converted URL, use it
        // Otherwise, fallback to public R2 URL for display
        return 'https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/' . ltrim($path, '/');
    };
@endphp

<div class="p-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
      
        {{ $title }}
    </h3>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 place-items-center">
        @forelse($sponsors as $s)
            @php
                // Support both legacy string entries and new object entries { logo: path }
                $logo = is_array($s) ? ($s['logo'] ?? null) : $s;
                $src = $toUrl($logo);
            @endphp
            @if($src)
            <div class="flex items-center justify-center">
                <img src="{{ $src }}" data-path="{{ $logo }}" alt="Sponsor Logo" class="sponsor-logo max-h-12 object-contain opacity-90 hover:opacity-100 transition duration-200" onerror="this.dataset.err='1'" />
            </div>
            @endif
        @empty
            <div class="col-span-full text-sm text-gray-900 dark:text-gray-400 italic text-center">
                No sponsors available.
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
(function(){
    try {
        const imgs = document.querySelectorAll('.sponsor-logo[data-path]');
        if (!imgs.length) return;
        const eventIdMeta = document.querySelector('meta[name="event-id"]');
        const eventId = (window.currentEventId || eventIdMeta?.getAttribute('content')) ? parseInt(window.currentEventId || eventIdMeta.getAttribute('content')) : null;
        if (!eventId) return; // public pages keep R2 public URL
        
        // Detect if we're on admin page or organizer page
        const isAdminPage = window.location.pathname.includes('/admin/events/');
        const isOrganizerPage = window.location.pathname.includes('/organizer/events/');
        // Use appropriate route based on page type, or try organizer route first (works for both)
        const imageUrlRoute = '/organizer/events/get-fresh-image-url';
        
        imgs.forEach(img => {
            const path = img.getAttribute('data-path');
            if (!path || img.dataset.fresh === '1') return;
            
            // If image src is already a valid URL (from controller conversion), mark as fresh
            if (img.src && (img.src.startsWith('http://') || img.src.startsWith('https://')) && !img.src.includes('pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev')) {
                img.dataset.fresh = '1';
                return;
            }
            
            fetch(imageUrlRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ path, event_id: eventId })
            }).then(r => r.ok ? r.json() : null).then(j => {
                if (j && j.success && j.image_url) {
                    img.src = j.image_url;
                    img.dataset.fresh = '1';
                }
            }).catch(()=>{});
        });
    } catch(_) {}
})();
</script>
@endpush
