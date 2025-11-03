<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Support Ticket</h2>
            <button onclick="goBack()" class="inline-flex items-center px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-xs font-semibold hover:bg-gray-300 dark:hover:bg-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back
            </button>
        </div>
    </x-slot>

    <div class="py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Chat Widget Container -->
            <div class="w-full h-[92vh] bg-white dark:bg-gray-800 rounded-xl shadow-lg flex flex-col overflow-hidden border border-qricket-100 dark:border-qricket-900">
                <!-- Header -->
                <div class="bg-gradient-to-r from-qricket-600 to-qricket-500 text-white p-5 flex flex-col gap-3 flex-shrink-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold text-base">Ticket #{{ $ticket->ticket_number }}</div>
                            <div class="text-sm opacity-90 mt-1">{{ $ticket->subject }}</div>
                        </div>
                        <div class="flex flex-col items-end">
                            <div class="text-sm opacity-90">{{ optional($ticket->created_at)->diffForHumans() }}</div>
                            @php
                                $statusBadge = [
                                    'open' => 'border-white/40 bg-white/20',
                                    'in_progress' => 'border-white/40 bg-white/20',
                                    'resolved' => 'border-white/40 bg-white/20',
                                    'closed' => 'border-white/40 bg-white/20',
                                ][$ticket->status] ?? 'border-white/40 bg-white/20';
                            @endphp
                            <span class="mt-1 text-xs border px-2 py-0.5 rounded-md font-semibold {{ $statusBadge }}">{{ ucwords(str_replace('_',' ', $ticket->status)) }}</span>
                        </div>
                    </div>
                    <hr class="border-white/30">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg overflow-hidden border-2 border-white/30 flex-shrink-0 flex items-center justify-center">
                            @php
                                $user = $ticket->user ?? null;
                                $picUrl = null;
                                $initials = 'U';
                                $bgColor = 'bg-qricket-500';
                                
                                if ($user) {
                                    try {
                                        $picUrl = $user->profile_picture_url ?? null;
                                    } catch (\Exception $e) {
                                        $picUrl = null;
                                    }
                                    
                                    $name = $user->full_name ?? 'U';
                                    $initials = strtoupper(substr($name, 0, 1) . (str_contains($name, ' ') ? substr($name, strpos($name, ' ') + 1, 1) : ''));
                                    
                                    $colors = ['bg-qricket-500', 'bg-qricket-600', 'bg-qricket-500'];
                                    $bgColor = $colors[crc32($name) % count($colors)];
                                }
                            @endphp
                            @if($picUrl && !empty($picUrl))
                                <img src="{{ $picUrl }}" alt="Profile" class="w-full h-full object-cover border border-white/30 shadow" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full {{ $bgColor }} hidden items-center justify-center text-white font-medium text-xl shadow">
                                    {{ $initials }}
                                </div>
                            @else
                                <div class="w-full h-full {{ $bgColor }} flex items-center justify-center text-white font-medium text-xl shadow">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="font-bold text-lg leading-tight">{{ optional($ticket->user)->full_name ?? 'Guest User' }} ({{ $ticket->email }})</div>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div id="replies-list" class="flex-1 overflow-auto p-5 bg-gradient-to-b from-qricket-50/40 to-white dark:from-gray-900 dark:to-gray-800 flex flex-col gap-4">
                    <!-- Initial Message (Ticket Message) -->
                    <div class="flex flex-col items-start max-w-[80%]">
                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-1 px-1">You</div>
                        <div class="bg-white dark:bg-gray-700 border border-qricket-100 dark:border-qricket-900 shadow-sm rounded-xl p-3">
                            <div class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $ticket->message }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ optional($ticket->created_at)->format('M d, Y · g:i A') }}</div>
                        </div>
                    </div>

                    <!-- Replies -->
                    @forelse($ticket->replies as $reply)
                        @php($isUser = (bool)$reply->user_id)
                        <div class="flex flex-col {{ $isUser ? 'items-end' : 'items-start' }} max-w-[80%] {{ $isUser ? 'self-end' : '' }}" data-reply-id="{{ $reply->id }}">
                            <div class="text-xs {{ $isUser ? 'text-gray-600 dark:text-gray-400' : 'text-qricket-600 dark:text-qricket-400 font-semibold' }} mb-1 px-1">
                                {{ $isUser ? 'You' : 'Qricket.support' }}
                            </div>
                            <div class="{{ $isUser ? 'bg-gradient-to-r from-qricket-600 to-qricket-500 text-white shadow-md' : 'bg-white dark:bg-gray-700 border border-qricket-100 dark:border-qricket-900 shadow-sm' }} rounded-xl p-3">
                                <div class="text-sm {{ $isUser ? 'text-white' : 'text-gray-800 dark:text-gray-200' }}">{{ $reply->message }}</div>
                                <div class="text-xs {{ $isUser ? 'text-white/80' : 'text-gray-500 dark:text-gray-400' }} mt-1 {{ $isUser ? 'text-right' : '' }}">{{ optional($reply->created_at)->format('M d, Y · g:i A') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No replies yet.</div>
                    @endforelse
                </div>

                <!-- Composer -->
                <div class="border-t border-qricket-100 dark:border-qricket-900 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm p-4 flex items-center gap-3 flex-shrink-0">
                    <form id="user-reply-form" action="{{ route('support.ticket.reply', $ticket->ticket_number) }}" method="POST" class="flex items-center gap-3 flex-1">
                        @csrf
                        <textarea id="user-message" name="message" rows="1" class="flex-1 border border-qricket-100 dark:border-qricket-900 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-qricket-400 dark:focus:ring-qricket-500 outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 resize-none" placeholder="Enter your message..." required></textarea>
                        <button id="user-send-btn" type="submit" class="w-10 h-10 bg-gradient-to-r from-qricket-600 to-qricket-500 rounded-lg flex items-center justify-center shadow-md hover:from-qricket-700 hover:to-qricket-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="user-send-spinner" class="hidden animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <svg id="user-send-icon" xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" class="w-5 h-5">
                                <path d="M2 21l21-9L2 3v7l15 2-15 2v7z"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pass data to JavaScript -->
    <script>
        window.USER_ID = {{ auth()->id() }};
        window.TICKET_NUMBER = @json($ticket->ticket_number);
        window.USER_INITIAL = @json(strtoupper(mb_substr(auth()->user()->first_name ?? 'U', 0, 1)));
        // console.log('[User Support] Window vars set:', {
        //     userId: window.USER_ID,
        //     ticketNumber: window.TICKET_NUMBER,
        //     initial: window.USER_INITIAL
        // });
    </script>
    <script src="{{ asset('js/user-support-ticket.js') }}?v={{ time() }}"></script>

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

    // Auto-resize textarea and scroll to bottom on load
    document.addEventListener('DOMContentLoaded', function() {
        const messageInput = document.getElementById('user-message');
        const repliesList = document.getElementById('replies-list');
        
        // Scroll to bottom on load
        if (repliesList) {
            setTimeout(() => {
                repliesList.scrollTop = repliesList.scrollHeight;
            }, 100);
        }
        
        if (messageInput) {
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
            
            // Handle Enter key (submit on Enter, new line on Shift+Enter)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    document.getElementById('user-reply-form').dispatchEvent(new Event('submit'));
                }
            });
        }
    });
    </script>

</x-app-layout>
