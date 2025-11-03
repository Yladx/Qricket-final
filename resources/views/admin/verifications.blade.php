<x-admin-layout title="Organizer Verifications - Qrickett Admin" page-title="Organizer Verifications">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Organizer Verification Requests
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Review and approve organizer verification requests
                </p>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">{{ $verifications->total() }}</span> total requests
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Filter Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.verifications') }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') == '' ? 'border-qricket-500 text-qricket-600 dark:text-qricket-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    All ({{ \App\Models\OrganizerVerification::count() }})
                </a>
                <a href="{{ route('admin.verifications', ['status' => 'pending']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') == 'pending' ? 'border-qricket-500 text-qricket-600 dark:text-qricket-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    Pending ({{ \App\Models\OrganizerVerification::pending()->count() }})
                </a>
                <a href="{{ route('admin.verifications', ['status' => 'approved']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') == 'approved' ? 'border-qricket-500 text-qricket-600 dark:text-qricket-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    Approved ({{ \App\Models\OrganizerVerification::approved()->count() }})
                </a>
                <a href="{{ route('admin.verifications', ['status' => 'rejected']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') == 'rejected' ? 'border-qricket-500 text-qricket-600 dark:text-qricket-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    Rejected ({{ \App\Models\OrganizerVerification::rejected()->count() }})
                </a>
            </nav>
        </div>
    </div>

    <!-- Verifications Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        @if($verifications->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Organization
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Document Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Submitted
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($verifications as $verification)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-qricket-100 dark:bg-qricket-900 flex items-center justify-center">
                                                <span class="text-sm font-medium text-qricket-600 dark:text-qricket-400">
                                                    {{ substr($verification->user->first_name, 0, 1) }}{{ substr($verification->user->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $verification->user->first_name }} {{ $verification->user->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $verification->user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $verification->organization_name }}
                                    </div>
                                    @if($verification->website)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <a href="{{ $verification->website }}" target="_blank" class="hover:text-qricket-600 dark:hover:text-qricket-400">
                                                {{ $verification->website }}
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if(pathinfo($verification->document_file_path, PATHINFO_EXTENSION) === 'pdf')
                                            <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        @endif
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ ucfirst(str_replace('_', ' ', $verification->document_type)) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($verification->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                            Pending
                                        </span>
                                    @elseif($verification->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $verification->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.verification.show', $verification) }}" 
                                           class="text-qricket-600 hover:text-qricket-900 dark:text-qricket-400 dark:hover:text-qricket-300">
                                            View Details
                                        </a>
                                        
                                        @if($verification->status === 'pending')
                                            <form method="POST" action="{{ route('admin.verification.approve', $verification) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                                        onclick="event.preventDefault(); if (typeof Swal !== 'undefined' && window.Swal) { Swal.fire({ title: 'Approve Verification?', text: 'Are you sure you want to approve this verification?', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, approve', cancelButtonText: 'Cancel', confirmButtonColor: '#059669', cancelButtonColor: '#6b7280' }).then((result) => { if (result.isConfirmed) { event.target.closest('form').submit(); } }); } else { if (confirm('Are you sure you want to approve this verification?')) { event.target.closest('form').submit(); } } return false;">
                                                    Approve
                                                </button>
                                            </form>
                                            
                                            <button type="button" 
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    onclick="showRejectModal({{ $verification->id }})">
                                                Reject
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $verifications->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No verification requests</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if(request('status'))
                        No {{ request('status') }} verification requests found.
                    @else
                        No verification requests have been submitted yet.
                    @endif
                </p>
            </div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Reject Verification Request
                </h3>
                
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Rejection Reason
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-gray-300"
                                  placeholder="Please provide a reason for rejection..."
                                  required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="hideRejectModal()"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function showRejectModal(verificationId) {
                document.getElementById('rejectForm').action = `/admin/verifications/${verificationId}/reject`;
                document.getElementById('rejectModal').classList.remove('hidden');
            }
            
            function hideRejectModal() {
                document.getElementById('rejectModal').classList.add('hidden');
                document.getElementById('rejection_reason').value = '';
            }
            
            // Close modal when clicking outside
            document.getElementById('rejectModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideRejectModal();
                }
            });
        </script>
    </x-slot>
</x-admin-layout>



