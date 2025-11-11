<x-unified-layout :layout-type="$layoutType" :title="'Edit Event - Basic Information'" :page-title="'Edit Event'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form Column -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <form id="editBasicForm" method="POST" action="{{ route('organizer.events.step1.store') }}?id={{ $event->id }}" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                @include('organizer.events.forms.basic-info', ['eventId' => $event->id, 'eventData' => $eventData])

                                <!-- Navigation Buttons -->
                                <div class="flex items-center justify-between pt-6">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('organizer.events.manage', ['event' => $event->id]) }}" class="inline-flex items-center px-6 py-3 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-lg font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-500 dark:active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                            Cancel
                                        </a>
                                    </div>

                                    <button type="submit" id="submitBtn" class="inline-flex items-center px-6 py-3 bg-emerald-500 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-emerald-600 focus:bg-emerald-600 active:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <span id="submitText">Save Changes</span>
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" id="submitSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Column -->
                <div class="lg:col-span-1">
                    <div class="sticky top-6 space-y-4">
                        <!-- Event Creation Guide Sidebar -->
                        <div class="bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-lg p-4 shadow-sm">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-teal-900 dark:text-teal-100 mb-2">Event Creation Guide</h4>
                                    <div class="text-sm text-teal-800 dark:text-teal-200 space-y-2">
                                        <ul class="list-disc list-inside space-y-1.5 ml-2 text-xs">
                                            <li><strong>Event Title:</strong> Choose a clear, descriptive title that helps attendees understand what your event is about.</li>
                                            <li><strong>Category:</strong> Select the most appropriate category to help attendees discover your event.</li>
                                            <li><strong>Description:</strong> Provide detailed information about your event. This helps attendees understand what to expect.</li>
                                            <li><strong>Tags:</strong> Add relevant tags (required) to help categorize and improve discoverability. Separate multiple tags with commas.</li>
                                            <li><strong>Date & Time:</strong> Set the start and end dates/times for your event. This helps attendees plan their attendance.</li>
                                            <li><strong>Location:</strong> Enter the physical venue address or online meeting link. Optionally add a Google Maps URL for directions.</li>
                                        </ul>
                                        <div class="mt-3 p-2 bg-teal-100 dark:bg-teal-900/30 rounded border border-teal-200 dark:border-teal-700">
                                            <p class="text-xs font-medium text-teal-900 dark:text-teal-100 mb-1">💡 Tip:</p>
                                            <p class="text-xs text-teal-800 dark:text-teal-200">Your event is automatically saved as a draft. You can come back to edit it anytime before publishing.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Free Event Information Sidebar -->
                        <div class="bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-lg p-4 shadow-sm">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-teal-900 dark:text-teal-100 mb-2">What is a Free Event?</h4>
                                    <div class="text-sm text-teal-800 dark:text-teal-200 space-y-2">
                                        <p>A <strong>Free Event</strong> is an event where attendees don't pay anything to participate. When you mark your event as free:</p>
                                        <ul class="list-disc list-inside space-y-1 ml-2 text-xs">
                                            <li><strong>All tickets are automatically set to ₱0.00</strong> - You cannot set prices for tickets in free events</li>
                                            <li><strong>Special ticket limits apply</strong> - Free events have different ticket quantity limits based on your subscription plan</li>
                                            <li><strong>No payment processing</strong> - Attendees can register and get tickets instantly without any payment</li>
                                            <li><strong>Perfect for:</strong> Workshops, seminars, community events, educational sessions, networking events, or any event where you want to offer free access</li>
                                        </ul>
                                        <div class="mt-3 p-2 bg-teal-100 dark:bg-teal-900/30 rounded border border-teal-200 dark:border-teal-700">
                                            <p class="text-xs font-medium text-teal-900 dark:text-teal-100 mb-1">📋 Important Notes:</p>
                                            <ul class="text-xs text-teal-800 dark:text-teal-200 space-y-0.5 list-disc list-inside ml-2">
                                                <li>Once you create a free event, you cannot change it to a paid event later</li>
                                                <li>Free events are great for building your audience and community engagement</li>
                                                <li>You can still track attendance and generate certificates for free events</li>
                                                <li>Ticket limits for free events may be different from paid events - check your subscription plan details</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Maps API -->
    @if(config('services.google_maps.api_key'))
    <script>
        let map;
        let marker;
        let geocoder;
        let autocomplete;

        function initMap() {
            // Initialize map
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: { lat: -6.200000, lng: 106.816666 }, // Jakarta coordinates
            });

            // Initialize geocoder
            geocoder = new google.maps.Geocoder();

            // Initialize marker
            marker = new google.maps.Marker({
                position: { lat: -6.199000, lng: 106.816666 },
                map: map,
                draggable: true,
            });

            // Create autocomplete for location input
            const locationInput = document.getElementById('location');
            autocomplete = new google.maps.places.Autocomplete(locationInput);
            autocomplete.bindTo('bounds', map);

            // Listen for place selection
            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) {
                    console.log("No details available for input: '" + place.name + "'");
                    return;
                }

                // Update map and marker
                map.setCenter(place.geometry.location);
                marker.setPosition(place.geometry.location);
                
                // Update hidden coordinates
                document.getElementById('latitude').value = place.geometry.location.lat();
                document.getElementById('longitude').value = place.geometry.location.lng();
            });

            // Listen for marker drag
            marker.addListener('dragend', () => {
                const position = marker.getPosition();
                document.getElementById('latitude').value = position.lat();
                document.getElementById('longitude').value = position.lng();

                // Reverse geocode to update address
                geocoder.geocode({ location: position }, (results, status) => {
                    if (status === 'OK' && results[0]) {
                        document.getElementById('location').value = results[0].formatted_address;
                    }
                });
            });

            // Listen for map click
            map.addListener('click', (event) => {
                marker.setPosition(event.latLng);
                document.getElementById('latitude').value = event.latLng.lat();
                document.getElementById('longitude').value = event.latLng.lng();

                // Reverse geocode to update address
                geocoder.geocode({ location: event.latLng }, (results, status) => {
                    if (status === 'OK' && results[0]) {
                        document.getElementById('location').value = results[0].formatted_address;
                    }
                });
            });

            // Set initial coordinates if they exist
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            if (lat && lng) {
                const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
                map.setCenter(position);
                marker.setPosition(position);
            }
        }

        // Fallback if Google Maps fails to load
        function handleMapError() {
            console.error('Google Maps failed to load');
            document.getElementById('map').innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Map could not be loaded. Please enter the location manually.</div>';
        }
    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&callback=initMap" onerror="handleMapError()"></script>
    @else
    <script>
        // Fallback script when no Google Maps API key is available
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Google Maps API key not configured. Using fallback mode.');
            
            const locationInput = document.getElementById('location');
            if (locationInput) {
                locationInput.addEventListener('input', function() {
                    // Simple fallback - just store the text location
                    console.log('Location entered:', this.value);
                });
            }
        });
    </script>
    @endif

    <!-- SweetAlert2 + Utilities (ensures dark mode theme and shared helpers) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/sweetalert-utils.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // AJAX Form Submission
            const form = document.getElementById('editBasicForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');

            if (form && submitBtn) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    // Show loading state
                    submitBtn.disabled = true;
                    submitText.textContent = 'Saving...';
                    submitSpinner.classList.remove('hidden');

                    try {
                        const formData = new FormData(form);
                        
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Show success message (dark-mode aware)
                            if (typeof showSuccess === 'function') {
                                await showSuccess('Success!', data.message || 'Event information updated successfully.', { toast: false });
                            }
                            // Redirect back to event management
                            window.location.href = "{{ route('organizer.events.manage', ['event' => $event->id]) }}";
                        } else {
                            // Show error message
                            let errorMessage = data.message || 'An error occurred while saving.';
                            
                            if (data.errors && Array.isArray(data.errors)) {
                                errorMessage = data.errors.join('<br>');
                            } else if (data.error_details) {
                                // Convert validation errors to readable format
                                const errorList = [];
                                for (const field in data.error_details) {
                                    errorList.push('<strong>' + field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ') + ':</strong> ' + data.error_details[field][0]);
                                }
                                errorMessage = errorList.join('<br>');
                            }

                            if (typeof showError === 'function') {
                                await showError('Validation Error', errorMessage, { html: true });
                            } else { alert(errorMessage.replace(/<br>/g, '\n')); }
                        }
                    } catch (error) {
                        console.error('Edit basic info submission error:', error);
                        
                        if (typeof showError === 'function') {
                            await showError('Error!', 'Network error occurred while saving. Please check your connection and try again.');
                        } else { alert('Network error occurred while saving. Please check your connection and try again.'); }
                    } finally {
                        // Reset button state
                        submitBtn.disabled = false;
                        submitText.textContent = 'Save Changes';
                        submitSpinner.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</x-unified-layout>