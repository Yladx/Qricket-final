<!-- Event Preview with Artistic Header Design -->
<div class="relative overflow-hidden rounded-lg mb-8">
    <!-- Background with artistic overlay -->
    <div class="relative h-96 bg-gradient-to-br from-gray-900 via-gray-800 to-black">
        <!-- Thumbnail as background (if uploaded) -->
        <div id="thumbnail-bg" class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20" style="background-image: url('{{ asset('images/placeholder-bg.svg') }}');">
            <!-- Artistic overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-gray-900/40 to-black/80"></div>
            <!-- Abstract pattern overlay -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 left-10 w-32 h-32 bg-red-500 rounded-full blur-xl"></div>
                <div class="absolute top-20 right-20 w-24 h-24 bg-blue-500 rounded-full blur-xl"></div>
                <div class="absolute bottom-20 left-1/4 w-20 h-20 bg-green-500 rounded-full blur-xl"></div>
                <div class="absolute bottom-10 right-1/3 w-28 h-28 bg-yellow-500 rounded-full blur-xl"></div>
            </div>
        </div>

        <!-- Content overlay -->
        <div class="relative z-10 flex h-full">
            <!-- Left side - Event Poster -->
            <div class="flex-shrink-0 w-1/3 p-6 flex items-center justify-center">
                <div id="poster-preview" class="relative bg-white rounded-lg shadow-2xl overflow-hidden" style="width: 200px; height: 280px;">
                    <!-- Poster image -->
                    <div id="poster-image" class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm">Upload Poster</p>
                        </div>
                    </div>
                    <!-- Event title overlay on poster -->
                    <div class="absolute bottom-0 left-0 right-0 bg-black/80 text-white p-3">
                        <h3 id="poster-title" class="text-lg font-bold truncate">{{ $eventData['title'] ?? 'Event Title' }}</h3>
                        <p id="poster-date" class="text-sm opacity-90">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('M d, Y') : 'Event Date' }}</p>
                    </div>
                </div>
            </div>

            <!-- Right side - Event Details -->
            <div class="flex-1 p-6 flex flex-col justify-center text-white">
                <!-- Event Title -->
                <h1 id="event-title" class="text-4xl font-bold mb-4">{{ $eventData['title'] ?? 'Event Title' }}</h1>
                
                <!-- Event ID -->
                <p class="text-gray-300 text-sm mb-4">Event ID: {{ $eventData['id'] ?? 'TBD' }}</p>
                
                <!-- Category Tag -->
                <div class="inline-block mb-4">
                    <span id="category-tag" class="px-3 py-1 bg-blue-500 text-white text-sm font-medium rounded-full">
                        {{ $eventData['category'] ?? 'Category' }}
                    </span>
                </div>
                
                <!-- Description -->
                <p id="event-description" class="text-gray-200 mb-6 leading-relaxed max-w-2xl">
                    {{ Str::limit($eventData['description'] ?? 'Event description will appear here...', 150) }}
                </p>
                
                <!-- Event Details -->
                <div class="space-y-3">
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span id="event-date">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('M d, Y') : 'Event Date' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span id="event-time">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('g:i A') : 'Event Time' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                        <span id="event-location">{{ $eventData['location'] ?? 'Event Location' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <span id="event-capacity">{{ $eventData['max_participants'] ?? '100' }} max participants</span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-4 mt-8">
                    <button type="button" class="px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                        Buy Ticket
                    </button>
                    <button type="button" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                        Save Event
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Navigation Bar -->
    <div class="bg-gray-800 px-6 py-4 rounded-b-lg">
        <div class="flex items-center justify-between">
            <span class="text-white font-bold">EVENT INFO</span>
            <span class="text-gray-400 font-bold">NEWS</span>
        </div>
    </div>
</div>

<script>
// Update preview when files are uploaded
function previewPoster(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('poster-image').style.backgroundImage = `url(${e.target.result})`;
            document.getElementById('poster-image').style.backgroundSize = 'cover';
            document.getElementById('poster-image').style.backgroundPosition = 'center';
            // Hide placeholder
            document.querySelector('#poster-image .text-center').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnail-bg').style.backgroundImage = `url(${e.target.result})`;
            document.getElementById('thumbnail-bg').style.opacity = '0.3';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Update preview when form fields change
document.addEventListener('DOMContentLoaded', function() {
    // Update title
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            document.getElementById('event-title').textContent = this.value || 'Event Title';
            document.getElementById('poster-title').textContent = this.value || 'Event Title';
        });
    }
    
    // Update category
    const categoryInput = document.querySelector('input[name="category"]');
    if (categoryInput) {
        categoryInput.addEventListener('input', function() {
            document.getElementById('category-tag').textContent = this.value || 'Category';
        });
    }
    
    // Update description
    const descriptionInput = document.querySelector('textarea[name="description"]');
    if (descriptionInput) {
        descriptionInput.addEventListener('input', function() {
            document.getElementById('event-description').textContent = this.value || 'Event description will appear here...';
        });
    }
    
    // Update location
    const locationInput = document.querySelector('input[name="location"]');
    if (locationInput) {
        locationInput.addEventListener('input', function() {
            document.getElementById('event-location').textContent = this.value || 'Event Location';
        });
    }
    
    // Update max participants
    const maxParticipantsInput = document.querySelector('input[name="max_participants"]');
    if (maxParticipantsInput) {
        maxParticipantsInput.addEventListener('input', function() {
            document.getElementById('event-capacity').textContent = (this.value || '100') + ' max participants';
        });
    }
});
</script>



    <!-- Background with artistic overlay -->
    <div class="relative h-96 bg-gradient-to-br from-gray-900 via-gray-800 to-black">
        <!-- Thumbnail as background (if uploaded) -->
        <div id="thumbnail-bg" class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20" style="background-image: url('{{ asset('images/placeholder-bg.svg') }}');">
            <!-- Artistic overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-gray-900/40 to-black/80"></div>
            <!-- Abstract pattern overlay -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 left-10 w-32 h-32 bg-red-500 rounded-full blur-xl"></div>
                <div class="absolute top-20 right-20 w-24 h-24 bg-blue-500 rounded-full blur-xl"></div>
                <div class="absolute bottom-20 left-1/4 w-20 h-20 bg-green-500 rounded-full blur-xl"></div>
                <div class="absolute bottom-10 right-1/3 w-28 h-28 bg-yellow-500 rounded-full blur-xl"></div>
            </div>
        </div>

        <!-- Content overlay -->
        <div class="relative z-10 flex h-full">
            <!-- Left side - Event Poster -->
            <div class="flex-shrink-0 w-1/3 p-6 flex items-center justify-center">
                <div id="poster-preview" class="relative bg-white rounded-lg shadow-2xl overflow-hidden" style="width: 200px; height: 280px;">
                    <!-- Poster image -->
                    <div id="poster-image" class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm">Upload Poster</p>
                        </div>
                    </div>
                    <!-- Event title overlay on poster -->
                    <div class="absolute bottom-0 left-0 right-0 bg-black/80 text-white p-3">
                        <h3 id="poster-title" class="text-lg font-bold truncate">{{ $eventData['title'] ?? 'Event Title' }}</h3>
                        <p id="poster-date" class="text-sm opacity-90">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('M d, Y') : 'Event Date' }}</p>
                    </div>
                </div>
            </div>

            <!-- Right side - Event Details -->
            <div class="flex-1 p-6 flex flex-col justify-center text-white">
                <!-- Event Title -->
                <h1 id="event-title" class="text-4xl font-bold mb-4">{{ $eventData['title'] ?? 'Event Title' }}</h1>
                
                <!-- Event ID -->
                <p class="text-gray-300 text-sm mb-4">Event ID: {{ $eventData['id'] ?? 'TBD' }}</p>
                
                <!-- Category Tag -->
                <div class="inline-block mb-4">
                    <span id="category-tag" class="px-3 py-1 bg-blue-500 text-white text-sm font-medium rounded-full">
                        {{ $eventData['category'] ?? 'Category' }}
                    </span>
                </div>
                
                <!-- Description -->
                <p id="event-description" class="text-gray-200 mb-6 leading-relaxed max-w-2xl">
                    {{ Str::limit($eventData['description'] ?? 'Event description will appear here...', 150) }}
                </p>
                
                <!-- Event Details -->
                <div class="space-y-3">
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span id="event-date">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('M d, Y') : 'Event Date' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span id="event-time">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('g:i A') : 'Event Time' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                        <span id="event-location">{{ $eventData['location'] ?? 'Event Location' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <span id="event-capacity">{{ $eventData['max_participants'] ?? '100' }} max participants</span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-4 mt-8">
                    <button type="button" class="px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                        Buy Ticket
                    </button>
                    <button type="button" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                        Save Event
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Navigation Bar -->
    <div class="bg-gray-800 px-6 py-4 rounded-b-lg">
        <div class="flex items-center justify-between">
            <span class="text-white font-bold">EVENT INFO</span>
            <span class="text-gray-400 font-bold">NEWS</span>
        </div>
    </div>
</div>

<script>
// Update preview when files are uploaded
function previewPoster(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('poster-image').style.backgroundImage = `url(${e.target.result})`;
            document.getElementById('poster-image').style.backgroundSize = 'cover';
            document.getElementById('poster-image').style.backgroundPosition = 'center';
            // Hide placeholder
            document.querySelector('#poster-image .text-center').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnail-bg').style.backgroundImage = `url(${e.target.result})`;
            document.getElementById('thumbnail-bg').style.opacity = '0.3';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Update preview when form fields change
document.addEventListener('DOMContentLoaded', function() {
    // Update title
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            document.getElementById('event-title').textContent = this.value || 'Event Title';
            document.getElementById('poster-title').textContent = this.value || 'Event Title';
        });
    }
    
    // Update category
    const categoryInput = document.querySelector('input[name="category"]');
    if (categoryInput) {
        categoryInput.addEventListener('input', function() {
            document.getElementById('category-tag').textContent = this.value || 'Category';
        });
    }
    
    // Update description
    const descriptionInput = document.querySelector('textarea[name="description"]');
    if (descriptionInput) {
        descriptionInput.addEventListener('input', function() {
            document.getElementById('event-description').textContent = this.value || 'Event description will appear here...';
        });
    }
    
    // Update location
    const locationInput = document.querySelector('input[name="location"]');
    if (locationInput) {
        locationInput.addEventListener('input', function() {
            document.getElementById('event-location').textContent = this.value || 'Event Location';
        });
    }
    
    // Update max participants
    const maxParticipantsInput = document.querySelector('input[name="max_participants"]');
    if (maxParticipantsInput) {
        maxParticipantsInput.addEventListener('input', function() {
            document.getElementById('event-capacity').textContent = (this.value || '100') + ' max participants';
        });
    }
});
</script>













    <!-- Background with artistic overlay -->
    <div class="relative h-96 bg-gradient-to-br from-gray-900 via-gray-800 to-black">
        <!-- Thumbnail as background (if uploaded) -->
        <div id="thumbnail-bg" class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20" style="background-image: url('{{ asset('images/placeholder-bg.svg') }}');">
            <!-- Artistic overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-gray-900/40 to-black/80"></div>
            <!-- Abstract pattern overlay -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 left-10 w-32 h-32 bg-red-500 rounded-full blur-xl"></div>
                <div class="absolute top-20 right-20 w-24 h-24 bg-blue-500 rounded-full blur-xl"></div>
                <div class="absolute bottom-20 left-1/4 w-20 h-20 bg-green-500 rounded-full blur-xl"></div>
                <div class="absolute bottom-10 right-1/3 w-28 h-28 bg-yellow-500 rounded-full blur-xl"></div>
            </div>
        </div>

        <!-- Content overlay -->
        <div class="relative z-10 flex h-full">
            <!-- Left side - Event Poster -->
            <div class="flex-shrink-0 w-1/3 p-6 flex items-center justify-center">
                <div id="poster-preview" class="relative bg-white rounded-lg shadow-2xl overflow-hidden" style="width: 200px; height: 280px;">
                    <!-- Poster image -->
                    <div id="poster-image" class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm">Upload Poster</p>
                        </div>
                    </div>
                    <!-- Event title overlay on poster -->
                    <div class="absolute bottom-0 left-0 right-0 bg-black/80 text-white p-3">
                        <h3 id="poster-title" class="text-lg font-bold truncate">{{ $eventData['title'] ?? 'Event Title' }}</h3>
                        <p id="poster-date" class="text-sm opacity-90">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('M d, Y') : 'Event Date' }}</p>
                    </div>
                </div>
            </div>

            <!-- Right side - Event Details -->
            <div class="flex-1 p-6 flex flex-col justify-center text-white">
                <!-- Event Title -->
                <h1 id="event-title" class="text-4xl font-bold mb-4">{{ $eventData['title'] ?? 'Event Title' }}</h1>
                
                <!-- Event ID -->
                <p class="text-gray-300 text-sm mb-4">Event ID: {{ $eventData['id'] ?? 'TBD' }}</p>
                
                <!-- Category Tag -->
                <div class="inline-block mb-4">
                    <span id="category-tag" class="px-3 py-1 bg-blue-500 text-white text-sm font-medium rounded-full">
                        {{ $eventData['category'] ?? 'Category' }}
                    </span>
                </div>
                
                <!-- Description -->
                <p id="event-description" class="text-gray-200 mb-6 leading-relaxed max-w-2xl">
                    {{ Str::limit($eventData['description'] ?? 'Event description will appear here...', 150) }}
                </p>
                
                <!-- Event Details -->
                <div class="space-y-3">
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span id="event-date">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('M d, Y') : 'Event Date' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span id="event-time">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('g:i A') : 'Event Time' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                        <span id="event-location">{{ $eventData['location'] ?? 'Event Location' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <span id="event-capacity">{{ $eventData['max_participants'] ?? '100' }} max participants</span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-4 mt-8">
                    <button type="button" class="px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                        Buy Ticket
                    </button>
                    <button type="button" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                        Save Event
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Navigation Bar -->
    <div class="bg-gray-800 px-6 py-4 rounded-b-lg">
        <div class="flex items-center justify-between">
            <span class="text-white font-bold">EVENT INFO</span>
            <span class="text-gray-400 font-bold">NEWS</span>
        </div>
    </div>
</div>

<script>
// Update preview when files are uploaded
function previewPoster(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('poster-image').style.backgroundImage = `url(${e.target.result})`;
            document.getElementById('poster-image').style.backgroundSize = 'cover';
            document.getElementById('poster-image').style.backgroundPosition = 'center';
            // Hide placeholder
            document.querySelector('#poster-image .text-center').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnail-bg').style.backgroundImage = `url(${e.target.result})`;
            document.getElementById('thumbnail-bg').style.opacity = '0.3';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Update preview when form fields change
document.addEventListener('DOMContentLoaded', function() {
    // Update title
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            document.getElementById('event-title').textContent = this.value || 'Event Title';
            document.getElementById('poster-title').textContent = this.value || 'Event Title';
        });
    }
    
    // Update category
    const categoryInput = document.querySelector('input[name="category"]');
    if (categoryInput) {
        categoryInput.addEventListener('input', function() {
            document.getElementById('category-tag').textContent = this.value || 'Category';
        });
    }
    
    // Update description
    const descriptionInput = document.querySelector('textarea[name="description"]');
    if (descriptionInput) {
        descriptionInput.addEventListener('input', function() {
            document.getElementById('event-description').textContent = this.value || 'Event description will appear here...';
        });
    }
    
    // Update location
    const locationInput = document.querySelector('input[name="location"]');
    if (locationInput) {
        locationInput.addEventListener('input', function() {
            document.getElementById('event-location').textContent = this.value || 'Event Location';
        });
    }
    
    // Update max participants
    const maxParticipantsInput = document.querySelector('input[name="max_participants"]');
    if (maxParticipantsInput) {
        maxParticipantsInput.addEventListener('input', function() {
            document.getElementById('event-capacity').textContent = (this.value || '100') + ' max participants';
        });
    }
});
</script>



    <!-- Background with artistic overlay -->
    <div class="relative h-96 bg-gradient-to-br from-gray-900 via-gray-800 to-black">
        <!-- Thumbnail as background (if uploaded) -->
        <div id="thumbnail-bg" class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20" style="background-image: url('{{ asset('images/placeholder-bg.svg') }}');">
            <!-- Artistic overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-gray-900/40 to-black/80"></div>
            <!-- Abstract pattern overlay -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 left-10 w-32 h-32 bg-red-500 rounded-full blur-xl"></div>
                <div class="absolute top-20 right-20 w-24 h-24 bg-blue-500 rounded-full blur-xl"></div>
                <div class="absolute bottom-20 left-1/4 w-20 h-20 bg-green-500 rounded-full blur-xl"></div>
                <div class="absolute bottom-10 right-1/3 w-28 h-28 bg-yellow-500 rounded-full blur-xl"></div>
            </div>
        </div>

        <!-- Content overlay -->
        <div class="relative z-10 flex h-full">
            <!-- Left side - Event Poster -->
            <div class="flex-shrink-0 w-1/3 p-6 flex items-center justify-center">
                <div id="poster-preview" class="relative bg-white rounded-lg shadow-2xl overflow-hidden" style="width: 200px; height: 280px;">
                    <!-- Poster image -->
                    <div id="poster-image" class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm">Upload Poster</p>
                        </div>
                    </div>
                    <!-- Event title overlay on poster -->
                    <div class="absolute bottom-0 left-0 right-0 bg-black/80 text-white p-3">
                        <h3 id="poster-title" class="text-lg font-bold truncate">{{ $eventData['title'] ?? 'Event Title' }}</h3>
                        <p id="poster-date" class="text-sm opacity-90">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('M d, Y') : 'Event Date' }}</p>
                    </div>
                </div>
            </div>

            <!-- Right side - Event Details -->
            <div class="flex-1 p-6 flex flex-col justify-center text-white">
                <!-- Event Title -->
                <h1 id="event-title" class="text-4xl font-bold mb-4">{{ $eventData['title'] ?? 'Event Title' }}</h1>
                
                <!-- Event ID -->
                <p class="text-gray-300 text-sm mb-4">Event ID: {{ $eventData['id'] ?? 'TBD' }}</p>
                
                <!-- Category Tag -->
                <div class="inline-block mb-4">
                    <span id="category-tag" class="px-3 py-1 bg-blue-500 text-white text-sm font-medium rounded-full">
                        {{ $eventData['category'] ?? 'Category' }}
                    </span>
                </div>
                
                <!-- Description -->
                <p id="event-description" class="text-gray-200 mb-6 leading-relaxed max-w-2xl">
                    {{ Str::limit($eventData['description'] ?? 'Event description will appear here...', 150) }}
                </p>
                
                <!-- Event Details -->
                <div class="space-y-3">
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span id="event-date">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('M d, Y') : 'Event Date' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span id="event-time">{{ isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('g:i A') : 'Event Time' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                        <span id="event-location">{{ $eventData['location'] ?? 'Event Location' }}</span>
                    </div>
                    
                    <div class="flex items-center text-gray-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <span id="event-capacity">{{ $eventData['max_participants'] ?? '100' }} max participants</span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-4 mt-8">
                    <button type="button" class="px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                        Buy Ticket
                    </button>
                    <button type="button" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                        Save Event
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Navigation Bar -->
    <div class="bg-gray-800 px-6 py-4 rounded-b-lg">
        <div class="flex items-center justify-between">
            <span class="text-white font-bold">EVENT INFO</span>
            <span class="text-gray-400 font-bold">NEWS</span>
        </div>
    </div>
</div>

<script>
// Update preview when files are uploaded
function previewPoster(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('poster-image').style.backgroundImage = `url(${e.target.result})`;
            document.getElementById('poster-image').style.backgroundSize = 'cover';
            document.getElementById('poster-image').style.backgroundPosition = 'center';
            // Hide placeholder
            document.querySelector('#poster-image .text-center').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnail-bg').style.backgroundImage = `url(${e.target.result})`;
            document.getElementById('thumbnail-bg').style.opacity = '0.3';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Update preview when form fields change
document.addEventListener('DOMContentLoaded', function() {
    // Update title
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            document.getElementById('event-title').textContent = this.value || 'Event Title';
            document.getElementById('poster-title').textContent = this.value || 'Event Title';
        });
    }
    
    // Update category
    const categoryInput = document.querySelector('input[name="category"]');
    if (categoryInput) {
        categoryInput.addEventListener('input', function() {
            document.getElementById('category-tag').textContent = this.value || 'Category';
        });
    }
    
    // Update description
    const descriptionInput = document.querySelector('textarea[name="description"]');
    if (descriptionInput) {
        descriptionInput.addEventListener('input', function() {
            document.getElementById('event-description').textContent = this.value || 'Event description will appear here...';
        });
    }
    
    // Update location
    const locationInput = document.querySelector('input[name="location"]');
    if (locationInput) {
        locationInput.addEventListener('input', function() {
            document.getElementById('event-location').textContent = this.value || 'Event Location';
        });
    }
    
    // Update max participants
    const maxParticipantsInput = document.querySelector('input[name="max_participants"]');
    if (maxParticipantsInput) {
        maxParticipantsInput.addEventListener('input', function() {
            document.getElementById('event-capacity').textContent = (this.value || '100') + ' max participants';
        });
    }
});
</script>











