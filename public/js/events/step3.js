/* Step 3 - Tickets: add/remove types and autosave to R2 */
(function() {
  // console.log('[step3.js] Script starting to load...');
  const form = document.querySelector('form[data-autosave-url], form[action*="ticket"]') || document.querySelector('form');
  const ticketContainer = document.getElementById('ticketTypes');
  const typesInput = document.getElementById('ticket_types_json');
  const settingsInput = document.getElementById('ticket_settings_json');
  let autosaveTimer = null;
  // console.log('[step3.js] Initial elements found:', { form: !!form, ticketContainer: !!ticketContainer, typesInput: !!typesInput, settingsInput: !!settingsInput });

  function getEventId() {
    // Try form action/query, autosave url, or location
    const autosaveUrl = form?.getAttribute('data-autosave-url') || '';
    const fromUrl = new URL(window.location.href);
    const idFromQuery = fromUrl.searchParams.get('id');
    const idFromPath = (window.location.pathname.match(/events\/(\d+)/) || [])[1];
    const idFromAutosave = (autosaveUrl.match(/events\/(\d+)/) || [])[1];
    return idFromQuery || idFromAutosave || idFromPath || '';
  }

  function readTypes() {
    try {
      const raw = typesInput?.value || '[]';
      let data = typeof raw === 'string' ? JSON.parse(raw) : raw;
      if (Array.isArray(data)) return data;
      // normalize legacy shapes
      if (data && Array.isArray(data.tickets)) return data.tickets;
      if (data && Array.isArray(data.types)) return data.types;
      if (data && typeof data === 'object') return Object.values(data);
      return [];
    } catch { return []; }
  }

  function writeTypes(list) {
    typesInput.value = JSON.stringify(list);
  }

  // Subscription ticket limit – fetched from meta tags rendered by Blade
  // If event is free and free-event-ticket-limit is set, cap by that
  function getTicketsPerEventLimit() {
    const planMeta = document.querySelector('meta[name="tickets-per-event"]');
    const freeMeta = document.querySelector('meta[name="free-event-ticket-limit"]');
    const isFreeMeta = document.querySelector('meta[name="event-is-free"]');
    const planVal = planMeta ? planMeta.getAttribute('content') : null;
    const freeVal = freeMeta ? freeMeta.getAttribute('content') : null;
    const isFree = (isFreeMeta ? isFreeMeta.getAttribute('content') : '') === '1';
    const planCap = (planVal == null || planVal === '' || planVal === 'null') ? null : (parseInt(planVal, 10) || null);
    const freeCap = (freeVal == null || freeVal === '' || freeVal === 'null') ? null : (parseInt(freeVal, 10) || null);
    // If event is free, prioritize free cap exclusively
    if (isFree && freeCap != null) {
      return freeCap;
    }
    return planCap;
  }

  function isFreeEvent() {
    const isFreeMeta = document.querySelector('meta[name="event-is-free"]');
    return (isFreeMeta ? isFreeMeta.getAttribute('content') : '') === '1';
  }

  function getUsedQuantity(list) {
    return (list || []).reduce((sum, t) => sum + (parseInt(t.quantity, 10) || 0), 0);
  }

  function remainingQuota(list) {
    const cap = getTicketsPerEventLimit();
    if (cap == null) return null;
    const used = getUsedQuantity(list);
    return Math.max(0, cap - used);
  }

  function updateQuantityMaxes() {
    const cap = getTicketsPerEventLimit();
    if (cap == null) return;
    const list = readTypes();
    const inputs = document.querySelectorAll('input[data-k="quantity"]');
    const free = isFreeEvent();
    // For each input, allow current value + remaining after subtracting others
    inputs.forEach((inp, idx) => {
      const others = list.reduce((sum, t, j) => sum + (j === idx ? 0 : (parseInt(t.quantity, 10) || 0)), 0);
      const allowedForThis = Math.max(0, cap - others);
      const cur = parseInt(inp.value, 10) || 0;
      inp.min = '0';
      // For free events, max is the cap itself (total limit); for others, allow current + remaining
      if (free) {
        inp.max = String(cap);
      } else {
        inp.max = String(cur + Math.max(0, allowedForThis - cur));
      }
    });
  }

  function readSettings() {
    try {
      const raw = settingsInput?.value || '{}';
      let data = typeof raw === 'string' ? JSON.parse(raw) : raw;
      if (data && typeof data === 'object') return data;
      return {};
    } catch { return {}; }
  }

  function writeSettings(obj) {
    settingsInput.value = JSON.stringify(obj);
  }

  // Ticket color customization functions
  function applyTicketColors() {
    const primaryColor = document.getElementById('ticket-primary-color').value;
    const secondaryColor = document.getElementById('ticket-secondary-color').value;
    const accentColor = document.getElementById('ticket-accent-color').value;
    
    // Apply colors to the ticket preview
    const previewContainer = document.getElementById('ticket-preview-container');
    if (previewContainer) {
      // Mobile ticket header gradient
      const mobileHeader = previewContainer.querySelector('.ticket-mobile-header');
      if (mobileHeader) {
        mobileHeader.style.background = `linear-gradient(to right, ${primaryColor}, ${secondaryColor})`;
      }
      
      // Mobile ticket background overlay
      const mobileOverlay = previewContainer.querySelector('.ticket-mobile-overlay');
      if (mobileOverlay) {
        mobileOverlay.style.background = `linear-gradient(to right, ${primaryColor}cc, ${secondaryColor}cc)`;
      }
      
      // Desktop ticket header gradient
      const desktopHeader = previewContainer.querySelector('.ticket-desktop-header');
      if (desktopHeader) {
        desktopHeader.style.background = `linear-gradient(to right, ${primaryColor}60, ${secondaryColor}60)`;
      }
      
      // Desktop ticket background overlay
      const desktopOverlay = previewContainer.querySelector('.ticket-desktop-overlay');
      if (desktopOverlay) {
        desktopOverlay.style.background = `linear-gradient(to right, ${primaryColor}60, ${secondaryColor}50)`;
      }
      
      // Status badge colors
      const statusBadges = previewContainer.querySelectorAll('.ticket-status-badge');
      statusBadges.forEach(badge => {
        badge.style.backgroundColor = accentColor;
      });
    }
    
    // console.log('Applied ticket colors immediately:', { primaryColor, secondaryColor, accentColor });
  }

  function resetTicketColors() {
    const defaultColors = {
      primary: '#10b981',
      secondary: '#f59e0b',
      accent: '#059669'
    };
    
    document.getElementById('ticket-primary-color').value = defaultColors.primary;
    document.getElementById('ticket-primary-color-hex').value = defaultColors.primary;
    document.getElementById('ticket-secondary-color').value = defaultColors.secondary;
    document.getElementById('ticket-secondary-color-hex').value = defaultColors.secondary;
    document.getElementById('ticket-accent-color').value = defaultColors.accent;
    document.getElementById('ticket-accent-color-hex').value = defaultColors.accent;
    
    applyTicketColors();
    // console.log('Reset ticket colors to default');
  }

  // Track if listeners have been added to prevent duplicates
  let colorListenersInitialized = false;
  
  function syncColorInputs() {
    // Sync color picker with text input and auto-save
    const colorInputs = ['ticket-primary-color', 'ticket-secondary-color', 'ticket-accent-color'];
    
    colorInputs.forEach(colorId => {
      const colorPicker = document.getElementById(colorId);
      const textInput = document.getElementById(colorId + '-hex');
      
      if (!colorPicker || !textInput) {
        // console.warn(`Color inputs not found for ${colorId}`);
        return;
      }
      
      // Remove existing listeners to prevent duplicates
      const newColorPicker = colorPicker.cloneNode(true);
      colorPicker.parentNode.replaceChild(newColorPicker, colorPicker);
      
      const newTextInput = textInput.cloneNode(true);
      textInput.parentNode.replaceChild(newTextInput, textInput);
      
      // Color picker input handler
      const handleColorPickerChange = function(e) {
        const value = e.target.value;
        const hexInput = document.getElementById(colorId + '-hex');
        if (hexInput) {
          hexInput.value = value;
        }
        applyTicketColors();
        updateTicketColorsInSettings(); // Auto-save colors
        // console.log('Color picker changed, autosaving:', value);
      };
      
      newColorPicker.addEventListener('input', handleColorPickerChange);
      newColorPicker.addEventListener('change', handleColorPickerChange);
      
      // Text input handler
      const handleTextInputChange = function(e) {
        const value = e.target.value.trim();
        if (/^#[0-9A-F]{6}$/i.test(value)) {
          const picker = document.getElementById(colorId);
          if (picker) {
            picker.value = value;
          }
          applyTicketColors();
          updateTicketColorsInSettings(); // Auto-save colors
          // console.log('Color text input changed, autosaving:', value);
        } else if (value && value.length === 6 && /^[0-9A-F]{6}$/i.test(value)) {
          // Handle hex without #
          const fullHex = '#' + value;
          const picker = document.getElementById(colorId);
          if (picker) {
            picker.value = fullHex;
          }
          e.target.value = fullHex;
          applyTicketColors();
          updateTicketColorsInSettings(); // Auto-save colors
          // console.log('Color text input changed (without #), autosaving:', fullHex);
        }
      };
      
      newTextInput.addEventListener('input', handleTextInputChange);
      newTextInput.addEventListener('blur', handleTextInputChange);
      newTextInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          handleTextInputChange(e);
        }
      });
    });
    
    // Mark as initialized
    colorListenersInitialized = true;
    // console.log('Color input listeners initialized');
  }

  function updateTicketColorsInSettings() {
    const primaryColorEl = document.getElementById('ticket-primary-color');
    const secondaryColorEl = document.getElementById('ticket-secondary-color');
    const accentColorEl = document.getElementById('ticket-accent-color');
    
    if (!primaryColorEl || !secondaryColorEl || !accentColorEl) {
      // console.warn('Color input elements not found, cannot update settings');
      return;
    }
    
    const primaryColor = primaryColorEl.value || '#10b981';
    const secondaryColor = secondaryColorEl.value || '#f59e0b';
    const accentColor = accentColorEl.value || '#059669';
    
    const currentSettings = readSettings();
    
    // Ensure ticket_colors object exists
    if (!currentSettings.ticket_colors) {
      currentSettings.ticket_colors = {};
    }
    
    // Update colors
    currentSettings.ticket_colors.primary = primaryColor;
    currentSettings.ticket_colors.secondary = secondaryColor;
    currentSettings.ticket_colors.accent = accentColor;
    
    writeSettings(currentSettings);
    
    // console.log('Updated ticket colors in settings:', {
    //   primary: primaryColor,
    //   secondary: secondaryColor,
    //   accent: accentColor,
    //   fullSettings: currentSettings
    // });
    
    // Trigger immediate autosave for colors (shorter delay for better UX)
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(autosave, 300); // Reduced from 600ms to 300ms for faster save
    
    // Show save indicator immediately
    showColorSaveIndicator();
    
    // console.log('Ticket colors queued for autosave (300ms delay)');
  }

  function showColorSaveIndicator() {
    const indicator = document.getElementById('color-save-indicator');
    if (indicator) {
      indicator.classList.remove('hidden');
      indicator.classList.remove('text-red-600', 'dark:text-red-400', 'text-yellow-600', 'dark:text-yellow-400');
      indicator.classList.add('text-green-600', 'dark:text-green-400');
      indicator.textContent = 'Saved';
      
      // Hide after 2 seconds
      setTimeout(() => {
        indicator.classList.add('hidden');
      }, 2000);
    }
  }
  
  function showColorSaveError() {
    const indicator = document.getElementById('color-save-indicator');
    if (indicator) {
      indicator.classList.remove('hidden');
      indicator.classList.remove('text-green-600', 'dark:text-green-400');
      indicator.classList.add('text-red-600', 'dark:text-red-400');
      indicator.textContent = 'Error saving';
      
      setTimeout(() => {
        indicator.classList.add('hidden');
      }, 3000);
    }
  }

  function render() {
    let list = readTypes();
    // On initial render, enforce cap across existing quantities so UI shows the exact allowed amount
    const cap = getTicketsPerEventLimit();
    if (cap != null) {
      let remaining = cap;
      let changed = false;
      const normalized = list.map((t) => {
        const originalQty = parseInt(t.quantity, 10) || 0;
        const capped = Math.max(0, Math.min(originalQty, remaining));
        remaining -= capped;
        if (capped !== originalQty) changed = true;
        return Object.assign({}, t, { quantity: String(capped) });
      });
      if (changed) {
        list = normalized;
        writeTypes(list);
      }
    }
    ticketContainer.innerHTML = '';
    if (!list.length) {
      const empty = document.getElementById('ticket-empty');
      if (empty) empty.style.display = 'block';
      return;
    } else {
      const empty = document.getElementById('ticket-empty');
      if (empty) empty.style.display = 'none';
    }
    const free = isFreeEvent();
    // Normalize prices for free events - ensure all prices are 0
    if (free) {
      list.forEach(t => {
        // Always set price to 0 for free events, regardless of current value
        t.price = '0';
      });
      writeTypes(list); // Save normalized data
    }
    list.forEach((t, idx) => {
      const row = document.createElement('div');
      row.className = 'space-y-2';
      const rowIdAttr = t.id != null ? `data-id="${String(t.id)}"` : '';
      const canDelete = t.can_delete !== false && (t.purchased_count || 0) === 0;
      const deleteButtonClass = canDelete 
        ? 'inline-flex items-center justify-center w-9 h-9 rounded-md border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:border-red-500 dark:hover:border-red-500 transition'
        : 'inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-300 dark:border-gray-600 text-gray-400 cursor-not-allowed opacity-50';
      const deleteButtonTitle = canDelete 
        ? 'Remove' 
        : `Cannot remove - ${t.purchased_count || 0} tickets sold`;
      const purchaseIndicator = (t.purchased_count || 0) > 0 
        ? `<div class="text-xs text-orange-600 dark:text-orange-400 font-medium">${t.purchased_count} sold</div>`
        : '';
      
      row.innerHTML = `
        <div class="flex justify-end items-center space-x-2">
          ${purchaseIndicator}
          <button type="button" data-remove="${idx}" class="${deleteButtonClass}" title="${deleteButtonTitle}" aria-label="${deleteButtonTitle}" ${canDelete ? '' : 'disabled'}>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6"/></svg>
          </button>
        </div>
        <div ${rowIdAttr} class="border border-gray-200 dark:border-gray-700 rounded-md p-3 space-y-3 bg-white dark:bg-gray-800">
          <div class="grid grid-cols-12 gap-3 items-end">
            <div class="col-span-12 md:col-span-6">
              <label class="block text-xs text-gray-500">Name</label>
              <input data-k="name" data-i="${idx}" class="w-full rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 h-10" value="${t.name || ''}">
            </div>
            <div class="col-span-6 md:col-span-3">
              <label class="block text-xs text-gray-500">Price</label>
              <input data-k="price" data-i="${idx}" type="number" min="0" step="0.01" class="w-full rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 h-10 ${free ? 'opacity-60 cursor-not-allowed bg-gray-100 dark:bg-gray-700' : ''}" value="${free ? '0' : (t.price || '500')}" ${free ? 'readonly disabled' : ''} ${free ? 'tabindex="-1"' : ''}>
            </div>
            <div class="col-span-6 md:col-span-3">
              <label class="block text-xs text-gray-500">Quantity</label>
              <input data-k="quantity" data-i="${idx}" type="number" min="0" class="w-full rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 h-10" value="${t.quantity ?? ''}" ${free ? `max="${getTicketsPerEventLimit() || ''}"` : ''}>
            </div>
          </div>
          <div>
            <label class="block text-xs text-gray-500">Description</label>
            <textarea data-k="description" data-i="${idx}" rows="2" maxlength="70" class="w-full rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" oninput="updateDescriptionCounter(this)">${t.description || ''}</textarea>
            <div class="flex justify-end mt-0.5">
              <span class="text-xs text-gray-400 description-counter" data-counter-for="${idx}">${(t.description || '').length}/70</span>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-gray-500">Sale start</label>
              <input data-k="sale_start" data-i="${idx}" type="datetime-local" class="w-full rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 h-10" value="${formatDateTimeLocal(t.sale_start)}">
            </div>
            <div>
              <label class="block text-xs text-gray-500">Sale end</label>
              <input data-k="sale_end" data-i="${idx}" type="datetime-local" class="w-full rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 h-10" value="${formatDateTimeLocal(t.sale_end)}">
            </div>
          </div>
          <div class="flex items-center space-x-2 pt-1">
            <input data-k="is_active" data-i="${idx}" type="checkbox" class="rounded border-gray-300 text-qricket-600" ${String(t.is_active)==='1' || t.is_active===true ? 'checked' : ''}>
            <label class="text-xs text-gray-500">Active</label>
          </div>
        </div>`;
      ticketContainer.appendChild(row);
    });
    
    // Set up date validation after rendering
    validateAndSetMinMaxDates();
    // Update max attributes for quantities under current cap
    setTimeout(updateQuantityMaxes, 0);
    
    // Block price inputs for free events after rendering
    if (free) {
      setTimeout(() => {
        document.querySelectorAll('input[data-k="price"]').forEach(input => {
          input.setAttribute('readonly', 'readonly');
          input.setAttribute('disabled', 'disabled');
          input.value = '0';
          input.style.pointerEvents = 'none';
          input.style.backgroundColor = '#f3f4f6';
          // Prevent any input events
          input.addEventListener('keydown', (e) => {
            e.preventDefault();
            e.stopPropagation();
            return false;
          }, true);
          input.addEventListener('input', (e) => {
            e.preventDefault();
            e.stopPropagation();
            input.value = '0';
            return false;
          }, true);
          input.addEventListener('paste', (e) => {
            e.preventDefault();
            e.stopPropagation();
            return false;
          }, true);
        });
      }, 0);
    }
  }

  function queueAutosave() {
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(autosave, 600);
  }

  // Immediate autosave for colors only (no delay)
  async function autosaveColorsOnly() {
    try {
      const url = form?.getAttribute('data-autosave-url') || `/organizer/events/ticket/autosave?id=${encodeURIComponent(getEventId())}`;
      
      // Get current ticket colors from form inputs
      const primaryColorEl = document.getElementById('ticket-primary-color');
      const secondaryColorEl = document.getElementById('ticket-secondary-color');
      const accentColorEl = document.getElementById('ticket-accent-color');
      
      if (!primaryColorEl || !secondaryColorEl || !accentColorEl) {
        // console.warn('Color inputs not found for autosave');
        return;
      }
      
      const ticketColors = {
        primary: primaryColorEl.value || '#10b981',
        secondary: secondaryColorEl.value || '#f59e0b',
        accent: accentColorEl.value || '#059669'
      };
      
      // Get existing settings to preserve other values
      const settings = readSettings();
      const types = readTypes();
      
      // Build payload with colors
      const payload = {
        ticket_types: types.map(t => ({
          id: t.id ?? undefined,
          name: t.name ?? '',
          description: t.description ?? '',
          price: t.price ?? '',
          quantity: t.quantity ?? '',
          sale_start: t.sale_start ?? null,
          sale_end: t.sale_end ?? null,
        })),
        ticket_settings: {
          ...settings,
          ticket_colors: ticketColors
        },
        ticket_colors: ticketColors
      };
      
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      });
      
      if (response.ok) {
        // console.log('Color autosave successful');
      } else {
        // console.error('Color autosave failed:', response.status);
      }
    } catch (e) {
      // console.error('Color autosave error:', e);
    }
  }

  async function autosave() {
    try {
      const url = form?.getAttribute('data-autosave-url') || `/organizer/events/ticket/autosave?id=${encodeURIComponent(getEventId())}`;
      const settings = readSettings();
      // console.log('Autosaving with settings:', settings);
      const sanitizedTypes = readTypes().map(t => ({
        id: t.id ?? undefined,
        name: t.name ?? '',
        description: t.description ?? '',
        price: t.price ?? '',
        quantity: t.quantity ?? '',
        sale_start: t.sale_start ?? null,
        sale_end: t.sale_end ?? null,
      }));
      
      // Always get ticket colors from current form inputs (most up-to-date)
      const primaryColorEl = document.getElementById('ticket-primary-color');
      const secondaryColorEl = document.getElementById('ticket-secondary-color');
      const accentColorEl = document.getElementById('ticket-accent-color');
      
      const ticketColors = {
        primary: primaryColorEl?.value || settings.ticket_colors?.primary || '#10b981',
        secondary: secondaryColorEl?.value || settings.ticket_colors?.secondary || '#f59e0b',
        accent: accentColorEl?.value || settings.ticket_colors?.accent || '#059669'
      };
      
      // Build ticket_settings object with ticket_colors
      const ticketSettings = {
        max_tickets_per_order: settings.max_tickets_per_order || 10,
        refund_policy: settings.refund_policy || '',
        allow_refunds: settings.allow_refunds ?? true,
        require_approval: settings.require_approval ?? false,
        allow_share_ticket: settings.allow_share_ticket ?? true,
        limit_per_user: settings.limit_per_user ?? false,
        max_tickets_per_user: settings.max_tickets_per_user || 1,
        ticket_colors: ticketColors  // Ensure ticket_colors is always included
      };
      
      const payload = {
        ticket_types: sanitizedTypes,
        ticket_settings: ticketSettings,
        max_tickets_per_order: settings.max_tickets_per_order || 10,
        refund_policy: settings.refund_policy || '',
        allow_refunds: settings.allow_refunds ?? true,
        require_approval: settings.require_approval ?? false,
        allow_share_ticket: settings.allow_share_ticket ?? true,
        limit_per_user: settings.limit_per_user ?? false,
        max_tickets_per_user: settings.max_tickets_per_user || 1,
        ticket_colors: ticketColors  // Also send directly for backwards compatibility
      };
      
      // console.log('Autosave payload - ticket_colors:', ticketColors);
      // console.log('Autosave payload - full settings:', payload.ticket_settings);
      
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      });
      
      if (response.ok) {
        const responseData = await response.json();
        // console.log('Autosave successful:', responseData);
        
        // Update local settings with saved ticket colors to keep in sync
        const updatedSettings = readSettings();
        updatedSettings.ticket_colors = ticketColors;
        writeSettings(updatedSettings);
        
        // Show success indicator for color save
        if (ticketColors && (ticketColors.primary || ticketColors.secondary || ticketColors.accent)) {
          showColorSaveIndicator();
        }
      } else {
        // console.error('[Step3] Autosave failed with status:', response.status);
        const errorText = await response.text();
        // console.error('[Step3] Autosave error response:', errorText);
        
        // Show error indicator for color save
        if (ticketColors && (ticketColors.primary || ticketColors.secondary || ticketColors.accent)) {
          showColorSaveError();
        }
      }
    } catch (e) {
      // console.error('[Step3] Autosave failed', e);
    }
  }

  function addTicketType() {
    // console.log('========================================');
    // console.log('[addTicketType] FUNCTION CALLED - Starting addTicketType');
    // console.log('[addTicketType] Timestamp:', new Date().toISOString());
    // console.log('[addTicketType] window.addTicketType === addTicketType:', window.addTicketType === addTicketType);
    // console.log('========================================');
    const list = readTypes();
    // console.log('[addTicketType] Current list:', list);
    const free = isFreeEvent();
    // console.log('[addTicketType] isFreeEvent:', free, 'meta tag:', document.querySelector('meta[name="event-is-free"]')?.getAttribute('content'));
    const cap = getTicketsPerEventLimit();
    if (cap != null && getUsedQuantity(list) >= cap) {
      if (typeof Swal !== 'undefined' && window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Ticket Limit Reached',
          text: 'You have reached your plan\'s ticket limit for this event.',
          confirmButtonColor: '#10b981'
        });
      } else {
        alert('You have reached your plan\'s ticket limit for this event.');
      }
      return;
    }
    
    // Set default sale dates based on event dates
    let defaultSaleStart = null;
    let defaultSaleEnd = null;
    
    if (window.eventDates?.startDate) {
      defaultSaleStart = formatDateTimeLocal(window.eventDates.startDate);
    }
    
    if (window.eventDates?.endDate) {
      defaultSaleEnd = formatDateTimeLocal(window.eventDates.endDate);
    }
    
    // Default quantity respects remaining quota when capped
    const remain = remainingQuota(list);
    const defaultQty = remain == null ? (cap ? String(Math.min(100, cap)) : '100') : String(Math.max(1, Math.min(100, remain)));

    // ALWAYS set price to 0 for free events - check multiple times to be sure
    const isFree = isFreeEvent();
    // console.log('[addTicketType] Checking isFree again:', isFree);
    // Force price to 0 if free, otherwise default to 500
    const ticketPrice = isFree ? '0' : '500';
    // console.log('[addTicketType] Setting price to:', ticketPrice, 'isFree:', isFree);

    const newTicket = {
      id: null,
      name: 'General Admission',
      description: 'Standard access',
      price: isFree ? '0' : '500', // Force 0 if free
      quantity: defaultQty,
      sale_start: defaultSaleStart,
      sale_end: defaultSaleEnd,
      is_active: '1'
    };
    // console.log('[addTicketType] New ticket created with price:', newTicket.price);
    
    // Double-check: Force price to 0 if free event (extra safety)
    if (isFree) {
      newTicket.price = '0';
      // console.log('[addTicketType] Forced price to 0 for free event');
    }
    
    // console.log('[addTicketType] Final ticket object before push:', newTicket);
    list.push(newTicket);
    // console.log('[addTicketType] List after push, last item price:', list[list.length - 1].price);
    
    // Triple-check: Normalize all prices in list before saving
    if (isFree) {
      // console.log('[addTicketType] Normalizing all prices to 0');
      list.forEach(t => {
        t.price = '0';
      });
      // console.log('[addTicketType] After normalization, last item price:', list[list.length - 1].price);
    }
    
    writeTypes(list);
    // console.log('[addTicketType] Calling render()');
    render();
    // console.log('[addTicketType] Render complete');
    // Immediately block price field after adding new ticket type
    setTimeout(() => {
      const free = isFreeEvent();
      if (free) {
        const priceInputs = document.querySelectorAll('input[data-k="price"]');
        priceInputs.forEach(input => {
          input.setAttribute('readonly', 'readonly');
          input.setAttribute('disabled', 'disabled');
          input.value = '0';
          input.style.pointerEvents = 'none';
          input.style.backgroundColor = '#f3f4f6';
          // Prevent any input events
          input.addEventListener('keydown', (e) => {
            e.preventDefault();
            e.stopPropagation();
            return false;
          }, true);
          input.addEventListener('input', (e) => {
            e.preventDefault();
            e.stopPropagation();
            input.value = '0';
            return false;
          }, true);
          input.addEventListener('paste', (e) => {
            e.preventDefault();
            e.stopPropagation();
            return false;
          }, true);
        });
      }
    }, 0);
    queueAutosave();
  }

  function removeTicketType(index) {
    const list = readTypes();
    const ticket = list[index];
    
    // Check if ticket has been purchased
    if (ticket && ticket.purchased_count > 0) {
      Swal.fire({
        title: 'Cannot Delete Ticket',
        text: `This ticket type has been purchased ${ticket.purchased_count} time(s) and cannot be deleted.`,
        icon: 'warning',
        confirmButtonText: 'OK',
        confirmButtonColor: '#10b981'
      });
      return;
    }
    
    // Check if ticket has can_delete flag set to false
    if (ticket && ticket.can_delete === false) {
      Swal.fire({
        title: 'Cannot Delete Ticket',
        text: 'This ticket type cannot be deleted because it has been purchased.',
        icon: 'warning',
        confirmButtonText: 'OK',
        confirmButtonColor: '#10b981'
      });
      return;
    }
    
    list.splice(index, 1);
    writeTypes(list);
    render();
    queueAutosave();
  }

  function onContainerInput(e) {
    const k = e.target.getAttribute('data-k');
    const i = e.target.getAttribute('data-i');
    if (k == null || i == null) return;
    const list = readTypes();
    if (e.target.type === 'checkbox') {
      list[Number(i)][k] = e.target.checked ? '1' : '0';
    } else {
      // Prevent editing price for free events
      if (k === 'price' && isFreeEvent()) {
        e.target.value = '0';
        list[Number(i)][k] = '0';
        return;
      }
      list[Number(i)][k] = e.target.value;
    }
    
    // Validate date fields
    if (k === 'sale_start' || k === 'sale_end') {
      const ticket = list[Number(i)];
      const validation = validateTicketSaleDates(ticket.sale_start, ticket.sale_end, i);
      
      if (validation.isValid) {
        clearDateValidationError(e.target);
      } else {
        showDateValidationError(e.target, validation.message);
        return; // Don't save invalid data
      }
    }
    
    // Enforce real-time quantity cap
    if (k === 'quantity') {
      const cap = getTicketsPerEventLimit();
      if (cap != null) {
        // Clamp this field so that total does not exceed cap
        const idx = Number(i);
        const others = list.reduce((sum, t, j) => sum + (j === idx ? 0 : (parseInt(t.quantity, 10) || 0)), 0);
        const allowedForThis = Math.max(0, cap - others);
        let val = parseInt(e.target.value, 10) || 0;
        if (val > allowedForThis) {
          val = allowedForThis;
          e.target.value = String(val);
          // Optional: brief UI feedback
          e.target.classList.add('ring-2','ring-red-400');
          setTimeout(()=>e.target.classList.remove('ring-2','ring-red-400'), 600);
        }
        list[idx].quantity = String(val);
        // Refresh max attributes after change
        setTimeout(updateQuantityMaxes, 0);
      }
    }

    // Update description counter if this is a description field
    if (k === 'description') {
      updateDescriptionCounter(e.target);
    }
    
    writeTypes(list);
    queueAutosave();
  }
  
  // Function to update description character counter
  function updateDescriptionCounter(textarea) {
    if (!textarea) return;
    const idx = textarea.getAttribute('data-i');
    if (idx == null) return;
    const counter = document.querySelector(`.description-counter[data-counter-for="${idx}"]`);
    if (counter) {
      const length = textarea.value.length;
      counter.textContent = `${length}/70`;
      if (length >= 70) {
        counter.classList.add('text-red-500');
        counter.classList.remove('text-gray-400');
      } else {
        counter.classList.remove('text-red-500');
        counter.classList.add('text-gray-400');
      }
    }
  }
  
  // Expose function globally for inline calls
  window.updateDescriptionCounter = updateDescriptionCounter;

  function onContainerClick(e) {
    const btn = e.target.closest && e.target.closest('button[data-remove]');
    if (!btn) return;
    const idx = btn.getAttribute('data-remove');
    if (idx != null) removeTicketType(Number(idx));
  }

  // expose for inline buttons - do this immediately, not in DOMContentLoaded
  window.addTicketType = addTicketType;
  // console.log('[step3.js] addTicketType function exposed to window:', typeof window.addTicketType);
  // console.log('[step3.js] window.addTicketType === addTicketType:', window.addTicketType === addTicketType);

  // Also add event listener as fallback for button clicks
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('button[onclick*="addTicketType"]');
    if (btn) {
      // console.log('========================================');
      // console.log('[step3.js] Button with addTicketType onclick clicked!');
      // console.log('[step3.js] Button element:', btn);
      // console.log('[step3.js] window.addTicketType type:', typeof window.addTicketType);
      e.preventDefault();
      e.stopPropagation();
      if (typeof window.addTicketType === 'function') {
        // console.log('[step3.js] Calling window.addTicketType from event listener');
        window.addTicketType();
      } else {
        // console.error('[step3.js] ERROR: window.addTicketType is not a function!', typeof window.addTicketType);
      }
      // console.log('========================================');
      return false;
    }
  }, true);

  document.addEventListener('DOMContentLoaded', () => {
    // console.log('[step3.js] DOMContentLoaded - addTicketType available:', typeof window.addTicketType);
    // console.log('[step3.js] Checking for addTicketType buttons:', document.querySelectorAll('button[onclick*="addTicketType"]').length);
    // if server provided initial data, prefer it
    const initialTypes = typesInput?.getAttribute('data-initial');
    if (initialTypes) {
      try {
        const parsed = JSON.parse(initialTypes);
        const arr = Array.isArray(parsed) ? parsed : (parsed?.tickets || parsed?.types || Object.values(parsed||{}));
        if (arr && arr.length) {
          typesInput.value = JSON.stringify(arr);
        } else {
          // no initial types
        }
      } catch { /* keep existing value */ }
    }
    const initialSettings = settingsInput?.getAttribute('data-initial');
    if (initialSettings) {
      try {
        const parsed = JSON.parse(initialSettings);
        if (parsed && typeof parsed === 'object') {
          settingsInput.value = JSON.stringify(parsed);
        } else {
          // no initial settings
        }
      } catch { /* keep */ }
    }

    render();
    ticketContainer?.addEventListener('input', onContainerInput);
    ticketContainer?.addEventListener('click', onContainerClick);
    
    // Block price field input for free events
    const blockPriceInputs = () => {
      const free = isFreeEvent();
      if (free) {
        document.querySelectorAll('input[data-k="price"]').forEach(input => {
          input.setAttribute('readonly', 'readonly');
          input.setAttribute('disabled', 'disabled');
          input.value = '0';
          input.style.pointerEvents = 'none';
          input.style.backgroundColor = '#f3f4f6';
          // Prevent any input events
          input.addEventListener('keydown', (e) => {
            e.preventDefault();
            e.stopPropagation();
            return false;
          });
          input.addEventListener('input', (e) => {
            e.preventDefault();
            e.stopPropagation();
            input.value = '0';
            return false;
          });
          input.addEventListener('paste', (e) => {
            e.preventDefault();
            e.stopPropagation();
            return false;
          });
        });
      }
    };
    
    // Initialize description counters for existing tickets
    setTimeout(() => {
      document.querySelectorAll('textarea[data-k="description"]').forEach(textarea => {
        updateDescriptionCounter(textarea);
      });
      // Block price inputs after render
      blockPriceInputs();
    }, 100);

    // hydrate settings UI
    const s = readSettings();
    const max = document.getElementById('ts-max');
    const refund = document.getElementById('ts-refund');
    const allow = document.getElementById('ts-allow-refunds');
    const approve = document.getElementById('ts-require-approval');
    const allowShare = document.getElementById('ts-allow-share');
    const limitPerUser = document.getElementById('ts-limit-per-user');
    const maxPerUser = document.getElementById('ts-max-per-user');
    
    if (max && s.max_tickets_per_order) max.value = s.max_tickets_per_order;
    if (refund && s.refund_policy) refund.value = s.refund_policy;
    if (allow) allow.checked = !!s.allow_refunds;
    if (approve) approve.checked = !!s.require_approval;
    if (allowShare) allowShare.checked = !!s.allow_share_ticket;
    if (limitPerUser) limitPerUser.checked = !!s.limit_per_user;
    if (maxPerUser && s.max_tickets_per_user) maxPerUser.value = s.max_tickets_per_user;

    // Show/hide max tickets per user container based on initial state
    if (limitPerUser && limitPerUser.checked) {
      const container = document.getElementById('max-tickets-per-user-container');
      if (container) container.classList.remove('hidden');
    }

    // Initialize ticket color customization
    const ticketColors = s.ticket_colors || {};
    const primaryColor = document.getElementById('ticket-primary-color');
    const secondaryColor = document.getElementById('ticket-secondary-color');
    const accentColor = document.getElementById('ticket-accent-color');
    const primaryColorHex = document.getElementById('ticket-primary-color-hex');
    const secondaryColorHex = document.getElementById('ticket-secondary-color-hex');
    const accentColorHex = document.getElementById('ticket-accent-color-hex');
    
    // console.log('Initializing ticket colors from settings:', ticketColors);
    
    // Load saved colors or use defaults
    const savedPrimary = ticketColors.primary || '#10b981';
    const savedSecondary = ticketColors.secondary || '#f59e0b';
    const savedAccent = ticketColors.accent || '#059669';
    
    if (primaryColor && primaryColorHex) {
      primaryColor.value = savedPrimary;
      primaryColorHex.value = savedPrimary;
      // console.log('Loaded primary color:', savedPrimary);
    }
    if (secondaryColor && secondaryColorHex) {
      secondaryColor.value = savedSecondary;
      secondaryColorHex.value = savedSecondary;
      // console.log('Loaded secondary color:', savedSecondary);
    }
    if (accentColor && accentColorHex) {
      accentColor.value = savedAccent;
      accentColorHex.value = savedAccent;
      // console.log('Loaded accent color:', savedAccent);
    }
    
    // Initialize color synchronization AFTER loading colors
    // Use setTimeout to ensure DOM is fully ready
    setTimeout(() => {
      syncColorInputs();
      
      // Apply initial colors to preview after loading from settings
      applyTicketColors();
      
      // Force an initial autosave to ensure colors are saved if they exist
      const primaryColorEl = document.getElementById('ticket-primary-color');
      const secondaryColorEl = document.getElementById('ticket-secondary-color');
      const accentColorEl = document.getElementById('ticket-accent-color');
      
      if (primaryColorEl && secondaryColorEl && accentColorEl) {
        // Only autosave if colors have been loaded/changed
        const hasColors = primaryColorEl.value && secondaryColorEl.value && accentColorEl.value;
        if (hasColors) {
          // console.log('Initial colors detected, ensuring autosave...');
          updateTicketColorsInSettings();
        }
      }
    }, 100);
    
    // Add event listener for reset color button (auto-save is already handled in syncColorInputs)
    const resetColorsBtn = document.getElementById('reset-ticket-colors');
    
    if (resetColorsBtn) {
      resetColorsBtn.addEventListener('click', () => {
        resetTicketColors();
        updateTicketColorsInSettings(); // Auto-save the reset colors
      });
    }
    
    // Ensure colors are saved to settings on initial load (in case they were missing from settings)
    // This ensures that if colors exist in the form but not in settings, they get saved
    setTimeout(() => {
      // Only save if colors aren't already in settings, or if they're different
      const currentSettings = readSettings();
      const needsSave = !currentSettings.ticket_colors || 
                       currentSettings.ticket_colors.primary !== savedPrimary ||
                       currentSettings.ticket_colors.secondary !== savedSecondary ||
                       currentSettings.ticket_colors.accent !== savedAccent;
      
      if (needsSave) {
        updateTicketColorsInSettings();
        // console.log('Initial ticket colors saved to settings (colors were missing or different)');
      } else {
        // console.log('Ticket colors already in settings, no need to save');
      }
    }, 200);

    function onSettingsChange(){
      const updated = {
        max_tickets_per_order: max?.value || '',
        refund_policy: refund?.value || '',
        allow_refunds: !!(allow?.checked),
        require_approval: !!(approve?.checked),
        allow_share_ticket: !!(allowShare?.checked),
        limit_per_user: !!(limitPerUser?.checked),
        max_tickets_per_user: maxPerUser?.value || 1,
        ticket_colors: {
          primary: primaryColor?.value || '#10b981',
          secondary: secondaryColor?.value || '#f59e0b',
          accent: accentColor?.value || '#059669'
        }
      };
      // console.log('Settings changed:', updated);
      writeSettings(updated);
      queueAutosave();
      
      // Also apply colors to preview immediately
      applyTicketColors();
    }
    max?.addEventListener('input', onSettingsChange);
    refund?.addEventListener('input', onSettingsChange);
    allow?.addEventListener('change', onSettingsChange);
    approve?.addEventListener('change', onSettingsChange);
    allowShare?.addEventListener('change', onSettingsChange);
    limitPerUser?.addEventListener('change', onSettingsChange);
    maxPerUser?.addEventListener('input', onSettingsChange);
    
    // Note: Color input event listeners are already set up by syncColorInputs()
    // Additional listeners are not needed as syncColorInputs() handles:
    // 1. Syncing color picker <-> text input
    // 2. Calling updateTicketColorsInSettings() which triggers autosave
    // 3. Applying colors to preview via applyTicketColors()
    
    // However, we ensure onSettingsChange also includes colors for consistency
    // (syncColorInputs already calls updateTicketColorsInSettings which handles autosave)

    // Prevent proceeding without at least one ticket
    // Get the form again inside DOMContentLoaded to ensure it's found
    const navForm = document.querySelector('form[data-autosave-url]') || 
                   document.querySelector('form[action*="ticket"]') || 
                   document.querySelector('form');
    
    if (navForm) {
      navForm.addEventListener('submit', (e) => {
        const list = readTypes();
        // console.log('Form submitted, ticket list:', list); // Debug log
        if (!list || list.length === 0) {
          e.preventDefault();
          e.stopPropagation();
          
          // Wait for SweetAlert to be available if it's not loaded yet
          const showAlert = () => {
            if (typeof Swal !== 'undefined' && window.Swal) {
              Swal.fire({
                title: 'No Tickets Added',
                text: 'Please add at least one ticket type before proceeding to the next step.',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981',
                showCancelButton: false,
                allowOutsideClick: true,
                allowEscapeKey: true
              });
            } else {
              alert('Please add at least one ticket type before proceeding to the next step.');
            }
          };
          
          // If SweetAlert is not loaded yet, wait a bit for it
          if (typeof Swal === 'undefined' || !window.Swal) {
            let attempts = 0;
            const checkSwal = setInterval(() => {
              attempts++;
              if (typeof Swal !== 'undefined' && window.Swal) {
                clearInterval(checkSwal);
                showAlert();
              } else if (attempts >= 10) {
                clearInterval(checkSwal);
                if (typeof Swal !== 'undefined' && window.Swal) {
                  Swal.fire({
                    icon: 'warning',
                    title: 'No Ticket Types',
                    text: 'Please add at least one ticket type before proceeding to the next step.',
                    confirmButtonColor: '#10b981'
                  });
                } else {
                  alert('Please add at least one ticket type before proceeding to the next step.');
                }
              }
            }, 100);
          } else {
            showAlert();
          }
          
          return false;
        }
      });
    } else {
      // console.warn('Ticket form not found, validation may not work');
    }
  });

  // Flush queued activity when user leaves the page
  function flushOnExit() {
    try {
      const url = `/organizer/events/ticket/autosave/flush?id=${encodeURIComponent(getEventId())}`;
      const tokenMeta = document.querySelector('meta[name="csrf-token"]');
      const csrf = tokenMeta ? tokenMeta.content : '';
      const payload = {};
      // Use keepalive fetch so the request completes during page unload
      fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(payload),
        keepalive: true,
        credentials: 'same-origin'
      }).catch(()=>{});
      // try { console.log('[Step3] Flush requested before exit'); } catch(_){ }
    } catch {}
  }
  window.addEventListener('pagehide', flushOnExit);
  window.addEventListener('beforeunload', flushOnExit);

  function formatDateTimeLocal(val) {
    if (!val) return '';
    try {
      const d = new Date(val);
      if (isNaN(d.getTime())) return '';
      const pad = (n)=> String(n).padStart(2,'0');
      return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    } catch { return ''; }
  }

  // More robust date parsing function
  function parseDate(dateString) {
    if (!dateString) return null;
    
    try {
      // Try direct parsing first
      let date = new Date(dateString);
      if (!isNaN(date.getTime())) return date;
      
      // Handle different date formats
      // If it looks like DD/MM/YYYY or MM/DD/YYYY, try to parse it
      if (typeof dateString === 'string') {
        // Remove time part if present
        const datePart = dateString.split(' ')[0];
        const parts = datePart.split('/');
        
        if (parts.length === 3) {
          // Try both DD/MM/YYYY and MM/DD/YYYY
          const day = parseInt(parts[0]);
          const month = parseInt(parts[1]);
          const year = parseInt(parts[2]);
          
          // If day > 12, it's likely DD/MM/YYYY
          if (day > 12) {
            date = new Date(year, month - 1, day);
          } else {
            // Try MM/DD/YYYY first
            date = new Date(year, month - 1, day);
            if (isNaN(date.getTime())) {
              // If that fails, try DD/MM/YYYY
              date = new Date(year, day - 1, month);
            }
          }
        }
      }
      
      return !isNaN(date.getTime()) ? date : null;
    } catch {
      return null;
    }
  }

  // Date validation functions
  function validateTicketSaleDates(saleStart, saleEnd, ticketIndex) {
    const eventStart = window.eventDates?.startDate;
    const eventEnd = window.eventDates?.endDate;
    
    if (!eventStart) return { isValid: true, message: '' };
    
    // Parse dates more carefully to handle format inconsistencies
    const saleStartDate = parseDate(saleStart);
    const saleEndDate = parseDate(saleEnd);
    const eventStartDate = parseDate(eventStart);
    const eventEndDate = parseDate(eventEnd);
    
    // Check if dates are valid
    if (saleStart && (!saleStartDate || isNaN(saleStartDate.getTime()))) {
      return { isValid: false, message: 'Invalid sale start date format' };
    }
    if (saleEnd && (!saleEndDate || isNaN(saleEndDate.getTime()))) {
      return { isValid: false, message: 'Invalid sale end date format' };
    }
    if (!eventStartDate || isNaN(eventStartDate.getTime())) {
      return { isValid: false, message: 'Invalid event start date' };
    }
    if (eventEnd && (!eventEndDate || isNaN(eventEndDate.getTime()))) {
      return { isValid: false, message: 'Invalid event end date' };
    }
    
    // Remove restrictions on sale start; any start is allowed if format is valid
    
    // Enforce: sale end must match event end exactly (date and time)
    if (saleEnd) {
      if (!eventEndDate) {
        return { isValid: false, message: 'Event end date is required to set sale end' };
      }
      if (saleEndDate.getTime() !== eventEndDate.getTime()) {
        return {
          isValid: false,
          message: `Sale end must match the event end (${eventEndDate.toLocaleString()})`
        };
      }
    }
    
    return { isValid: true, message: '' };
  }

  function showDateValidationError(input, message) {
    // Remove existing error styling
    input.classList.remove('border-red-500');
    input.classList.add('border-gray-300', 'dark:border-gray-600');
    
    // Remove existing error message
    const existingError = input.parentNode.querySelector('.date-error-message');
    if (existingError) {
      existingError.remove();
    }
    
    // Add error styling
    input.classList.remove('border-gray-300', 'dark:border-gray-600');
    input.classList.add('border-red-500');
    
    // Add error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'date-error-message text-xs text-red-500 mt-1';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
  }

  function clearDateValidationError(input) {
    input.classList.remove('border-red-500');
    input.classList.add('border-gray-300', 'dark:border-gray-600');
    
    const existingError = input.parentNode.querySelector('.date-error-message');
    if (existingError) {
      existingError.remove();
    }
  }

  function validateAndSetMinMaxDates() {
    const eventStart = window.eventDates?.startDate;
    const eventEnd = window.eventDates?.endDate;
    
    if (!eventStart) return;
    
    const eventStartDate = new Date(eventStart);
    const eventEndDate = eventEnd ? new Date(eventEnd) : null;
    
    // Format dates for datetime-local input
    const formatForInput = (date) => {
      const pad = (n) => String(n).padStart(2, '0');
      return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    };
    
    // Set attributes on sale date inputs
    const saleStartInputs = document.querySelectorAll('input[data-k="sale_start"]');
    const saleEndInputs = document.querySelectorAll('input[data-k="sale_end"]');
    
    // Remove restrictions on sale start
    saleStartInputs.forEach(input => {
      input.removeAttribute('min');
      input.removeAttribute('max');
    });
    
    saleEndInputs.forEach(input => {
      if (eventEndDate) {
        const exact = formatForInput(eventEndDate);
        // Force sale_end to exactly equal event end
        input.value = exact;
        input.min = exact;
        input.max = exact;
        input.setAttribute('readonly', 'readonly');
        input.setAttribute('disabled', 'disabled');
        input.style.pointerEvents = 'none';
      }
    });
  }
})();