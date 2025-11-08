<x-app-layout title="My Support Tickets - Qricket">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">My Support Tickets</h2>
            <button onclick="goBack()" class="inline-flex items-center px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-xs font-semibold hover:bg-gray-300 dark:hover:bg-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="lg:bg-white lg:dark:bg-gray-800 lg:shadow-sm lg:rounded-lg">
                <div class="p-6">
                    <!-- Mobile cards -->
                    <div class="space-y-3 sm:hidden">
                        @forelse($tickets as $ticket)
                            @php($badge = [
                                'open' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                            ][$ticket->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200')
                            <a href="{{ route('support.ticket', $ticket->ticket_number) }}" class="block rounded-lg border border-gray-200 dark:border-gray-700 p-3 transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="text-qricket-600 dark:text-qricket-400 font-mono text-xs">{{ $ticket->ticket_number }}</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </div>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100 line-clamp-1">{{ $ticket->subject }}</div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Created {{ $ticket->created_at->format('M d, Y') }}</div>
                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Updated {{ $ticket->updated_at->diffForHumans() }}</div>
                            </a>
                        @empty
                            <div class="text-center text-gray-500 dark:text-gray-400 py-6">No tickets yet.</div>
                        @endforelse
                    </div>

                    <!-- Desktop table -->
                    <div class="overflow-x-auto hidden sm:block">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ticket #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                            <a href="{{ route('support.ticket', $ticket->ticket_number) }}" class="text-qricket-600 dark:text-qricket-400 hover:underline">
                                                {{ $ticket->ticket_number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                            <a href="{{ route('support.ticket', $ticket->ticket_number) }}" class="hover:underline">
                                                {{ $ticket->subject }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 text-sm">
                                            @php($badge = [
                                                'open' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                                'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                            ][$ticket->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                                {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $ticket->created_at->format('M d, Y') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $ticket->updated_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No tickets yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($tickets, 'links'))
                        <div class="mt-4">{{ $tickets->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
// Smart back navigation
function goBack() {
    const fromParam = @json($from ?? 'help');
    const urlParams = new URLSearchParams(window.location.search);
    const referrer = document.referrer;
    
    // Use server-side from parameter first (most reliable)
    if (fromParam === 'help') {
        window.location.href = '/help';
        return;
    }
    
    if (fromParam === 'profile') {
        window.location.href = '/profile';
        return;
    }
    
    // Check URL parameter as backup
    if (urlParams.get('from') === 'help') {
        window.location.href = '/help';
        return;
    }
    
    if (urlParams.get('from') === 'profile') {
        window.location.href = '/profile';
        return;
    }
    
    // Check referrer as fallback
    if (referrer.includes('/help') || referrer.includes('/support')) {
        window.location.href = '/help';
        return;
    }
    
    // Check if coming from profile page
    if (referrer.includes('/profile')) {
        window.location.href = '/profile';
        return;
    }
    
    // Check if coming from dashboard
    if (referrer.includes('/dashboard')) {
        window.location.href = '/dashboard';
        return;
    }
    
    // Default: use browser back
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Fallback to help page if no history
        window.location.href = '/help';
    }
}
</script>



