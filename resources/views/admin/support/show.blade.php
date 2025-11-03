<x-admin-layout>
    <x-slot name="title">Support Ticket {{ $ticket->ticket_number }} - Qricket Admin</x-slot>
    <x-slot name="page-title">Support Ticket: {{ $ticket->ticket_number }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.support') }}" class="text-qricket-600 hover:text-qricket-800 dark:text-qricket-400 dark:hover:text-qricket-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->ticket_number }}</h2>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->getStatusBadgeColor() }}">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->getPriorityBadgeColor() }}">
                    {{ ucfirst($ticket->priority) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-0">
            <!-- Ticket Information -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ticket Information</h3>
                
                <div class="space-y-4" style="max-height:60vh;overflow:auto">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->subject }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Initial Message</label>
                        <div class="mt-1 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                            <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $ticket->message }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversation Section -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-b-lg p-0 overflow-hidden">
                <div class="px-6 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Conversation</h3>
                </div>
                <!-- Chat area -->
                <div id="admin-replies-list" class="px-4 sm:px-6 pb-4 space-y-3 h-[420px] overflow-y-auto scroll-smooth no-scrollbar">
                    @foreach($ticket->replies as $reply)
                        @php($isAdmin = (bool) $reply->is_admin_reply)
                        <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}" data-reply-id="{{ $reply->id }}">
                            <div class="max-w-[80%]">
                                <div class="flex items-center space-x-2 {{ $isAdmin ? 'justify-end flex-row-reverse space-x-reverse' : '' }} mb-1">
                                    <div class="h-7 w-7 rounded-full {{ $isAdmin ? 'bg-qricket-600' : 'bg-gray-300 dark:bg-gray-600' }} flex items-center justify-center text-[11px] font-bold text-white select-none">
                                        {{ strtoupper(substr($reply->author_name,0,1)) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $reply->author_name }}</span>
                                        • {{ $reply->created_at->format('M j, Y g:i A') }}
                                    </div>
                                </div>
                                <div class="rounded-2xl px-4 py-2 text-sm shadow-sm {{ $isAdmin ? 'bg-qricket-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
                                    {{ $reply->message }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- Composer -->
                <form id="admin-reply-form" method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="border-t border-gray-200 dark:border-gray-700 p-4 sm:p-6 bg-gray-50/60 dark:bg-gray-800/60">
                    @csrf
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label for="admin-message" class="sr-only">Reply</label>
                            <textarea id="admin-message" name="message" rows="2" class="w-full resize-none px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-gray-200" placeholder="Type your message... (Shift+Enter for new line)"></textarea>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" id="admin-send-btn" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-qricket-600 hover:bg-qricket-700 text-white font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-qricket-500">
                                <svg id="admin-send-spinner" class="hidden animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="admin-send-text">Send</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Ticket Status (separate control above user info) -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Ticket Status</h3>
                    <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="flex items-center space-x-2">
                        @csrf
                        <select name="status" class="px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-qricket-500 focus:border-qricket-500">
                            <option value="">{{ ucfirst(str_replace('_',' ', $ticket->status)) }}</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-qricket-500 hover:bg-qricket-600 text-white rounded-md text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">Update</button>
                    </form>
                </div>
            </div>

            <!-- User Information -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">User Information</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $ticket->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $ticket->email }}</p>
                    </div>
                    
                    @if($ticket->user)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">User Account</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $ticket->user->full_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Registered User</p>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">User Account</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Guest User</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ticket Details -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ticket Details</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $ticket->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Updated</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $ticket->updated_at->format('M j, Y g:i A') }}</p>
                    </div>
                    
                    @if($ticket->resolved_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolved</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $ticket->resolved_at->format('M j, Y g:i A') }}</p>
                        </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Replies</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $ticket->replies->count() }} messages</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pass data to JavaScript -->
    <script>
        window.TICKET_NUMBER = @json($ticket->ticket_number);
        // console.log('[Admin Support] Ticket number set:', window.TICKET_NUMBER);
    </script>
    
    <!-- Load the JavaScript file -->
    <script src="{{ asset('js/admin-support-show.js') }}?v={{ time() }}"></script>
    <style>
        /* Hide scrollbars in chat list */
        #admin-replies-list.no-scrollbar {
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }
        #admin-replies-list.no-scrollbar::-webkit-scrollbar { /* Chrome, Safari */
            display: none;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const list = document.getElementById('admin-replies-list');
            const textarea = document.getElementById('admin-message');
            const form = document.getElementById('admin-reply-form');

            // Auto-scroll to latest
            const scrollToBottom = () => {
                if (!list) return;
                list.scrollTop = list.scrollHeight;
            };
            scrollToBottom();

            // Observe new messages to keep scrolled to bottom
            if (window.MutationObserver && list) {
                const observer = new MutationObserver(() => {
                    // slight delay to wait for layout
                    requestAnimationFrame(scrollToBottom);
                });
                observer.observe(list, { childList: true, subtree: false });
            }

            // Textarea auto-resize
            const autoResize = () => {
                if (!textarea) return;
                textarea.style.height = 'auto';
                const max = 200; // px cap to avoid oversized composer
                textarea.style.height = Math.min(textarea.scrollHeight, max) + 'px';
            };
            if (textarea) {
                autoResize();
                textarea.addEventListener('input', autoResize);
                // Enter to send, Shift+Enter for newline
                textarea.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        if (form) form.requestSubmit();
                    }
                });
            }
        });
    </script>
</x-admin-layout>
