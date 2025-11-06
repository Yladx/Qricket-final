@php
    $items = isset($details['testimonials']) && is_array($details['testimonials']) ? $details['testimonials'] : [];
@endphp

<div class="p-4 md:p-6">
    <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 md:mb-4">Testimonials</h3>
    <div class="space-y-3 md:space-y-4">
        @foreach($items as $it)
            <blockquote class="p-3 md:p-4 rounded bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                <p class="text-sm md:text-base italic">"{{ $it['quote'] ?? '' }}"</p>
                <footer class="mt-1 md:mt-2 text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ $it['author'] ?? '' }}</footer>
            </blockquote>
        @endforeach
    </div>
</div>
