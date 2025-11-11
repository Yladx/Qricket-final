<x-unified-layout :layout-type="$layoutType" :title="'Edit Event - Basic Information'" :page-title="'Edit Event'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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