// My Purchases page JS extracted from Blade
let currentEventId = null;
let selectedUsers = [];

window.toggleIndividualTickets = function(eventId) {
    const element = document.getElementById(`individual-tickets-${eventId}`);
    if (!element) return;
    element.classList.toggle('hidden');
};

window.openShareModal = function(eventId, totalTickets) {
    currentEventId = eventId;
    selectedUsers = [];
    const modal = document.getElementById('shareModal');
    if (modal) modal.classList.remove('hidden');
    const availableTickets = getAvailableTicketCount(eventId);
    const el = document.getElementById('availableTicketCount');
    if (el) el.textContent = availableTickets;
    updateSelectedUsersDisplay();
};

function getAvailableTicketCount(eventId) {
    const ticketsList = document.getElementById(`tickets-list-${eventId}`);
    if (!ticketsList) return 0;
    // Count only tickets that are not shared/gifted and are marked shareable
    return Array.from(ticketsList.querySelectorAll('.ticket-item'))
        .filter(item => item.getAttribute('data-shareable') === 'true')
        .length;
}

window.closeShareModal = function() {
    const modal = document.getElementById('shareModal');
    if (modal) modal.classList.add('hidden');
    const search = document.getElementById('userSearch');
    if (search) search.value = '';
    const results = document.getElementById('searchResults');
    if (results) results.classList.add('hidden');
};

function updateSelectedUsersDisplay() {
    const container = document.getElementById('selectedUsers');
    const section = document.getElementById('selectedUsersSection');
    
    if (!container || !section) return;
    
    if (selectedUsers.length === 0) {
        // Hide the entire section when no users are selected
        section.classList.add('hidden');
        return;
    }
    
    // Show the section when users are selected
    section.classList.remove('hidden');
    
    container.innerHTML = selectedUsers.map(user => `
        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-600 rounded-lg border border-gray-200 dark:border-gray-500 mb-2">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-qricket-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                    ${user.name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">${user.name}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">${user.email}</div>
                </div>
            </div>
            <button onclick="removeUser(${user.id})" class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `).join('');
}

window.removeUser = function(userId) {
    selectedUsers = selectedUsers.filter(u => u.id !== userId);
    updateSelectedUsersDisplay();
};

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('userSearch');
    const resultsContainer = document.getElementById('searchResults');
    if (input) {
        input.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            if (query.length < 2) {
                if (resultsContainer) resultsContainer.classList.add('hidden');
                return;
            }
            fetch(`/api/users/search?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                if (!resultsContainer) return;
                if (data.success && data.users && data.users.length > 0) {
                    resultsContainer.innerHTML = data.users.map(user => `
                        <div class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer" onclick="selectUser(${user.id}, '${user.name}', '${user.email}')">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center text-xs font-medium">
                                    ${user.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">${user.name}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">${user.email}</div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                    resultsContainer.classList.remove('hidden');
                } else {
                    resultsContainer.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">No users found</div>';
                    resultsContainer.classList.remove('hidden');
                }
            })
            .catch(() => {
                if (!resultsContainer) return;
                resultsContainer.innerHTML = '<div class="px-3 py-2 text-sm text-red-500">Search failed. Please try again.</div>';
                resultsContainer.classList.remove('hidden');
            });
        });
    }

    const modal = document.getElementById('shareModal');
    if (modal) {
        modal.addEventListener('click', function(e) { if (e.target === this) closeShareModal(); });
    }

    // Attach hide-shared toggles
    document.querySelectorAll("[id^='hide-shared-']").forEach(cb => {
        cb.addEventListener('change', (e) => {
            e.stopPropagation();
            const eventId = cb.id.replace('hide-shared-','');
            toggleSharedTickets(eventId);
        });
        cb.addEventListener('click', (e) => e.stopPropagation());
        cb.parentElement?.addEventListener('click', (e) => e.stopPropagation());
    });
});

window.selectUser = function(id, name, email) {
    if (!selectedUsers.find(u => u.id === id)) {
        selectedUsers.push({ id, name, email });
        updateSelectedUsersDisplay();
    }
    const input = document.getElementById('userSearch');
    const results = document.getElementById('searchResults');
    if (input) input.value = '';
    if (results) results.classList.add('hidden');
};

window.shareTickets = function() {
    if (selectedUsers.length === 0) {
        Swal.fire('Error', 'Please select at least one user to share with.', 'error');
        return;
    }
    const availableTickets = getAvailableTicketCount(currentEventId);
    if (availableTickets < selectedUsers.length) {
        Swal.fire('Error', `You only have ${availableTickets} ticket(s) available, but trying to share with ${selectedUsers.length} user(s).`, 'error');
        return;
    }
    Swal.fire({
        title: 'Share Tickets',
        text: `Share 1 ticket each with ${selectedUsers.length} user(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, share',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Sharing tickets...', text: 'Please wait while we process your request.', allowOutsideClick: false, showConfirmButton: false, willOpen: () => { Swal.showLoading(); }});
        fetch('/api/tickets/share', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ event_id: currentEventId, users: selectedUsers.map(u => u.id) })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ title: 'Success', text: 'Tickets shared successfully!', icon: 'success', timer: 1200, showConfirmButton: false });
                // Update available ticket count and clear selected users without reload
                closeShareModal();
                try {
                    const countEl = document.getElementById(`tickets-count-${currentEventId}`);
                    if (countEl) {
                        const num = parseInt(countEl.textContent || '0', 10);
                        countEl.textContent = String(Math.max(0, num - selectedUsers.length));
                    }
                } catch (e) { /* no-op */ }
                logTicketSharing(currentEventId, selectedUsers.length);
            } else {
                Swal.fire('Error', data.message || 'Failed to share tickets.', 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Failed to share tickets.', 'error'));
    });
};

window.viewTicketDetails = function(ticketId) {
    window.location.href = `/tickets/details/ticket/${ticketId}`;
};

window.respondToShareRequest = function(shareId, action) {
    const actionText = action === 'accept' ? 'accept' : 'decline';
    Swal.fire({
        title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Share Request`,
        text: `Are you sure you want to ${actionText} this ticket share request?`,
        icon: action === 'accept' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonText: `Yes, ${actionText}`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: action === 'accept' ? '#10b981' : '#ef4444'
    }).then((result) => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Processing...', text: `Please wait while we ${actionText} the request.`, allowOutsideClick: false, showConfirmButton: false, willOpen: () => { Swal.showLoading(); }});
        fetch(`/api/tickets/share/${shareId}/${action}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ title: 'Success', text: data.message, icon: 'success', timer: 1200, showConfirmButton: false });
                // Optimistically update UI without reload
                try {
                    const card = document.getElementById(`share-request-${shareId}`);
                    if (card) {
                        const eventId = card.getAttribute('data-event-id');
                        card.remove();
                        if (action === 'accept') {
                            // Increase visible ticket count by 1
                            const countEl = document.getElementById(`tickets-count-${eventId}`);
                            if (countEl) {
                                const num = parseInt(countEl.textContent || '0', 10);
                                countEl.textContent = String(num + 1);
                            }
                            // Optionally append a new ticket tag in the list (skip for brevity)
                        }
                    }
                } catch (e) { /* no-op */ }
            } else {
                Swal.fire('Error', data.message || `Failed to ${actionText} the request.`, 'error');
            }
        })
        .catch(() => Swal.fire('Error', `Failed to ${actionText} the request.`, 'error'));
    });
};

function logTicketSharing(eventId, userCount) {
    fetch('/api/tickets/share/log', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: JSON.stringify({ event_id: eventId, quantity: userCount, user_count: userCount })
    }).then(() => {}).catch(() => {});
}

// Hide shared tickets toggle
window.toggleSharedTickets = function(eventId) {
    const cb = document.getElementById(`hide-shared-${eventId}`);
    const hide = cb && cb.checked;
    const container = document.getElementById(`tickets-list-${eventId}`);
    if (!container) return;
    container.querySelectorAll('.ticket-item').forEach(item => {
        const isShared = item.getAttribute('data-shared') === 'true';
        if (hide && isShared) {
            item.classList.add('hidden');
        } else {
            item.classList.remove('hidden');
        }
    });
};


