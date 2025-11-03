// Event Step 1 - Event Handlers (initMap is defined in the HTML)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Event handlers loaded');
});

function setupEventListeners() {
    console.log('Setting up event listeners');
    
    // Search input change
    const searchInput = document.getElementById('location-search');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchInput);
    }

    // Autocomplete selection
    if (autocomplete) {
        autocomplete.addListener('place_changed', handlePlaceSelect);
    }

    // Marker drag
    if (marker) {
        marker.addListener('dragend', handleMarkerDrag);
    }

    // Map click
    if (map) {
        map.addListener('click', handleMapClick);
    }
}

function handleSearchInput() {
    const searchInput = document.getElementById('location-search');
    const locationInput = document.getElementById('location');
    
    if (searchInput.value.trim() === '') {
        locationInput.value = '';
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
    }
}

function handlePlaceSelect() {
    const place = autocomplete.getPlace();
    
    if (!place.geometry || !place.geometry.location) {
        console.log('No details available for input: ' + place.name);
        return;
    }

    // Update map center and marker
    map.setCenter(place.geometry.location);
    marker.setPosition(place.geometry.location);

    // Update form fields
    updateLocationFields(place.geometry.location, place.formatted_address);
}

function handleMarkerDrag() {
    const position = marker.getPosition();
    geocodePosition(position);
}

function handleMapClick(event) {
    const position = event.latLng;
    marker.setPosition(position);
    geocodePosition(position);
}

function geocodePosition(position) {
    geocoder.geocode({
        location: position
    }, function(results, status) {
        if (status === 'OK' && results[0]) {
            updateLocationFields(position, results[0].formatted_address);
        } else {
            console.error('Geocoder failed due to: ' + status);
            updateLocationFields(position, 'Custom Location');
        }
    });
}

function updateLocationFields(position, address) {
    // Update hidden coordinate fields
    document.getElementById('latitude').value = position.lat();
    document.getElementById('longitude').value = position.lng();
    
    // Update location input
    document.getElementById('location').value = address;
    
    // Clear search input
    document.getElementById('location-search').value = '';
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const location = document.getElementById('location').value;
            const latitude = document.getElementById('latitude').value;
            const longitude = document.getElementById('longitude').value;
            
            if (!location || !latitude || !longitude) {
                e.preventDefault();
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Location Required',
                        text: 'Please select a location by searching or clicking on the map.',
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    alert('Please select a location by searching or clicking on the map.');
                }
                return false;
            }
        });
    }
});