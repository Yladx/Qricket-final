@props(['section', 'event'])

<!-- Event Info Section -->
<div id="event-info-section" class="p-6 relative z-10" style="display: {{ $section !== 'news' ? 'block' : 'none' }};">
    <!-- Layout Components Container -->
    <div id="layout-components-display" class="space-y-8 relative z-10">
        <!-- This will be populated by JavaScript with the actual event layout -->
    </div>
    
    <!-- Organizer Info Component (Auto-loaded at bottom) -->
    @if(isset($event))
        <div class="mt-8">
            @include('events.layouts.organizer-info', ['event' => $event, 'details' => ['title' => 'Event Organizer']])
        </div>
    @endif
</div>
