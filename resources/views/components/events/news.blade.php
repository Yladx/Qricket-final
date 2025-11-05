@props(['news', 'event', 'canManage' => false])

<!-- Event News Section - Facebook Style -->
@if($news && $news->count() > 0)
<div id="event-news-section" class="space-y-4 mt-6">
        @foreach($news as $newsItem)
            <!-- Facebook Post -->
            <div class="w-full max-w-2xl mx-auto relative bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                
                <!-- Header -->
                <div class="flex items-center justify-between p-3">
                    <div class="flex items-center space-x-2">
                        @if($event->user->profile_picture_url)
                            <img src="{{ $event->user->profile_picture_url }}" 
                                 alt="{{ $event->user->full_name }}" 
                                 class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ substr($event->user->full_name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-200">{{ $event->user->full_name }}</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $newsItem->created_at->format('M j \a\t g:i A') }} · 🌐</p>
                        </div>
                    </div>

                    @if($canManage && !\Illuminate\Support\Facades\Auth::guard('admin')->check())
                    <div class="relative">
                        <button type="button" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full" aria-haspopup="true" aria-expanded="false" data-news-menu="news-menu-{{ $newsItem->id }}">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                        </button>
                        <div id="news-menu-{{ $newsItem->id }}" class="hidden absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10">
                            <a href="{{ route('organizer.events.news.edit', [$event, $newsItem]) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Edit</a>
                            <form method="POST" action="{{ route('organizer.events.news.destroy', [$event, $newsItem]) }}" onsubmit="event.preventDefault(); if (typeof Swal !== 'undefined' && window.Swal) { Swal.fire({ title: 'Delete Post?', text: 'Are you sure you want to delete this post?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it', cancelButtonText: 'Cancel', confirmButtonColor: '#dc2626' }).then((result) => { if (result.isConfirmed) { event.target.submit(); } }); } else { if (confirm('Delete this post?')) { event.target.submit(); } } return false;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30">Delete</button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Post Text -->
                <div class="px-3 pb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-200 mb-1">{{ $newsItem->title }}</h3>
                    <p class="text-sm text-gray-900 dark:text-gray-300 mb-2">
                        @php
                            $bodyText = strip_tags($newsItem->body);
                            $isLongText = strlen($bodyText) > 200;
                        @endphp
                        
                        @if($isLongText)
                            <span id="news-body-short-{{ $newsItem->id }}">
                                {{ Str::limit($bodyText, 200) }}
                                <button onclick="toggleNewsBody({{ $newsItem->id }})" 
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline text-sm ml-1">
                                    View more
                                </button>
                            </span>
                            <span id="news-body-full-{{ $newsItem->id }}" class="hidden">
                                {{ $bodyText }}
                                <button onclick="toggleNewsBody({{ $newsItem->id }})" 
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline text-sm ml-1">
                                    View less
                                </button>
                            </span>
                        @else
                            {{ $bodyText }}
                        @endif
                    </p>

                    <!-- Image Gallery -->
                    @if(!empty($newsItem->attachments))
                        <div id="imageGrid-{{ $newsItem->id }}" class="overflow-hidden rounded-lg"></div>
                    @endif
                </div>
            </div>
        @endforeach
</div>
@else
<!-- No News State -->
<div id="event-news-section" class="w-full max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No News Yet</h3>
        <p class="text-gray-600 dark:text-gray-400">No news for this event yet.</p>
    </div>
</div>
@endif

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center">
    <div class="relative max-w-4xl max-h-full p-4">
        <!-- Close Button -->
        <button onclick="window.closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        <!-- Navigation Arrows -->
        <button id="prevBtn" onclick="window.changeImage(-1)" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button id="nextBtn" onclick="window.changeImage(1)" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        
        <!-- Image Container -->
        <div class="flex flex-col items-center">
            <img id="modalImage" src="" alt="News image" class="max-w-full max-h-[80vh] object-contain rounded-lg transition-opacity duration-300">00
            
            <!-- Image Counter -->
            <div id="imageCounter" class="mt-4 text-white text-sm">
                <span id="currentImage">1</span> / <span id="totalImages">1</span>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables for image modal (accessible across all news items on the page)
window.currentImages = window.currentImages || [];
window.currentImageIndex = window.currentImageIndex || 0;
window.allNewsImages = window.allNewsImages || []; // Store all images from all news items

// Initialize image grids for each news item
document.addEventListener('DOMContentLoaded', function() {
    @if($news && $news->count() > 0)
        // Collect all images from all news items
        @foreach($news as $newsItem)
            @if(!empty($newsItem->attachments) && is_array($newsItem->attachments))
                @foreach($newsItem->attachments as $attachment)
                    @if(!empty($attachment))
                        window.allNewsImages.push({
                            url: @json($attachment),
                            newsId: {{ $newsItem->id }},
                            newsTitle: @json($newsItem->title ?? 'News Post')
                        });
                    @endif
                @endforeach
                @php
                    $attachmentsArray = array_filter($newsItem->attachments ?? [], function($url) {
                        return !empty($url);
                    });
                @endphp
                @if(!empty($attachmentsArray))
                    initializeImageGrid({{ $newsItem->id }}, @json(array_values($attachmentsArray)));
                @endif
            @endif
        @endforeach
    @endif
});

function initializeImageGrid(newsId, images) {
    const container = document.getElementById(`imageGrid-${newsId}`);
    if (!container) {
        console.warn(`Image container not found for news ID: ${newsId}`);
        return;
    }
    
    if (!images || images.length === 0) {
        console.warn(`No images provided for news ID: ${newsId}`);
        return;
    }
    
    const imageCount = images.length;
    let html = '';

    // Find the starting index in allNewsImages for this news item
    let startIndex = -1;
    for (let i = 0; i < window.allNewsImages.length; i++) {
        if (window.allNewsImages[i].newsId === newsId) {
            startIndex = i;
            break;
        }
    }
    
    // Validate that we have valid image URLs
    const validImages = images.filter(img => img && typeof img === 'string' && img.length > 0);
    if (validImages.length === 0) {
        console.warn(`No valid image URLs for news ID: ${newsId}`);
        return;
    }

    // Use validImages instead of images
    const displayImages = validImages;
    const displayCount = displayImages.length;
    
    // 1 image = full width
    if (displayCount === 1) {
        html = `
            <img src="${displayImages[0]}" class="w-full h-72 object-cover cursor-pointer hover:opacity-90 transition-opacity" alt="post image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImage not available%3C/text%3E%3C/svg%3E';" onclick="window.openImageModalFromAll(${startIndex >= 0 ? startIndex : 0})">
        `;
    }
    // 2 images = side by side
    else if (displayCount === 2) {
        html = `
            <div class="grid grid-cols-2 gap-1">
                ${displayImages.slice(0, 2).map((img, i) => `<img src="${img}" class="w-full h-60 object-cover cursor-pointer hover:opacity-90 transition-opacity" alt="post image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImage not available%3C/text%3E%3C/svg%3E';" onclick="window.openImageModalFromAll(${startIndex >= 0 ? startIndex + i : i})">`).join('')}
            </div>
        `;
    }
    // 3 images = one on top, two below
    else if (displayCount === 3) {
        html = `
            <div class="grid grid-rows-2 gap-1">
                <img src="${displayImages[0]}" class="w-full h-48 object-cover cursor-pointer hover:opacity-90 transition-opacity" alt="post image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImage not available%3C/text%3E%3C/svg%3E';" onclick="window.openImageModalFromAll(${startIndex >= 0 ? startIndex : 0})">
                <div class="grid grid-cols-2 gap-1">
                    ${displayImages.slice(1, 3).map((img, i) => `<img src="${img}" class="w-full h-36 object-cover cursor-pointer hover:opacity-90 transition-opacity" alt="post image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImage not available%3C/text%3E%3C/svg%3E';" onclick="window.openImageModalFromAll(${startIndex >= 0 ? startIndex + i + 1 : i + 1})">`).join('')}
                </div>
            </div>
        `;
    }
    // 4 or more images = grid (2x2)
    else if (displayCount >= 4) {
        html = `
            <div class="grid grid-cols-2 gap-1 relative">
                ${displayImages.slice(0, 4).map((img, i) => {
                    if (i === 3 && displayCount > 4) {
                        return `
                            <div class="relative cursor-pointer" onclick="window.openImageModalFromAll(${startIndex >= 0 ? startIndex + i : i})">
                                <img src="${img}" class="w-full h-36 object-cover brightness-75 hover:brightness-90 transition-all" alt="post image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImage not available%3C/text%3E%3C/svg%3E';">
                                <div class="absolute inset-0 flex items-center justify-center text-white font-semibold text-lg bg-black/40">
                                    +${displayCount - 4} more
                                </div>
                            </div>
                        `;
                    }
                    return `<img src="${img}" class="w-full h-36 object-cover cursor-pointer hover:opacity-90 transition-opacity" alt="post image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImage not available%3C/text%3E%3C/svg%3E';" onclick="window.openImageModalFromAll(${startIndex >= 0 ? startIndex + i : i})">`;
                }).join('')}
            </div>
        `;
    }

    container.innerHTML = html;
    console.log(`Image grid initialized for news ID ${newsId} with ${displayCount} images`);
}

function toggleNewsBody(newsId) {
    const shortDiv = document.getElementById(`news-body-short-${newsId}`);
    const fullDiv = document.getElementById(`news-body-full-${newsId}`);
    
    if (fullDiv.classList.contains('hidden')) {
        // Show full body
        shortDiv.classList.add('hidden');
        fullDiv.classList.remove('hidden');
    } else {
        // Show short body
        shortDiv.classList.remove('hidden');
        fullDiv.classList.add('hidden');
    }
}

// Open modal from all images (new function that uses allNewsImages)
window.openImageModalFromAll = function(index) {
    if (!window.allNewsImages || window.allNewsImages.length === 0) {
        // Fallback to old method if allNewsImages is empty
        console.warn('No images available in allNewsImages');
        return;
    }
    
    window.currentImageIndex = Math.max(0, Math.min(index, window.allNewsImages.length - 1));
    window.currentImages = window.allNewsImages.map(img => img.url);
    
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const currentImageSpan = document.getElementById('currentImage');
    const totalImagesSpan = document.getElementById('totalImages');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (!modal || !modalImage) {
        console.error('Image modal elements not found');
        return;
    }
    
    // Set initial image
    modalImage.src = window.currentImages[window.currentImageIndex];
    currentImageSpan.textContent = window.currentImageIndex + 1;
    totalImagesSpan.textContent = window.currentImages.length;
    
    // Show/hide navigation buttons
    if (prevBtn && nextBtn) {
        prevBtn.style.display = window.currentImages.length > 1 ? 'block' : 'none';
        nextBtn.style.display = window.currentImages.length > 1 ? 'block' : 'none';
    }
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

// Legacy function for backward compatibility (can be removed if not used elsewhere)
window.openImageModal = function(images, index) {
    // If allNewsImages is available, find the image in the global array
    if (window.allNewsImages && window.allNewsImages.length > 0 && images && images.length > 0) {
        const imageUrl = images[index];
        const globalIndex = window.allNewsImages.findIndex(img => img.url === imageUrl);
        if (globalIndex >= 0) {
            window.openImageModalFromAll(globalIndex);
            return;
        }
    }
    
    // Fallback to old behavior
    window.currentImages = images;
    window.currentImageIndex = index;
    
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const currentImageSpan = document.getElementById('currentImage');
    const totalImagesSpan = document.getElementById('totalImages');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (!modal || !modalImage) {
        console.error('Image modal elements not found');
        return;
    }
    
    // Set initial image
    modalImage.src = window.currentImages[window.currentImageIndex];
    currentImageSpan.textContent = window.currentImageIndex + 1;
    totalImagesSpan.textContent = window.currentImages.length;
    
    // Show/hide navigation buttons
    if (prevBtn && nextBtn) {
        prevBtn.style.display = window.currentImages.length > 1 ? 'block' : 'none';
        nextBtn.style.display = window.currentImages.length > 1 ? 'block' : 'none';
    }
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

window.closeImageModal = function() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
};

window.changeImage = function(direction) {
    if (!window.currentImages || window.currentImages.length === 0) return;
    
    window.currentImageIndex += direction;
    
    // Loop around
    if (window.currentImageIndex >= window.currentImages.length) {
        window.currentImageIndex = 0;
    } else if (window.currentImageIndex < 0) {
        window.currentImageIndex = window.currentImages.length - 1;
    }
    
    const modalImage = document.getElementById('modalImage');
    const currentImageSpan = document.getElementById('currentImage');
    
    if (!modalImage || !currentImageSpan) return;
    
    modalImage.src = window.currentImages[window.currentImageIndex];
    currentImageSpan.textContent = window.currentImageIndex + 1;
    
    // Optional: Add smooth transition
    modalImage.style.opacity = '0';
    setTimeout(() => {
        modalImage.style.opacity = '1';
    }, 100);
};

// Close modal on escape key, navigate with arrow keys
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('imageModal');
    if (modal && !modal.classList.contains('hidden')) {
        if (e.key === 'Escape') {
            window.closeImageModal();
        } else if (e.key === 'ArrowLeft') {
            window.changeImage(-1);
        } else if (e.key === 'ArrowRight') {
            window.changeImage(1);
        }
    }
});

// Close modal on background click
const imageModalElement = document.getElementById('imageModal');
if (imageModalElement) {
    imageModalElement.addEventListener('click', function(e) {
        if (e.target === this) {
            window.closeImageModal();
        }
    });
}

// Simple popover toggle for news 3-dot menus
document.addEventListener('click', function(e){
    var btn = e.target.closest('[data-news-menu]');
    // Close all menus first
    document.querySelectorAll('[id^="news-menu-"]').forEach(function(m){ m.classList.add('hidden'); });
    if(btn){
        var id = btn.getAttribute('data-news-menu');
        var menu = document.getElementById(id);
        if(menu){ menu.classList.toggle('hidden'); }
    }
});
</script>
