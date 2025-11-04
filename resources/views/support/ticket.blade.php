<x-app-layout title="Ticket #{{ $ticket->ticket_number }} - Qrickett">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Ticket #{{ $ticket->ticket_number }}</h2>
            <button onclick="goBack()" class="inline-flex items-center px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-xs font-semibold hover:bg-gray-300 dark:hover:bg-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Post style ticket header -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-qricket-500 text-white flex items-center justify-center text-sm font-semibold">{{ strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}</div>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $ticket->subject }}</h3>
                            @php($badge = [
                                'open' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                            ][$ticket->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ ucwords(str_replace('_',' ', $ticket->status)) }}</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Ticket #{{ $ticket->ticket_number }} • Updated {{ $ticket->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                @if(Auth::guard('admin')->check())
                <div class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $ticket->message ?? '' }}</div>
                @endif
            </div>

            <!-- Comments style replies -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div id="replies-list" class="p-6 space-y-5">
                    @forelse($ticket->replies as $reply)
                        @php(
                            $originMsg = trim((string)($ticket->message ?? ''))
                        )
                        @php(
                            $isDupOfOrigin = trim((string)$reply->message) === $originMsg
                        )
                        @continue($isDupOfOrigin)
                        @php($isMine = ((int)$reply->user_id === (int)Auth::id()))
                        <div class="flex items-start {{ $isMine ? 'flex-row-reverse' : '' }}">
                            <div class="flex-shrink-0 {{ $isMine ? 'ml-3' : '' }} {{ $isMine ? '' : 'mr-3' }}">
                                <div class="h-8 w-8 rounded-full {{ $reply->is_admin_reply ? 'bg-qricket-600' : 'bg-gray-500' }} text-white flex items-center justify-center text-xs font-semibold">
                                    {{ $reply->is_admin_reply ? strtoupper(substr($reply->admin?->username ?? 'A',0,1)) : strtoupper(substr($reply->user?->first_name ?? 'U',0,1)) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center {{ $isMine ? 'justify-between flex-row-reverse' : 'justify-between' }}">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 {{ $isMine ? 'text-right' : '' }}">{{ $reply->is_admin_reply ? ($reply->admin?->username ?? 'Admin') : ($reply->user?->full_name ?? 'You') }}</p>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 {{ $isMine ? '' : '' }}">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-1 {{ $isMine ? 'text-right' : '' }}">
                                    <span class="inline-block px-3 py-2 rounded-lg {{ $isMine ? 'bg-qricket-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }} whitespace-pre-line">{{ $reply->message }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No replies yet.</p>
                    @endforelse
                </div>
                @auth
                    @if($ticket->user_id === Auth::id())
                    <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                        <form id="user-reply-form" method="POST" action="{{ route('support.ticket.reply', $ticket->ticket_number) }}" class="flex items-start gap-3">
                            @csrf
                            <textarea id="user-message" name="message" rows="2" required class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-gray-200" placeholder="Write a reply..."></textarea>
                            <button id="user-send-btn" type="submit" class="px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-md flex items-center">
                                <span id="user-send-text">Send</span>
                                <svg id="user-send-spinner" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </form>
                        <div id="user-message-error" class="text-red-500 text-xs mt-2 hidden"></div>
                    </div>
                    @endif
                @endauth
                @guest
                    <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Guests cannot view tickets in-app. Please check your email for replies and continue the conversation by email.</p>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</x-app-layout>


    <!-- Pass ticket number to JavaScript -->
    <script>
        window.TICKET_NUMBER = @json($ticket->ticket_number);
    </script>
    
    <!-- Load the external JavaScript file -->
    <script src="{{ asset('js/user-support-ticket.js') }}"></script>


<script>
// Smart back navigation
function goBack() {
    const urlParams = new URLSearchParams(window.location.search);
    const referrer = document.referrer;
    
    // Check URL parameter first (more reliable)
    if (urlParams.get('from') === 'notifications') {
        window.location.href = '/notifications';
        return;
    }
    
    // Check referrer as fallback
    if (referrer.includes('/notifications')) {
        window.location.href = '/notifications';
        return;
    }
    
    // Check if coming from profile page
    if (referrer.includes('/profile')) {
        window.location.href = '/profile';
        return;
    }
    
    // Check if coming from support tickets list
    if (referrer.includes('/support/tickets') && !referrer.includes('/support/tickets/')) {
        window.location.href = '/support/tickets';
        return;
    }
    
    // Default: use browser back
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Fallback to support tickets if no history
        window.location.href = '/support/tickets';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        const userIdMeta = document.querySelector('meta[name="csrf-token"]');
        const ticketNumber = @json($ticket->ticket_number);
        const listContainer = document.getElementById('replies-list');
        const originMessage = (document.querySelector('.bg-white.dark\\:bg-gray-800.shadow-sm.rounded-lg .mt-4')?.textContent || '').trim();

        if (typeof Pusher === 'undefined' || !window.PUSHER_APP_KEY) return;

        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const config = {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
            disableStats: true,
            authEndpoint: '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': csrf } },
        };

        const p = new Pusher(window.PUSHER_APP_KEY, config);
        const uid = {{ (int)Auth::id() }};
        const channel = p.subscribe('private-user.' + uid);
        channel.bind('support.reply', function(data) {
            if (!data || data.ticket_number !== ticketNumber) return;
            const incomingText = (data.message || '').trim();
            if (!data.is_admin_reply && originMessage && incomingText === originMessage) {
                return; // Avoid duplicating the original ticket message
            }
            // Append new reply to the list
            const wrapper = document.createElement('div');
            const isMine = Number(data.user_id) === Number({{ (int)Auth::id() }});
            wrapper.className = 'flex items-start ' + (isMine ? 'flex-row-reverse' : '');
            const badgeBg = data.is_admin_reply ? 'bg-qricket-600' : 'bg-gray-500';
            wrapper.innerHTML = `
                <div class="flex-shrink-0 ${isMine ? 'ml-3' : 'mr-3'}">
                    <div class="h-8 w-8 rounded-full ${badgeBg} text-white flex items-center justify-center text-xs font-semibold">
                        ${data.author?.charAt(0)?.toUpperCase() || 'U'}
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center ${isMine ? 'justify-between flex-row-reverse' : 'justify-between'}">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 ${isMine ? 'text-right' : ''}">${data.is_admin_reply ? (data.author || 'Admin') : (data.author || 'You')}</p>
                        <span class="text-xs text-gray-500 dark:text-gray-400">just now</span>
                    </div>
                    <div class="mt-1 ${isMine ? 'text-right' : ''}">
                        <span class="inline-block px-3 py-2 rounded-lg ${isMine ? 'bg-qricket-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'} whitespace-pre-line"></span>
                    </div>
                </div>
            `;
            wrapper.querySelector('.whitespace-pre-line').textContent = data.message;
            if (listContainer) {
                listContainer.appendChild(wrapper);
                listContainer.scrollTop = listContainer.scrollHeight;
            }
        });
    } catch (e) { console.error('Ticket realtime failed', e); }
});
</script>