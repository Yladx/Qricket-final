<x-app-layout>
    <x-slot name="title">Buy Tickets - {{ $event->title }}</x-slot>
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center h-16">
                    <a href="{{ route('events.show', $event) }}" class="flex items-center text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $event->title }} - Buy Ticket
                    </h1>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Ticket Types (Left Side) -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-3">Available Tickets</h2>
                        <div id="purchase-limits-info" class="mb-4"></div>
                        <div id="ticket-types-container"></div>
                    </div>
                </div>

                <!-- Customer Form (Right Side) -->
                <div class="lg:col-span-1">
                    <!-- Customer Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Customer Information</h2>
                        
                        <form id="ticket-purchase-form">
                            @csrf
                            <input type="hidden" id="event-id" value="{{ $event->id }}">
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name *</label>
                                    <input type="text" id="customer_name" name="customer_name" required
                                           class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-white"
                                           value="{{ Auth::user()->full_name }}">
                                </div>
                                <div>
                                    <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                                    <input type="email" id="customer_email" name="customer_email" required
                                           class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-white"
                                           value="{{ Auth::user()->email }}">
                                </div>
                                <div>
                                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number *</label>
                                    <input type="tel" id="customer_phone" name="customer_phone" required
                                           class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-white"
                                           placeholder="+63 912 345 6789">
                                </div>
                                <div>
                                    <label for="customer_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address *</label>
                                    <textarea id="customer_address" name="customer_address" required rows="3"
                                              class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-white resize-none"
                                              placeholder="Enter your complete address"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Purchase Summary -->
                    <div id="purchase-summary" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Order Summary</h2>
                        <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                            </svg>
                            <p>Select tickets to see purchase summary</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('events.show', $event) }}" 
                           class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-lg transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" form="ticket-purchase-form" id="purchase-btn"
                                class="px-8 py-3 text-sm font-medium text-white bg-qricket-500 hover:bg-qricket-600 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="purchase-btn-text">Proceed to Payment</span>
                            <span id="purchase-btn-loading" class="hidden">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // Load ticket types when page loads
    function loadTicketTypes() {
        const eventId = document.getElementById('event-id').value;
        
        if (!eventId) {
            console.error('No event ID found');
            Swal.fire('Error', 'Event ID not found', 'error');
            return;
        }
        
        console.log('Loading ticket types for event:', eventId);
        
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
        const limitsInfo = document.getElementById('purchase-limits-info');
        const maxTicketsPerOrder = settings.max_tickets_per_order || 10;
        const limitPerUser = settings.limit_per_user || false;
        const maxTicketsPerUser = settings.max_tickets_per_user || 1;
        
        // Store settings globally for validation
        window.ticketSettings = settings;
        
        if (ticketTypes.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">No tickets available</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm">Tickets for this event are not currently on sale or have sold out.</p>
                </div>
            `;
            document.getElementById('purchase-btn').disabled = true;
            return;
        }
        
        // Show purchase limits info
        let limitsInfoHtml = '';
        if (limitPerUser) {
            limitsInfoHtml = `
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            limitsInfoHtml = `
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-blue-700 dark:text-blue-300">
                            <strong>Order Limit:</strong> Maximum ${maxTicketsPerOrder} ticket(s) per order.
                        </div>
                    </div>
                </div>
            `;
        }
        
        limitsInfo.innerHTML = limitsInfoHtml;
        
        container.innerHTML = ticketTypes.map(ticket => `
            <div class="border-2 border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 ticket-option cursor-pointer" data-ticket-id="${ticket.id}" onclick="toggleTicketSelection('${ticket.id}')">
                <input type="checkbox" id="select-ticket-${ticket.id}" class="hidden" onchange="toggleTicketSelection('${ticket.id}')">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${ticket.name}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">${ticket.description || 'No description available'}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">₱${parseInt(ticket.price).toLocaleString('en-US')}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Available: ${ticket.quantity ? (ticket.quantity - (ticket.sold_count || 0)) : 'Unlimited'}</div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <button type="button" onclick="event.stopPropagation(); changeQuantity('${ticket.id}', -1)" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 flex items-center justify-center text-gray-600 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" id="decrease-${ticket.id}">-</button>
                        <span id="quantity-${ticket.id}" class="w-12 text-center font-semibold text-lg text-gray-900 dark:text-white" data-max-quantity="${maxTicketsPerOrder}" data-max-per-user="${limitPerUser ? maxTicketsPerUser : ''}" data-ticket-price="${ticket.price}">1</span>
                        <button type="button" onclick="event.stopPropagation(); changeQuantity('${ticket.id}', 1)" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 flex items-center justify-center text-gray-600 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" id="increase-${ticket.id}">+</button>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="selection-hint">Click to select</span>
                    </div>
                </div>
            </div>
        `).join('');
        
        // Initialize ticket selection state after DOM is ready
        setTimeout(() => {
            initializeTicketSelection();
        }, 100);
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
        
        if (!checkbox) {
            console.error('Checkbox not found for ticket:', ticketId);
            return;
        }
        
        // Toggle checkbox state
        checkbox.checked = !checkbox.checked;
        
        // Force visual state to match checkbox state
        setTicketVisualState(ticketId, checkbox.checked);
        
        // Update purchase summary immediately
        updatePurchaseSummary();
    }
    
    function setTicketVisualState(ticketId, isSelected) {
        const ticketDiv = document.querySelector(`[data-ticket-id="${ticketId}"]`);
        const quantityElement = document.getElementById(`quantity-${ticketId}`);
        const decreaseBtn = document.getElementById(`decrease-${ticketId}`);
        const increaseBtn = document.getElementById(`increase-${ticketId}`);
        const selectionHint = ticketDiv ? ticketDiv.querySelector('span.selection-hint') : null;
        const checkbox = document.getElementById(`select-ticket-${ticketId}`);
        
        // Check if critical elements exist (selectionHint is optional for now)
        if (!ticketDiv || !quantityElement || !decreaseBtn || !increaseBtn || !checkbox) {
            console.error('Missing critical elements for ticket:', ticketId, {
                ticketDiv: !!ticketDiv,
                quantityElement: !!quantityElement,
                decreaseBtn: !!decreaseBtn,
                increaseBtn: !!increaseBtn,
                selectionHint: !!selectionHint,
                checkbox: !!checkbox
            });
            
            // Debug: Log the actual HTML structure
            if (ticketDiv) {
                console.log('Ticket div HTML:', ticketDiv.innerHTML);
                console.log('Available spans:', ticketDiv.querySelectorAll('span'));
            }
            return;
        }
        
        // Ensure checkbox state matches
        checkbox.checked = isSelected;
        
        if (isSelected) {
            // Selected state - green border
            ticketDiv.classList.remove('border-gray-200', 'dark:border-gray-600');
            ticketDiv.classList.add('border-green-500', 'bg-green-50', 'dark:bg-green-900/20');
            
            // Enable quantity controls
            decreaseBtn.disabled = false;
            increaseBtn.disabled = false;
            quantityElement.style.opacity = '1';
            
            // Update hint text
            if (selectionHint) {
                selectionHint.textContent = 'Selected';
                selectionHint.className = 'text-green-600 dark:text-green-400 font-medium';
            }
        } else {
            // Unselected state - gray border
            ticketDiv.classList.remove('border-green-500', 'bg-green-50', 'dark:bg-green-900/20');
            ticketDiv.classList.add('border-gray-200', 'dark:border-gray-600');
            
            // Disable quantity controls and reset to 1
            decreaseBtn.disabled = true;
            increaseBtn.disabled = true;
            quantityElement.style.opacity = '0.5';
            quantityElement.textContent = '1';
            
            // Update hint text
            if (selectionHint) {
                selectionHint.textContent = 'Click to select';
                selectionHint.className = 'text-gray-500 dark:text-gray-400';
            }
        }
    }

    function initializeTicketSelection() {
        // Initially all tickets are unselected and quantity controls are disabled
        document.querySelectorAll('.ticket-option').forEach(option => {
            const ticketId = option.dataset.ticketId;
            if (ticketId) {
                setTicketVisualState(ticketId, false);
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
            
            
            if (checkbox && checkbox.checked) {
                const quantityElement = document.getElementById(`quantity-${ticketId}`);
                const quantity = parseInt(quantityElement.textContent);
                const price = parseInt(quantityElement.dataset.ticketPrice);
                const total = price * quantity;
                
                selectedTickets.push({
                    id: ticketId,
                    name: option.querySelector('h3').textContent,
                    quantity: quantity,
                    price: price,
                    total: total
                });
                
                totalAmount += total;
                totalQuantity += quantity;
            }
        });
        
        if (selectedTickets.length === 0) {
            document.getElementById('purchase-summary').innerHTML = `
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Order Summary</h2>
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    <p>Select tickets to see purchase summary</p>
                </div>
            `;
            document.getElementById('purchase-summary').classList.remove('hidden');
            return;
        }
        
        // Update summary display
        const summaryHtml = `
            <div class="space-y-4">
                ${selectedTickets.map(ticket => `
                    <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">${ticket.name}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">₱${ticket.price.toLocaleString('en-US')} × ${ticket.quantity}</div>
                        </div>
                        <div class="font-semibold text-gray-900 dark:text-white">₱${ticket.total.toLocaleString('en-US')}</div>
                    </div>
                `).join('')}
                <div class="border-t border-gray-300 dark:border-gray-600 pt-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600 dark:text-gray-300">Total Quantity:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">${totalQuantity}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xl font-bold text-gray-900 dark:text-white">Total Amount:</span>
                        <span class="text-xl font-bold text-qricket-600 dark:text-qricket-400">₱${totalAmount.toLocaleString('en-US')}</span>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('purchase-summary').innerHTML = `
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Order Summary</h2>
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
            customer_address: document.getElementById('customer_address').value,
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
                    // Bypass mode - redirect to success page
                    window.location.href = data.data.redirect_url;
                } else {
                    // Normal mode - redirect to payment
                    window.location.href = data.data.invoice_url;
                }
            } else {
                Swal.fire('Error', data.message || 'Purchase failed', 'error');
            }
        })
        .catch(error => {
            console.error('Purchase error:', error);
            Swal.fire('Error', 'An error occurred while processing your purchase', 'error');
        })
        .finally(() => {
            // Reset loading state
            document.getElementById('purchase-btn').disabled = false;
            document.getElementById('purchase-btn-text').classList.remove('hidden');
            document.getElementById('purchase-btn-loading').classList.add('hidden');
        });
    });

    // Load ticket types when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Use the ticket types passed from the controller
        const ticketTypes = @json($ticketTypes);
        renderTicketTypes(ticketTypes, {});
    });
    </script>
</x-app-layout>


