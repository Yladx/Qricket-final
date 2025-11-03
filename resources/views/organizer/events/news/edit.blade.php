<x-unified-layout :layout-type="$layoutType" :title="'Edit News Article - ' . $event->title" :page-title="'Edit News Article'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit News Article</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Update news article for {{ $event->title }}</p>
                        </div>
                        <a href="{{ route('organizer.events.news.index', $event) }}" class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-transparent hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back
                        </a>
                    </div>

                    <!-- Edit Form -->
                    <form method="POST" action="{{ route('organizer.events.news.update', [$event, $news]) }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <!-- Hidden input to track images to delete -->
                        <input type="hidden" id="images-to-delete" name="images_to_delete" value="">
                        
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Article Title
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $news->title) }}"
                                   class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:border-qricket-500"
                                   placeholder="Enter article title"
                                   required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div>
                            <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Article Content
                            </label>
                            <textarea id="body" 
                                      name="body" 
                                      rows="12"
                                      class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:border-qricket-500"
                                      placeholder="Write your news article content here..."
                                      required>{{ old('body', $news->body) }}</textarea>
                            @error('body')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Media (images) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Photos</label>
                            <div id="dropzone" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-white/50 dark:bg-gray-700/30 text-center cursor-pointer">
                                <p class="text-sm text-gray-600 dark:text-gray-300">Drag & drop images here or click to upload</p>
                                <p class="text-xs text-gray-400 mt-1">Up to 10 images, PNG/JPG/WebP</p>
                                <input id="images" name="images[]" type="file" accept="image/*" multiple class="hidden">
                            </div>
                            <div id="previews" class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                @if(!empty($news->attachments))
                                    @foreach($news->attachments as $index => $img)
                                        <div class="relative group existing-image" data-index="{{ $index }}">
                                            <img src="{{ $img }}" class="h-28 w-full object-cover rounded-md border border-gray-200 dark:border-gray-600"/>
                                            <button type="button" 
                                                    class="absolute top-1 right-1 hidden group-hover:block bg-red-600 hover:bg-red-700 text-white text-xs px-2 py-1 rounded delete-existing-btn"
                                                    onclick="deleteExistingImage({{ $index }})">
                                                ✕
                                            </button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @error('images')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Visibility -->
                        <div>
                            <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Visibility
                            </label>
                            <select id="visibility" 
                                    name="visibility"
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="public" {{ old('visibility', $news->visibility) == 'public' ? 'selected' : '' }}>Public - Visible to everyone</option>
                                <option value="ticket_buyers" {{ old('visibility', $news->visibility) == 'ticket_buyers' ? 'selected' : '' }}>Ticket Buyers - Only visible to ticket holders</option>
                                <option value="saved_users" {{ old('visibility', $news->visibility) == 'saved_users' ? 'selected' : '' }}>Saved Users - Only visible to users who saved the event</option>
                            </select>
                            @error('visibility')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Publishing Options -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Publishing Options</h3>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="publish_option" 
                                           value="draft" 
                                           {{ !$news->published_at ? 'checked' : '' }}
                                           class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Save as draft</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="publish_option" 
                                           value="publish"
                                           {{ $news->published_at ? 'checked' : '' }}
                                           class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Publish immediately</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="publish_option" 
                                           value="schedule"
                                           class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Schedule for later</span>
                                </label>
                            </div>

                            <!-- Scheduled Date -->
                            <div id="schedule-date" class="mt-3 hidden">
                                <label for="scheduled_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Schedule Date & Time
                                </label>
                                <input type="datetime-local" 
                                       id="scheduled_at" 
                                       name="scheduled_at"
                                       value="{{ old('scheduled_at', $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : '') }}"
                                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:border-qricket-500">
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('organizer.events.news.index', $event) }}" 
                               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-qricket-500 rounded-md hover:bg-qricket-600 transition-colors">
                                Update Article
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Track images to delete
        let imagesToDelete = [];

        function deleteExistingImage(index) {
            const proceedDelete = () => {
                // Add to deletion list
                imagesToDelete.push(index);
                document.getElementById('images-to-delete').value = imagesToDelete.join(',');
                
                // Remove from UI
                const imageElement = document.querySelector(`[data-index="${index}"]`);
                if (imageElement) {
                    imageElement.remove();
                }
            };
            
            if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                    title: 'Delete Image?',
                    text: 'Are you sure you want to delete this image?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        proceedDelete();
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete this image?')) {
                    proceedDelete();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const publishOptions = document.querySelectorAll('input[name="publish_option"]');
            const scheduleDate = document.getElementById('schedule-date');
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('images');
            const previews = document.getElementById('previews');
            let dt = new DataTransfer();

            publishOptions.forEach(option => {
                option.addEventListener('change', function() {
                    if (this.value === 'schedule') {
                        scheduleDate.classList.remove('hidden');
                    } else {
                        scheduleDate.classList.add('hidden');
                    }
                });
            });

            const handleFiles = (files) => {
                Array.from(files).forEach(file => {
                    if (!file.type.startsWith('image/')) return;
                    if (dt.files.length >= 10) return;
                    dt.items.add(file);
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'relative group';
                        wrapper.innerHTML = `
                            <img src="${e.target.result}" class="h-28 w-full object-cover rounded-md border border-gray-200 dark:border-gray-600"/>
                            <button type=\"button\" class=\"absolute top-1 right-1 hidden group-hover:block bg-black/60 text-white text-xs px-2 py-1 rounded remove-btn\">Remove</button>
                        `;
                        previews.appendChild(wrapper);
                        wrapper.querySelector('.remove-btn').addEventListener('click', () => {
                            const filesArr = Array.from(dt.files);
                            const idx = filesArr.findIndex(f => f.name === file.name && f.size === file.size);
                            if (idx > -1) {
                                dt.items.remove(idx);
                                fileInput.files = dt.files;
                            }
                            wrapper.remove();
                        });
                        fileInput.files = dt.files;
                    };
                    reader.readAsDataURL(file);
                });
            };

            dropzone.addEventListener('click', () => fileInput.click());
            dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('ring-2','ring-qricket-500'); });
            dropzone.addEventListener('dragleave', () => dropzone.classList.remove('ring-2','ring-qricket-500'));
            dropzone.addEventListener('drop', (e) => { e.preventDefault(); dropzone.classList.remove('ring-2','ring-qricket-500'); handleFiles(e.dataTransfer.files); });
            fileInput.addEventListener('change', (e) => handleFiles(e.target.files));
        });
    </script>
</x-unified-layout>
