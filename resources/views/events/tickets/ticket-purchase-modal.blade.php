<!-- Ticket Purchase Modal -->
<div id="ticket-purchase-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Buy Tickets</h3>
                <button onclick="closeTicketModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <!-- Event Info -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ $event->title }}</h4>
                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex items-center mb-1">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y g:i A') }}
                        </div>
                        @if($event->location)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $event->location }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Purchase Form -->
                <form id="ticket-purchase-form">
                    @csrf
                    <input type="hidden" id="event-id" value="{{ $event->id }}">
                    
                    <!-- Ticket Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Select Ticket Type</label>
                        <div id="ticket-types-container" class="space-y-3">
                            <!-- Ticket types will be loaded here -->
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="mb-6">
                        <h5 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Customer Information</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name *</label>
                                <input type="text" id="customer_name" name="customer_name" required
                                       class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-white"
                                       value="{{ Auth::user()->full_name }}">
                            </div>
                            <div>
                                <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                                <input type="email" id="customer_email" name="customer_email" required
                                       class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-white"
                                       value="{{ Auth::user()->email }}">
                            </div>
                            <div class="md:col-span-2">
                                <label for="customer_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number *</label>
                                <input type="tel" id="customer_phone" name="customer_phone" required
                                       class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-white"
                                       placeholder="+62 812 3456 7890">
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Summary -->
                    <div id="purchase-summary" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hidden">
                        <h5 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Purchase Summary</h5>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Ticket Type:</span>
                                <span id="summary-ticket-type" class="font-medium text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Quantity:</span>
                                <span id="summary-quantity" class="font-medium text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Unit Price:</span>
                                <span id="summary-unit-price" class="font-medium text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="border-t border-gray-300 dark:border-gray-600 pt-2">
                                <div class="flex justify-between">
                                    <span class="text-lg font-medium text-gray-900 dark:text-white">Total:</span>
                                    <span id="summary-total" class="text-lg font-medium text-qricket-600 dark:text-qricket-400"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeTicketModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-md transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" id="purchase-btn"
                                class="px-6 py-2 text-sm font-medium text-white bg-qricket-500 hover:bg-qricket-600 rounded-md transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="purchase-btn-text">Proceed to Payment</span>
                            <span id="purchase-btn-loading" class="hidden">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Load ticket types when modal opens
function loadTicketTypes() {
    const eventId = document.getElementById('event-id').value;
    
    if (!eventId) {
        console.error('No event ID found');
        Swal.fire('Error', 'Event ID not found', 'error');
        return;
    }
    
    console.log('Loading ticket types for event:', eventId);
    
    // Show loading state
    const container = document.getElementById('ticket-types-container');
    container.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-qricket-500 mx-auto mb-4"></div>
            <p class="text-gray-500 dark:text-gray-400">Loading tickets...</p>
        </div>
    `;
    
    fetch(`/api/events/${eventId}/ticket-types`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Ticket types data:', data);
            if (data.success) {
                renderTicketTypes(data.ticket_types, data.settings || {});
            } else {
                console.error('API returned error:', data.message);
                Swal.fire('Error', data.message || 'Failed to load ticket types', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading ticket types:', error);
            Swal.fire('Error', 'Failed to load ticket types: ' + error.message, 'error');
        });
}

function renderTicketTypes(ticketTypes, settings = {}) {
    const container = document.getElementById('ticket-types-container');
    const maxTicketsPerOrder = settings.max_tickets_per_order || 10;
    const limitPerUser = settings.limit_per_user || false;
    const maxTicketsPerUser = settings.max_tickets_per_user || 1;
    
    // Store settings globally for validation
    window.ticketSettings = settings;
    
    if (ticketTypes.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 text-lg font-medium mb-2">No tickets available</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm">Tickets for this event are not currently on sale or have sold out.</p>
            </div>
        `;
        // Disable purchase button when no tickets available
        document.getElementById('purchase-btn').disabled = true;
        return;
    }
    
    // Show purchase limits info
    let limitsInfo = '';
    if (limitPerUser) {
        limitsInfo = `
            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-700 dark:text-blue-300">
                        <strong>Purchase Limit:</strong> Maximum ${maxTicketsPerUser} ticket(s) per user for this event.
                        ${maxTicketsPerOrder !== maxTicketsPerUser ? `Maximum ${maxTicketsPerOrder} ticket(s) per order.` : ''}
                    </div>
                </div>
            </div>
        `;
    } else if (maxTicketsPerOrder !== 10) {
        limitsInfo = `
            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-700 dark:text-blue-300">
                        <strong>Order Limit:</strong> Maximum ${maxTicketsPerOrder} ticket(s) per order.
                    </div>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = limitsInfo + ticketTypes.map(ticket => `
        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 ticket-option" data-ticket-id="${ticket.id}">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center">
                    <input type="checkbox" id="select-ticket-${ticket.id}" class="mr-3 rounded border-gray-300 text-qricket-600 focus:ring-qricket-500" onchange="toggleTicketSelection('${ticket.id}')">
                    <h6 class="font-medium text-gray-900 dark:text-white">${ticket.name}</h6>
                </div>
                <span class="text-lg font-semibold text-qricket-600 dark:text-qricket-400">₱${parseInt(ticket.price).toLocaleString('en-US')}</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 ml-6">${ticket.description || 'No description available'}</p>
            <div class="flex items-center justify-between ml-6">
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="changeQuantity('${ticket.id}', -1)" aria-label="Decrease quantity for ${(ticket.name || 'ticket').replace(/'/g, '&apos;')}" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 flex items-center justify-center text-gray-600 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed" id="decrease-${ticket.id}">-</button>
                    <span id="quantity-${ticket.id}" class="w-8 text-center font-medium text-gray-900 dark:text-white" data-max-quantity="${maxTicketsPerOrder}" data-max-per-user="${limitPerUser ? maxTicketsPerUser : ''}" data-ticket-price="${ticket.price}" aria-live="polite">1</span>
                    <button type="button" onclick="changeQuantity('${ticket.id}', 1)" aria-label="Increase quantity for ${(ticket.name || 'ticket').replace(/'/g, '&apos;')}" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 flex items-center justify-center text-gray-600 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed" id="increase-${ticket.id}">+</button>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Available: ${ticket.quantity ? (ticket.quantity - (ticket.sold_count || 0)) : 'Unlimited'}</span>
            </div>
        </div>
    `).join('');
    
    // Initialize ticket selection state
    initializeTicketSelection();
}

function changeQuantity(ticketId, change) {
    const checkbox = document.getElementById(`select-ticket-${ticketId}`);
    if (!checkbox.checked) {
        return; // Don't allow quantity changes if ticket is not selected
    }
    
    const quantityElement = document.getElementById(`quantity-${ticketId}`);
    let currentQuantity = parseInt(quantityElement.textContent);
    let newQuantity = currentQuantity + change;
    
    // Get max quantity from ticket option data attribute or default to 10
    const maxQuantity = parseInt(quantityElement.dataset.maxQuantity) || 10;
    
    // Check if there's a max per user limit
    const maxPerUser = quantityElement.dataset.maxPerUser;
    if (maxPerUser) {
        const maxPerUserLimit = parseInt(maxPerUser);
        if (newQuantity > maxPerUserLimit) {
            Swal.fire({
                title: 'Purchase Limit Reached',
                text: `You can only purchase up to ${maxPerUserLimit} ticket(s) for this event.`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
    }
    
    if (newQuantity >= 1 && newQuantity <= maxQuantity) {
        quantityElement.textContent = newQuantity;
        updatePurchaseSummary();
    }
}

function toggleTicketSelection(ticketId) {
    const checkbox = document.getElementById(`select-ticket-${ticketId}`);
    const quantityElement = document.getElementById(`quantity-${ticketId}`);
    const decreaseBtn = document.getElementById(`decrease-${ticketId}`);
    const increaseBtn = document.getElementById(`increase-${ticketId}`);
    
    if (checkbox.checked) {
        // Enable quantity controls
        decreaseBtn.disabled = false;
        increaseBtn.disabled = false;
        quantityElement.style.opacity = '1';
    } else {
        // Disable quantity controls and reset to 1
        decreaseBtn.disabled = true;
        increaseBtn.disabled = true;
        quantityElement.style.opacity = '0.5';
        quantityElement.textContent = '1';
    }
    
    updatePurchaseSummary();
}

function initializeTicketSelection() {
    // Initially all tickets are unselected and quantity controls are disabled
    document.querySelectorAll('.ticket-option').forEach(option => {
        const ticketId = option.dataset.ticketId;
        const checkbox = document.getElementById(`select-ticket-${ticketId}`);
        const quantityElement = document.getElementById(`quantity-${ticketId}`);
        const decreaseBtn = document.getElementById(`decrease-${ticketId}`);
        const increaseBtn = document.getElementById(`increase-${ticketId}`);
        
        if (!checkbox.checked) {
            decreaseBtn.disabled = true;
            increaseBtn.disabled = true;
            quantityElement.style.opacity = '0.5';
        }
    });
    
    updatePurchaseSummary();
}

function updatePurchaseSummary() {
    const selectedTickets = [];
    let totalAmount = 0;
    let totalQuantity = 0;
    
    // Get all selected tickets
    document.querySelectorAll('.ticket-option').forEach(option => {
        const ticketId = option.dataset.ticketId;
        const checkbox = document.getElementById(`select-ticket-${ticketId}`);
        
        if (checkbox.checked) {
            const quantityElement = document.getElementById(`quantity-${ticketId}`);
            const quantity = parseInt(quantityElement.textContent);
            const price = parseInt(quantityElement.dataset.ticketPrice);
            const total = price * quantity;
            
            selectedTickets.push({
                id: ticketId,
                name: option.querySelector('h6').textContent,
                quantity: quantity,
                price: price,
                total: total
            });
            
            totalAmount += total;
            totalQuantity += quantity;
        }
    });
    
    if (selectedTickets.length === 0) {
        document.getElementById('purchase-summary').classList.add('hidden');
        return;
    }
    
    // Update summary display
    const summaryHtml = `
        <div class="space-y-2 text-sm">
            ${selectedTickets.map(ticket => `
                <div class="flex justify-between items-center p-2 bg-gray-100 dark:bg-gray-600 rounded">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">${ticket.name}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">₱${ticket.price.toLocaleString('en-US')} × ${ticket.quantity}</div>
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">₱${ticket.total.toLocaleString('en-US')}</div>
                </div>
            `).join('')}
            <div class="border-t border-gray-300 dark:border-gray-600 pt-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-300">Total Quantity:</span>
                    <span class="font-medium text-gray-900 dark:text-white">${totalQuantity}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-lg font-medium text-gray-900 dark:text-white">Total Amount:</span>
                    <span class="text-lg font-medium text-qricket-600 dark:text-qricket-400">₱${totalAmount.toLocaleString('en-US')}</span>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('purchase-summary').innerHTML = `
        <h5 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Purchase Summary</h5>
        ${summaryHtml}
    `;
    
    document.getElementById('purchase-summary').classList.remove('hidden');
}

// Handle form submission
document.getElementById('ticket-purchase-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get all selected tickets
    const selectedTickets = [];
    document.querySelectorAll('.ticket-option').forEach(option => {
        const ticketId = option.dataset.ticketId;
        const checkbox = document.getElementById(`select-ticket-${ticketId}`);
        
        if (checkbox.checked) {
            const quantityElement = document.getElementById(`quantity-${ticketId}`);
            const quantity = parseInt(quantityElement.textContent);
            
            selectedTickets.push({
                ticket_type_id: ticketId,
                quantity: quantity
            });
        }
    });
    
    if (selectedTickets.length === 0) {
        Swal.fire('Error', 'Please select at least one ticket type', 'error');
        return;
    }
    
    const formData = {
        tickets: selectedTickets,
        customer_name: document.getElementById('customer_name').value,
        customer_email: document.getElementById('customer_email').value,
        customer_phone: document.getElementById('customer_phone').value,
    };
    
    // Show loading state
    document.getElementById('purchase-btn').disabled = true;
    document.getElementById('purchase-btn-text').classList.add('hidden');
    document.getElementById('purchase-btn-loading').classList.remove('hidden');
    
    // Submit purchase
    fetch(`/events/{{ $event->id }}/buy-tickets`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.data.bypass_mode) {
                // Bypass mode: Show success message and redirect
                Swal.fire({
                    title: 'Purchase Successful!',
                    text: data.message,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = data.data.redirect_url;
                });
            } else {
                // Normal mode: Redirect to Xendit payment page
                window.location.href = data.data.invoice_url;
            }
        } else {
            Swal.fire('Error', data.message || 'Failed to process purchase', 'error');
        }
    })
    .catch(error => {
        console.error('Purchase error:', error);
        Swal.fire('Error', 'An error occurred while processing your purchase', 'error');
    })
    .finally(() => {
        // Reset button state
        document.getElementById('purchase-btn').disabled = false;
        document.getElementById('purchase-btn-text').classList.remove('hidden');
        document.getElementById('purchase-btn-loading').classList.add('hidden');
    });
});

function openTicketModal() {
    document.getElementById('ticket-purchase-modal').classList.remove('hidden');
    loadTicketTypes();
}

function closeTicketModal() {
    document.getElementById('ticket-purchase-modal').classList.add('hidden');
}
</script>
