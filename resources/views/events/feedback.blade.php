<x-app-layout>
    <x-slot name="title">Event Feedback - {{ $event->title }}</x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Give Feedback</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Rate each question from 1–5 stars. All ratings are required.</p>

                    @if($existing)
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Feedback Already Submitted</h3>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">You already submitted your feedback for this event.</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('events.show', $event) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 dark:text-blue-200 dark:bg-blue-800 dark:hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Back to Event
                                </a>
                            </div>
                        </div>
                    @else

                    <form method="POST" action="{{ route('events.feedback.submit', $event) }}" id="feedbackForm">
                        @csrf
                        <input type="hidden" name="responses[version]" value="1" />
                        <!-- Stepper -->
                        <div class="mt-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span data-step-chip="1" class="inline-flex h-7 min-w-[28px] items-center justify-center rounded-full text-xs font-medium bg-qricket-600 text-white">1</span>
                                    <span data-step-chip="2" class="inline-flex h-7 min-w-[28px] items-center justify-center rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">2</span>
                                    <span data-step-chip="3" class="inline-flex h-7 min-w-[28px] items-center justify-center rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">3</span>
                                </div>
                                <span id="stepIndicator" class="text-xs text-gray-600 dark:text-gray-400" aria-live="polite">Section 1 of 3</span>
                            </div>
                            <div class="mt-3 h-1.5 w-full rounded bg-gray-100 dark:bg-gray-700">
                                <div id="stepProgress" class="h-1.5 rounded bg-qricket-600" style="width: 33%;"></div>
                            </div>
                        </div>

                        <div class="space-y-8 mt-6">
                            <!-- Step 1 -->
                            <div id="step-1" class="min-h-[220px]">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Overall Experience</h3>
                                @php($sec='responses[overall_experience]')
                                <x-feedback-stars label="How would you rate your overall experience at the event?" name="{{$sec}}[experience]" :value="data_get($existing,'responses.overall_experience.experience')" />
                                <x-feedback-stars label="Did the event meet your expectations?" name="{{$sec}}[expectations]" :value="data_get($existing,'responses.overall_experience.expectations')" />
                                <x-feedback-stars label="How satisfied are you with the event’s organization?" name="{{$sec}}[organization]" :value="data_get($existing,'responses.overall_experience.organization')" />
                                <x-feedback-stars label="Would you attend this event again in the future?" name="{{$sec}}[attend_again]" :value="data_get($existing,'responses.overall_experience.attend_again')" />
                                <x-feedback-stars label="How likely are you to recommend this event to others?" name="{{$sec}}[recommend]" :value="data_get($existing,'responses.overall_experience.recommend')" />
                            </div>

                            <!-- Step 2 -->
                            <div id="step-2" class="hidden min-h-[220px]">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Logistics & Organization</h3>
                                @php($sec='responses[logistics]')
                                <x-feedback-stars label="How satisfied were you with the event's schedule and timing?" name="{{$sec}}[schedule]" :value="data_get($existing,'responses.logistics.schedule')" />
                                <x-feedback-stars label="Was the registration process smooth and efficient?" name="{{$sec}}[registration]" :value="data_get($existing,'responses.logistics.registration')" />
                                <x-feedback-stars label="How would you rate the venue or online platform quality?" name="{{$sec}}[venue]" :value="data_get($existing,'responses.logistics.venue')" />
                                <x-feedback-stars label="Were you satisfied with the event's technical setup?" name="{{$sec}}[technical]" :value="data_get($existing,'responses.logistics.technical')" />
                                <x-feedback-stars label="Were the staff or organizers helpful and approachable?" name="{{$sec}}[staff]" :value="data_get($existing,'responses.logistics.staff')" />
                            </div>

                            <!-- Step 3 -->
                            <div id="step-3" class="hidden min-h-[220px]">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Comments & Suggestions</h3>
                                
                                <!-- Open feedback (optional) -->
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">What did you like most about this event?</label>
                                        <textarea name="responses[comments][liked]" rows="3" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md" placeholder="Share what you enjoyed most...">{{ data_get($existing,'responses.comments.liked') }}</textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">What could be improved for future events?</label>
                                        <textarea name="responses[comments][improvements]" rows="3" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md" placeholder="Suggest improvements...">{{ data_get($existing,'responses.comments.improvements') }}</textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Additional comments or suggestions</label>
                                        <textarea name="responses[comments][additional]" rows="3" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md" placeholder="Any other feedback...">{{ data_get($existing,'responses.comments.additional') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="mt-8 flex items-center justify-between">
                            <button id="backBtn" class="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-transparent hover:bg-gray-50 dark:hover:bg-gray-700 transition hidden" type="button">Back</button>
                            <div class="ml-auto flex items-center gap-3">
                                <button id="nextBtn" class="px-5 py-2.5 bg-qricket-600 hover:bg-qricket-700 text-white rounded-md" type="button">Next</button>
                                <button id="submitBtn" class="px-5 py-2.5 bg-qricket-600 hover:bg-qricket-700 text-white rounded-md hidden" type="submit">Submit Feedback</button>
                            </div>
                        </div>
                    </form>
                </div>
    </div>
    </div>
    </div>
    @endif
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Star rating behavior
        document.querySelectorAll('[data-rating-group]').forEach(function(group){
            const input = group.querySelector('input[type="hidden"]');
            const stars = group.querySelectorAll('[data-star]');
            const update = (val) => {
                input.value = val;
                stars.forEach(btn => {
                    const active = parseInt(btn.value) <= val;
                    btn.classList.toggle('text-yellow-400', active);
                    btn.classList.toggle('text-gray-400', !active);
                    btn.classList.toggle('dark:text-gray-500', !active);
                });
            };
            stars.forEach(btn => btn.addEventListener('click', () => update(parseInt(btn.value))));
            update(parseInt(input.value || '0'));
        });

        // Stepper controls
        const total = 3;
        let current = 1;
        const indicator = document.getElementById('stepIndicator');
        const progress = document.getElementById('stepProgress');
        const chips = document.querySelectorAll('[data-step-chip]');
        const backBtn = document.getElementById('backBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        function showStep(n){
            for (let i=1;i<=total;i++){
                const el = document.getElementById('step-'+i);
                if (el) { el.classList.toggle('hidden', i !== n); }
            }
            indicator.textContent = `Section ${n} of ${total}`;
            const pct = Math.round((n/total) * 100);
            if (progress) progress.style.width = pct + '%';
            chips.forEach(chip => {
                const isActive = parseInt(chip.getAttribute('data-step-chip')) === n;
                chip.classList.toggle('bg-qricket-600', isActive);
                chip.classList.toggle('text-white', isActive);
                chip.classList.toggle('bg-gray-100', !isActive);
                chip.classList.toggle('text-gray-600', !isActive);
                chip.classList.toggle('dark:bg-gray-700', !isActive);
                chip.classList.toggle('dark:text-gray-300', !isActive);
            });
            backBtn.classList.toggle('hidden', n === 1);
            nextBtn.classList.toggle('hidden', n === total);
            submitBtn.classList.toggle('hidden', n !== total);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        nextBtn.addEventListener('click', function(){ 
            // Validate current step before proceeding
            if (current === 1 || current === 2) {
                // Check if all star ratings are filled
                const currentStep = document.getElementById('step-' + current);
                const starInputs = currentStep.querySelectorAll('input[type="hidden"]');
                let allFilled = true;
                
                starInputs.forEach(input => {
                    if (!input.value || input.value === '0') {
                        allFilled = false;
                    }
                });
                
                if (!allFilled) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Ratings',
                        text: 'Please rate all questions before proceeding.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    return;
                }
            }
            
            current = Math.min(total, current + 1); 
            showStep(current); 
        });
        
        // Form submission validation
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            // Check if all star ratings are filled
            const starInputs = document.querySelectorAll('input[type="hidden"]');
            let allFilled = true;
            
            starInputs.forEach(input => {
                if (!input.value || input.value === '0') {
                    allFilled = false;
                }
            });
            
            if (!allFilled) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Ratings',
                    text: 'Please rate all questions before submitting.',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }
        });
        
        backBtn.addEventListener('click', function(){ current = Math.max(1, current - 1); showStep(current); });

        showStep(current);
    });
</script>