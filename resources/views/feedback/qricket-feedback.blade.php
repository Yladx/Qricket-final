<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give Feedback - Qricket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .star-rating {
            display: inline-flex;
            gap: 2px;
        }
        .star {
            cursor: pointer;
            font-size: 24px;
            color: #d1d5db;
            transition: color 0.2s;
        }
        .star.active {
            color: #fbbf24;
        }
        .star:hover {
            color: #fbbf24;
        }
    </style>
</head>
<body class="bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Give Feedback</h1>
            <p class="text-gray-400">Rate each question from 1–5 stars. This only takes a minute.</p>
        </div>

        <!-- Progress Indicator -->
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center space-x-4">
                <div class="flex space-x-2">
                    <div class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm font-semibold">1</div>
                    <div class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm font-semibold">2</div>
                    <div class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm font-semibold">3</div>
                </div>
                <span class="text-gray-400 text-sm">Section 3 of 3</span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-700 rounded-full h-2 mb-8">
            <div class="bg-teal-500 h-2 rounded-full" style="width: 100%"></div>
        </div>

        <form id="feedbackForm" class="space-y-8">
            @csrf
            
            <!-- Qricket Website Section -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-blue-400 mb-6">Qricket Website</h2>
                
                <!-- Question 1 -->
                <div class="mb-6">
                    <p class="text-white mb-3">How easy was it to navigate the Qricket website?</p>
                    <div class="star-rating" data-field="navigation_ease">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                </div>

                <!-- Question 2 -->
                <div class="mb-6">
                    <p class="text-white mb-3">Did you find all the information you needed about the event on Qricket?</p>
                    <div class="star-rating" data-field="information_found">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                </div>

                <!-- Question 3 -->
                <div class="mb-6">
                    <p class="text-white mb-3">How would you rate the design and overall appearance of the Qricket website?</p>
                    <div class="star-rating" data-field="design_rating">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                </div>

                <!-- Question 4 -->
                <div class="mb-6">
                    <p class="text-white mb-3">Did the website load quickly and function properly on your device?</p>
                    <div class="star-rating" data-field="performance_rating">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                </div>

                <!-- Question 5 -->
                <div class="mb-6">
                    <p class="text-white mb-3">How satisfied are you with the registration or ticketing process on Qricket?</p>
                    <div class="star-rating" data-field="process_satisfaction">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                </div>
            </div>

            <!-- Open Feedback Section -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Optional: Open Feedback</h3>
                <p class="text-gray-400 mb-4">What improvements or new features would you like to see on the Qricket website?</p>
                <textarea 
                    name="improvements_suggestions" 
                    rows="4" 
                    class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-transparent resize-none"
                    placeholder="Share your suggestions..."
                ></textarea>
            </div>

            <!-- Additional Comments -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Additional comments or suggestions</h3>
                <textarea 
                    name="additional_comments" 
                    rows="4" 
                    class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-teal-500 focus:border-transparent resize-none"
                    placeholder="Any other thoughts or feedback..."
                ></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center">
                <button 
                    type="submit" 
                    class="bg-teal-500 hover:bg-teal-600 text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200"
                >
                    Submit Feedback
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Star rating functionality
            document.querySelectorAll('.star-rating').forEach(rating => {
                const stars = rating.querySelectorAll('.star');
                const fieldName = rating.dataset.field;
                
                stars.forEach((star, index) => {
                    star.addEventListener('click', function() {
                        const rating = index + 1;
                        
                        // Update visual state
                        stars.forEach((s, i) => {
                            s.classList.toggle('active', i < rating);
                        });
                        
                        // Store rating value
                        rating.dataset.value = rating;
                    });
                });
            });

            // Form submission
            document.getElementById('feedbackForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                // Collect star ratings
                document.querySelectorAll('.star-rating').forEach(rating => {
                    const fieldName = rating.dataset.field;
                    const value = rating.dataset.value;
                    if (value) {
                        formData.append(fieldName, value);
                    }
                });

                // Submit feedback
                fetch('{{ route("qricket.feedback.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Thank You!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Close modal or redirect
                            if (window.opener) {
                                window.close();
                            } else {
                                window.history.back();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Failed to submit feedback',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to submit feedback. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        });
    </script>
</body>
</html>
