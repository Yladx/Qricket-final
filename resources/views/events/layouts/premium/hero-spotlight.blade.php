@php
    $headline = $details['headline'] ?? '';
    $sub = $details['subheadline'] ?? '';
    $bgUrl = $details['background_image'] ?? $details['background_image_url'] ?? null;
@endphp

<section class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
    @if($bgUrl)
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('{{ $bgUrl }}')"></div>
        <div class="absolute inset-0 bg-black/50"></div>
    @endif
    <div class="relative p-6 md:p-10 text-white">
        <h2 class="text-2xl md:text-3xl font-bold mb-2">{{ $headline ?: 'Spotlight' }}</h2>
        @if($sub)
            <p class="text-base md:text-lg opacity-90">{{ $sub }}</p>
        @endif
    </div>
</section>
