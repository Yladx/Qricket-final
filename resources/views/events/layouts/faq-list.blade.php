<!-- FAQ List Layout -->
<div class="faq-list-section py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($details['title']) && $details['title'])
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $details['title'] }}</h2>
            @if(isset($details['subtitle']) && $details['subtitle'])
            <p class="text-lg text-gray-900 dark:text-gray-400">{{ $details['subtitle'] }}</p>
            @endif
        </div>
        @endif
        
        <div class="space-y-4">
            @if(isset($details['faqs']) && is_array($details['faqs']))
                @foreach($details['faqs'] as $index => $faq)
                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg group">
                    <summary class="w-full px-6 py-4 text-left flex items-center justify-between cursor-pointer list-none">
                        <span class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ $faq['question'] ?? 'Question ' . ($index + 1) }}
                        </span>
                        <svg class="w-5 h-5 text-gray-900 dark:text-gray-400 transform transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-6 pb-4">
                        <p class="text-gray-900 dark:text-gray-400">
                            {{ $faq['answer'] ?? 'Answer not provided' }}
                        </p>
                    </div>
                </details>
                @endforeach
            @else
                <!-- Default FAQ items if no data provided -->
                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg group">
                    <summary class="w-full px-6 py-4 text-left flex items-center justify-between cursor-pointer list-none">
                        <span class="text-lg font-medium text-gray-900 dark:text-gray-100">What should I bring to the event?</span>
                        <svg class="w-5 h-5 text-gray-900 dark:text-gray-400 transform transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-6 pb-4">
                        <p class="text-gray-900 dark:text-gray-400">Please bring a valid ID, your ticket (digital or printed), and any materials mentioned in the event description.</p>
                    </div>
                </details>
                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg group">
                    <summary class="w-full px-6 py-4 text-left flex items-center justify-between cursor-pointer list-none">
                        <span class="text-lg font-medium text-gray-900 dark:text-gray-100">Is parking available at the venue?</span>
                        <svg class="w-5 h-5 text-gray-900 dark:text-gray-400 transform transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-6 pb-4">
                        <p class="text-gray-900 dark:text-gray-400">Yes, parking is available at the venue. Please arrive early as spaces may be limited.</p>
                    </div>
                </details>
                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg group">
                    <summary class="w-full px-6 py-4 text-left flex items-center justify-between cursor-pointer list-none">
                        <span class="text-lg font-medium text-gray-900 dark:text-gray-100">Can I get a refund if I can't attend?</span>
                        <svg class="w-5 h-5 text-gray-900 dark:text-gray-400 transform transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-6 pb-4">
                        <p class="text-gray-900 dark:text-gray-400">Refunds are available up to 48 hours before the event. Please contact our support team for assistance.</p>
                    </div>
                </details>
            @endif
        </div>
    </div>
</div>
