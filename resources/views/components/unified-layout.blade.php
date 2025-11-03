@props(['layoutType' => 'organizer', 'title' => '', 'pageTitle' => '', 'header' => null])

@if($layoutType === 'admin')
<x-admin-layout :title="$title" :page-title="$pageTitle">
    @if($header)
    <x-slot name="header">
        {{ $header }}
    </x-slot>
    @endif
    {{ $slot }}
</x-admin-layout>
@else
<x-app-layout>
    <x-slot name="title">{{ $title }}</x-slot>
    @if($header)
    <x-slot name="header">
        {{ $header }}
    </x-slot>
    @endif
    {{ $slot }}
</x-app-layout>
@endif

