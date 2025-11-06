<div id="pending-events-container" class="divide-y divide-gray-200 dark:divide-gray-700">
    @forelse($pendingEvents as $event)
        <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    @if($event->thumbnail_url)
                        <img src="{{ $event->thumbnail_url }}" alt="{{ $event->title }}" class="w-10 h-10 rounded-md object-cover">
                    @else
                        <div class="w-10 h-10 rounded-md bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $event->title ?? 'Untitled Event' }}</h3>
                    <div class="flex items-center space-x-2 mt-0.5">
                        @if($event->user?->profile_picture_url)
                            <img src="{{ $event->user->profile_picture_url }}" alt="{{ $event->user->first_name }}" class="w-4 h-4 rounded-full object-cover">
                        @else
                            @php($initial = $event->user ? strtoupper(substr(trim(($event->user->first_name ?? '') . ' ' . ($event->user->last_name ?? '')),0,1)) : 'U')
                            <div class="w-4 h-4 rounded-full bg-qricket-600 text-white flex items-center justify-center text-[8px] font-semibold">{{ $initial }}</div>
                        @endif
                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ $event->user?->first_name }} {{ $event->user?->last_name }}</span>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Submitted {{ optional($event->evr_submitted_at)->diffForHumans() ?? 'N/A' }}</div>
                </div>
                <div class="flex-shrink-0">
                    @if($event->status === 'draft')
                        <a href="{{ route('admin.events.approval.show', $event->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Preview</a>
                    @elseif($event->status === 'pending_approval')
                        <a href="{{ route('admin.events.approval.show', $event->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-qricket-600 rounded-md hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">Review</a>
                    @elseif(in_array($event->status, ['approved', 'published']))
                        <a href="{{ route('admin.event.manage', $event->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Manage</a>
                    @else
                        <a href="{{ route('admin.events.approval.show', $event->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-qricket-600 rounded-md hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">Review</a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="px-4 py-8 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No pending approvals</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">All caught up! No events are waiting for review.</p>
        </div>
    @endforelse
</div>
@if($pendingEvents->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        <div class="flex items-center justify-between">
            {{ $pendingEvents->links() }}
            <a href="{{ route('admin.events.pending') }}" class="ml-4 inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300 border border-qricket-300 dark:border-qricket-600 rounded-md hover:bg-qricket-50 dark:hover:bg-qricket-900/20 transition-colors duration-200">
                View More
                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
@elseif($pendingEvents->count() > 0)
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-center">
        <a href="{{ route('admin.events.pending') }}" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300 border border-qricket-300 dark:border-qricket-600 rounded-md hover:bg-qricket-50 dark:hover:bg-qricket-900/20 transition-colors duration-200">
            View More
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
@endif
