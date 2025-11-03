<x-admin-layout>
    <x-slot name="title">Support Tickets - Qricket Admin</x-slot>
    <x-slot name="page-title">Support Tickets</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Support Tickets</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review and manage support requests from users and organizers.</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Total: {{ $tickets->total() }} tickets
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label for="status" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-gray-300 text-sm">
                        <option value="">All Statuses</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div>
                    <label for="priority" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                    <select name="priority" id="priority" class="w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-gray-300 text-sm">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                

                <div class="flex items-end">
                    <button type="submit" class="w-full px-3 py-2 bg-qricket-500 hover:bg-qricket-600 text-white font-medium rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 text-sm">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Tickets Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ticket</th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Priority</th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                        {{ $ticket->ticket_number }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                        {{ $ticket->replies->count() }} replies
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 dark:text-gray-100">
                                        {{ $ticket->name }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                        {{ $ticket->email }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-gray-900 dark:text-gray-100 max-w-xs truncate">
                                        {{ $ticket->subject }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $ticket->getStatusBadgeColor() }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $ticket->getPriorityBadgeColor() }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                    {{ $ticket->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs font-medium">
                                    <a href="{{ route('admin.support.show', $ticket) }}" class="text-qricket-600 hover:text-qricket-900 dark:text-qricket-400 dark:hover:text-qricket-300">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No support tickets found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($tickets->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $tickets->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
