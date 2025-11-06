<!-- FAQ List Layout -->
<div class="faq-list-section py-8 md:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($details['title']) && $details['title'])
        <div class="text-center mb-8 md:mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-3 md:mb-4">{{ $details['title'] }}</h2>
            @if(isset($details['subtitle']) && $details['subtitle'])
            <p class="text-base md:text-lg text-gray-900 dark:text-gray-400">{{ $details['subtitle'] }}</p>
            @endif
        </div>
        @endif
        
        <div class="space-y-3 md:space-y-4">
            @if(isset($details['faqs']) && is_array($details['faqs']))
                @foreach($details['faqs'] as $index => $faq)
                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg group overflow-hidden">
                    <summary class="w-full px-4 md:px-6 py-3 md:py-4 text-left flex items-start sm:items-center justify-between cursor-pointer list-none gap-2 md:gap-3 min-w-0">
                        <span class="text-sm md:text-base lg:text-lg font-medium text-gray-900 dark:text-gray-100 flex-1 pr-2 break-words min-w-0">
                            {{ $faq['question'] ?? 'Question ' . ($index + 1) }}
                        </span>
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-900 dark:text-gray-400 transform transition-transform group-open:rotate-180 flex-shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-4 md:px-6 pb-3 md:pb-4 pt-0">
                        <p class="text-xs md:text-sm lg:text-base text-gray-900 dark:text-gray-400 break-words leading-relaxed">
                            {{ $faq['answer'] ?? 'Answer not provided' }}
                        </p>
                    </div>
                </details>
                @endforeach
            @else
                <!-- Default FAQ items if no data provided -->
                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg group overflow-hidden">
                    <summary class="w-full px-4 md:px-6 py-3 md:py-4 text-left flex items-start sm:items-center justify-between cursor-pointer list-none gap-2 md:gap-3 min-w-0">
                        <span class="text-sm md:text-base lg:text-lg font-medium text-gray-900 dark:text-gray-100 flex-1 pr-2 break-words min-w-0">What should I bring to the event?</span>
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-900 dark:text-gray-400 transform transition-transform group-open:rotate-180 flex-shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-4 md:px-6 pb-3 md:pb-4 pt-0">
                        <p class="text-xs md:text-sm lg:text-base text-gray-900 dark:text-gray-400 break-words leading-relaxed">Please bring a valid ID, your ticket (digital or printed), and any materials mentioned in the event description.</p>
                    </div>
                </details>
                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg group overflow-hidden">
                    <summary class="w-full px-4 md:px-6 py-3 md:py-4 text-left flex items-start sm:items-center justify-between cursor-pointer list-none gap-2 md:gap-3 min-w-0">
                        <span class="text-sm md:text-base lg:text-lg font-medium text-gray-900 dark:text-gray-100 flex-1 pr-2 break-words min-w-0">Is parking available at the venue?</span>
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-900 dark:text-gray-400 transform transition-transform group-open:rotate-180 flex-shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-4 md:px-6 pb-3 md:pb-4 pt-0">
                        <p class="text-xs md:text-sm lg:text-base text-gray-900 dark:text-gray-400 break-words leading-relaxed">Yes, parking is available at the venue. Please arrive early as spaces may be limited.</p>
                    </div>
                </details>
                <details class="bg-gray-50 dark:bg-gray-700 rounded-lg group overflow-hidden">
                    <summary class="w-full px-4 md:px-6 py-3 md:py-4 text-left flex items-start sm:items-center justify-between cursor-pointer list-none gap-2 md:gap-3 min-w-0">
                        <span class="text-sm md:text-base lg:text-lg font-medium text-gray-900 dark:text-gray-100 flex-1 pr-2 break-words min-w-0">Can I get a refund if I can't attend?</span>
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-900 dark:text-gray-400 transform transition-transform group-open:rotate-180 flex-shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="px-4 md:px-6 pb-3 md:pb-4 pt-0">
                        <p class="text-xs md:text-sm lg:text-base text-gray-900 dark:text-gray-400 break-words leading-relaxed">Refunds are available up to 48 hours before the event. Please contact our support team for assistance.</p>
                    </div>
                </details>
            @endif
        </div>
    </div>
</div>
