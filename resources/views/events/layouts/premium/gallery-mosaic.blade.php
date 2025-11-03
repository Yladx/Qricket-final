@php
    $photos = isset($details['photos']) && is_array($details['photos']) ? $details['photos'] : [];
@endphp

<div class="grid grid-cols-2 md:grid-cols-3 gap-2">
    @foreach($photos as $p)
        @php $url = $p['url'] ?? null; @endphp
        @if($url)
            <img src="{{ $url }}" class="w-full h-40 object-cover rounded" alt="">
        @endif
    @endforeach
</div>
