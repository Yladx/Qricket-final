<!-- Center Text Layout -->
<div class="center-text-section py-8 md:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            @if(isset($details['title']) && $details['title'])
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-gray-100 mb-6 md:mb-8">
                {{ $details['title'] }}
            </h2>
            @endif
            
            @if(isset($details['content']) && $details['content'])
            <div class="prose prose-sm md:prose-lg dark:prose-invert max-w-none mx-auto">
                <div class="text-sm md:text-base text-gray-900 dark:text-gray-400 leading-relaxed">
                    {!! $details['content'] !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>