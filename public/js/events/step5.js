/* Step 5 - Staff management with autosave */
(function() {
  const form = document.querySelector('form[data-autosave-url], form[action*="staff"]') || document.querySelector('form');
  const searchInput = document.getElementById('staff-search');
  const results = document.getElementById('staff-search-results');
  const clearBtn = document.getElementById('staff-search-clear');
  const autocomplete = document.getElementById('staff-autocomplete');
  const cards = document.getElementById('staff-cards');
  const dataInput = document.getElementById('staff_data_json');
  const ownerInfo = document.getElementById('owner-info');
  let debounceTimer = null;
  let autosaveTimer = null;
  let flushInFlight = false;

  function getEventId() {
    const autosaveUrl = form?.getAttribute('data-autosave-url') || '';
    const idFromAutosave = (autosaveUrl.match(/events\/(\d+)/) || [])[1];
    const idFromPath = (window.location.pathname.match(/events\/(\d+)/) || [])[1];
    const idFromQuery = new URL(window.location.href).searchParams.get('id');
    return idFromQuery || idFromAutosave || idFromPath || '';
  }

  function readData() {
    try {
      const parsed = JSON.parse(dataInput?.value || '[]');
      if (Array.isArray(parsed)) {
        return { members: parsed };
      }
      if (parsed && Array.isArray(parsed.members)) {
        return parsed;
      }
      return { members: [] };
    } catch {
      return { members: [] };
    }
  }

  function writeData(obj) {
    const members = Array.isArray(obj) ? obj : (obj.members || []);
    dataInput.value = JSON.stringify(members);
  }

  function renderCards() {
    if (!cards) {
      // console.error('[Step5] Cards container not found');
      return;
    }
    
    const data = readData();
    const members = data.members || [];
    cards.innerHTML = '';
    
    if (members.length === 0) {
      cards.innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">No staff members added yet. Use the search above to add staff.</div>';
      return;
    }
    
    members.forEach((m, idx) => {
      const card = document.createElement('div');
      card.className = 'border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden';
      card.setAttribute('data-member-id', m.id);
      card.innerHTML = `
        <!-- Staff Card Header -->
        <div class="flex items-center justify-between px-3 py-2 ${m.role === 'owner' ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-gray-50 dark:bg-gray-700'} border-b border-gray-200 dark:border-gray-600">
          <div class="flex items-center gap-3 flex-1">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">${m.name || m.email || 'Member'}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">${m.email || ''}</div>
            ${m.role === 'owner' ? '<span class="px-2 py-0.5 text-xs bg-purple-100 dark:bg-purple-800 text-purple-700 dark:text-purple-300 rounded-full">Owner</span>' : ''}
          </div>
          <div class="flex items-center gap-3">
            <span class="px-2 py-1 text-xs ${m.role === 'owner' ? 'bg-purple-200 dark:bg-purple-700 text-purple-800 dark:text-purple-200' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300'} rounded">${m.role || 'staff'}</span>
            ${m.id !== Number(ownerInfo?.getAttribute('data-owner-id')) ? `<button type="button" class="p-1 text-gray-400 hover:text-red-500 transition-colors remove-staff-btn" data-member-id="${m.id}" data-member-index="${idx}" title="Remove staff member">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6"></path>
              </svg>
            </button>` : '<div class="w-6 h-6"></div>'}
          </div>
        </div>
        
        <!-- Staff Card Content -->
        <div class="bg-white dark:bg-gray-800 p-3 space-y-3">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Role</label>
              <select data-i="${idx}" data-k="role" data-member-id="${m.id}" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" ${m.role === 'owner' && m.id === Number(ownerInfo?.getAttribute('data-owner-id')) ? 'disabled' : ''}>
                <option value="staff" ${m.role==='staff'?'selected':''}>Staff</option>
                <option value="co-owner" ${m.role==='co-owner'?'selected':''}>Co-Owner</option>
                <option value="manager" ${m.role==='manager'?'selected':''}>Manager</option>
                ${m.role === 'owner' ? '<option value="owner" selected>Owner</option>' : ''}
              </select>
            </div>
          </div>
          
          ${m.role === 'owner' && m.id === Number(ownerInfo?.getAttribute('data-owner-id')) ? '' : `
          <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-2">Permissions</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
              <label class="flex items-center gap-2">
                <input type="checkbox" data-i="${idx}" data-k="can_manage_event" data-member-id="${m.id}" ${m.can_manage_event?'checked':''} class="rounded border-gray-300 text-purple-600">
                <span class="text-gray-700 dark:text-gray-300">Manage Event</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" data-i="${idx}" data-k="can_manage_staff" data-member-id="${m.id}" ${m.can_manage_staff?'checked':''} class="rounded border-gray-300 text-purple-600">
                <span class="text-gray-700 dark:text-gray-300">Manage Staff</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" data-i="${idx}" data-k="can_manage_ticket" data-member-id="${m.id}" ${m.can_manage_ticket?'checked':''} class="rounded border-gray-300 text-purple-600">
                <span class="text-gray-700 dark:text-gray-300">Manage Tickets</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" data-i="${idx}" data-k="can_manage_news" data-member-id="${m.id}" ${m.can_manage_news?'checked':''} class="rounded border-gray-300 text-purple-600">
                <span class="text-gray-700 dark:text-gray-300">Manage News</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" data-i="${idx}" data-k="can_manage_attendance" data-member-id="${m.id}" ${m.can_manage_attendance?'checked':''} class="rounded border-gray-300 text-purple-600">
                <span class="text-gray-700 dark:text-gray-300">Manage Attendance</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" data-i="${idx}" data-k="can_manage_sales" data-member-id="${m.id}" ${m.can_manage_sales?'checked':''} class="rounded border-gray-300 text-purple-600">
                <span class="text-gray-700 dark:text-gray-300">Sales Report</span>
              </label>
            </div>
          </div>
          `}
        </div>
      `;
      cards.appendChild(card);
    });
    
    // Re-attach event listeners after rendering
    attachCardListeners();
  }
  
  function attachCardListeners() {
    if (!cards) return;
    
    // Remove all existing listeners by cloning (clean slate)
    const removeButtons = cards.querySelectorAll('.remove-staff-btn');
    removeButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const memberId = this.getAttribute('data-member-id');
        const memberIndex = this.getAttribute('data-member-index');
        if (memberId) {
          removeMemberById(memberId, Number(memberIndex));
        }
      });
    });
  }


  // Function to show autosave indicator (like in autosave.js)
  function showAutosaveIndicator(status) {
    // Remove existing indicator
    const existingIndicator = document.getElementById('autosave-indicator');
    if (existingIndicator) {
      existingIndicator.remove();
    }
    
    // Create new indicator
    const indicator = document.createElement('div');
    indicator.id = 'autosave-indicator';
    indicator.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 shadow-lg ${
      status === 'success' 
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    }`;
    indicator.textContent = status === 'success' ? '✓ Auto-saved' : '✗ Save failed';
    
    document.body.appendChild(indicator);
    
    // Remove after 3 seconds
    setTimeout(() => {
      if (indicator && indicator.parentNode) {
        indicator.remove();
      }
    }, 3000);
  }

  function queueAutosave() {
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(autosave, 600);
  }

  async function autosave() {
    try {
      const url = form?.getAttribute('data-autosave-url') || `/organizer/events/staff/autosave?id=${encodeURIComponent(getEventId())}`;
      
      // Collect staff settings
      const staffWelcomeMessage = document.getElementById('staff_welcome_message')?.value || null;
      const staffInstructions = document.getElementById('staff_instructions')?.value || null;
      
      const resp = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ 
          staff_data: dataInput.value,
          staff_settings: {
            staff_welcome_message: staffWelcomeMessage,
            staff_instructions: staffInstructions
          }
        }),
        credentials: 'same-origin'
      });
      if (!resp.ok) {
        // console.warn('[Step5] Autosave response not OK', resp.status);
        showAutosaveIndicator('error');
      } else {
        // console.log('[Step5] Autosave succeeded');
        showAutosaveIndicator('success');
      }
    } catch (e) {
      // console.error('[Step5] Autosave failed', e);
      showAutosaveIndicator('error');
    }
  }

  async function flushOnExit() {
    if (flushInFlight) return;
    flushInFlight = true;
    const id = getEventId();
    if (!id) return;
    try {
      await fetch(`/organizer/events/staff/autosave/flush?id=${encodeURIComponent(id)}` , {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        keepalive: true,
        credentials: 'same-origin'
      });
      // console.log('[Step5] Autosave flush requested');
    } catch (e) {
      // swallow errors during page unload
    }
  }

  async function search(q) {
    try {
      const url = `/organizer/events/staff/search?q=${encodeURIComponent(q)}&id=${encodeURIComponent(getEventId())}`;
      const r = await fetch(url, { credentials: 'same-origin' });
      const list = await r.json();
      if (!autocomplete) return;
      autocomplete.innerHTML = '';
      (list || []).forEach((u, index) => {
        const item = document.createElement('div');
        item.className = 'group px-4 cursor-pointer transition-colors duration-150';
        item.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
        if (index === 0) item.style.backgroundColor = 'rgba(255, 255, 255, 0.2)'; // Highlight first item
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'flex items-center justify-between';
        
        const userInfo = document.createElement('div');
        userInfo.className = 'flex-1';
        
        const nameSpan = document.createElement('div');
        nameSpan.className = 'font-semibold text-sm mb-0.5';
        nameSpan.style.color = 'white';
        nameSpan.textContent = u.name || u.email || 'Unknown User';
        
        const emailSpan = document.createElement('div');
        emailSpan.className = 'text-xs';
        emailSpan.style.color = 'rgba(255, 255, 255, 0.8)';
        emailSpan.textContent = u.email || '';
        
        userInfo.appendChild(nameSpan);
        userInfo.appendChild(emailSpan);
        
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'opacity-0 group-hover:opacity-100 px-3 py-1.5 text-white text-xs rounded-md transition-all duration-200 font-medium';
        btn.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
        btn.style.border = '1px solid rgba(255, 255, 255, 0.3)';
        btn.setAttribute('data-add', JSON.stringify(u).replace(/'/g, "&apos;"));
        btn.textContent = 'Add';
        
        contentDiv.appendChild(userInfo);
        contentDiv.appendChild(btn);
        item.appendChild(contentDiv);
        
        // Add subtle separator except for last item
        if (index < list.length - 1) {
          const separator = document.createElement('div');
          separator.style.borderTop = '1px solid rgba(255, 255, 255, 0.2)';
          separator.className = 'mt-2 -mx-4';
          item.appendChild(separator);
        }
        
        // Also allow clicking the row to add
        item.addEventListener('click', (e) => {
          const payload = btn.getAttribute('data-add');
          if (!payload) return;
          if (e.target === btn || e.currentTarget === item) {
            addMember(JSON.parse(payload));
            autocomplete.classList.add('hidden');
            searchInput.value='';
          }
        });
        
        // Highlight/select behavior
        item.addEventListener('mouseenter', () => {
          autocomplete.querySelectorAll('.group').forEach(el => el.classList.remove('bg-gray-500'));
          item.classList.add('bg-gray-500');
        });
        
        autocomplete.appendChild(item);
      });
      if ((list || []).length) { autocomplete.classList.remove('hidden'); } else { autocomplete.classList.add('hidden'); }
    } catch (e) { /* console.error('[Step5] search failed', e); */ }
  }

  // Flush queued staff activity on page exit like tickets
  window.addEventListener('pagehide', flushOnExit);
  window.addEventListener('beforeunload', flushOnExit);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
      flushOnExit();
    }
  });

  function addMember(user, initialRole = 'staff') {
    if (!user || !user.id) {
      // console.error('[Step5] Invalid user data for adding:', user);
      return;
    }
    
    const data = readData();
    data.members = data.members || [];
    
    // Check if member already exists
    if (data.members.find(m => m.id === user.id)) {
      if (typeof Swal !== 'undefined' && window.Swal) {
        Swal.fire({
          icon: 'info',
          title: 'Already Added',
          text: 'This staff member is already in the list.',
          timer: 2000,
          showConfirmButton: false
        });
      }
      return;
    }
    
    // Determine if all permissions should be enabled (for co-owner and owner roles)
    const isCoOwnerOrOwner = initialRole === 'co-owner' || initialRole === 'owner';
    
    // Add the new member
    const newMember = { 
      id: user.id, 
      name: user.name, 
      email: user.email, 
      role: initialRole,
      can_manage_event: isCoOwnerOrOwner,
      can_manage_staff: isCoOwnerOrOwner,
      can_manage_ticket: isCoOwnerOrOwner,
      can_manage_news: isCoOwnerOrOwner,
      can_manage_attendance: isCoOwnerOrOwner,
      can_manage_sales: isCoOwnerOrOwner
    };
    
    data.members.push(newMember);
    writeData(data);
    
    // Render cards with animation
    renderCards();
    
    // Highlight the newly added card
    setTimeout(() => {
      const newCard = cards.querySelector(`[data-member-id="${user.id}"]`);
      if (newCard) {
        newCard.style.transition = 'all 0.3s';
        newCard.style.transform = 'scale(1.05)';
        newCard.style.boxShadow = '0 4px 12px rgba(16, 185, 129, 0.3)';
        setTimeout(() => {
          newCard.style.transform = 'scale(1)';
          newCard.style.boxShadow = '';
        }, 300);
      }
    }, 100);
    
    // Trigger autosave
    queueAutosave();
  }

  function removeMemberById(memberId, fallbackIndex = null) {
    const data = readData();
    const members = data.members || [];
    
    // Find member by ID
    let member = null;
    let actualIndex = -1;
    
    if (memberId) {
      actualIndex = members.findIndex(m => m.id === Number(memberId));
      if (actualIndex !== -1) {
        member = members[actualIndex];
      }
    }
    
    // Fallback to index if ID lookup fails
    if (!member && fallbackIndex !== null && fallbackIndex >= 0 && fallbackIndex < members.length) {
      actualIndex = fallbackIndex;
      member = members[actualIndex];
    }
    
    if (!member) {
      // console.error('[Step5] Member not found for removal. ID:', memberId, 'Index:', fallbackIndex);
      return;
    }
    
    const ownerId = ownerInfo?.getAttribute('data-owner-id');
    
    // Prevent actual event creator deletion (not just anyone with owner role)
    if (member.id === Number(ownerId)) {
      if (typeof Swal !== 'undefined' && window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Cannot Remove',
          text: 'Cannot remove the event creator. The event creator cannot be deleted.',
          confirmButtonColor: '#10b981'
        });
      } else {
        alert('Cannot remove the event creator. The event creator cannot be deleted.');
      }
      return;
    }
    
    const memberName = member.name || member.email || 'this staff member';
    
    const proceedWithRemove = () => {
      // Re-read data to ensure we have the latest
      const currentData = readData();
      const currentMembers = currentData.members || [];
      
      // Find the member by ID again (most reliable)
      const foundIndex = currentMembers.findIndex(m => m.id === member.id);
      
      if (foundIndex === -1) {
        // console.error('[Step5] Member not found in current data after confirmation, ID:', member.id);
        return;
      }
      
      // Remove the member
      currentMembers.splice(foundIndex, 1);
      
      // Write the updated data back
      writeData(currentData);
      
      // Update the UI immediately with animation
      const cardToRemove = cards.querySelector(`[data-member-id="${member.id}"]`);
      if (cardToRemove) {
        cardToRemove.style.transition = 'opacity 0.3s, transform 0.3s';
        cardToRemove.style.opacity = '0';
        cardToRemove.style.transform = 'translateX(-20px)';
        setTimeout(() => {
          renderCards();
        }, 300);
      } else {
        renderCards();
      }
      
      // Trigger autosave
      queueAutosave();
      
      // Also call global functions if they exist (for other pages like design/layout)
      if (typeof window.renderMembers === 'function') {
        window.renderMembers();
      }
      if (typeof window.renderCards === 'function') {
        window.renderCards();
      }
    };
    
    if (typeof Swal !== 'undefined' && window.Swal) {
      Swal.fire({
        title: 'Remove Staff Member?',
        text: `Are you sure you want to remove ${memberName} from the staff?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280'
      }).then((result) => {
        if (result.isConfirmed) {
          proceedWithRemove();
        }
      });
    } else {
      if (confirm(`Are you sure you want to remove ${memberName} from the staff?`)) {
        proceedWithRemove();
      }
    }
  }
  
  // Keep legacy function for backward compatibility
  function removeMember(index) {
    const data = readData();
    const members = data.members || [];
    
    if (index < 0 || index >= members.length) {
      // console.error('[Step5] Invalid index for removal:', index);
      return;
    }
    
    const member = members[index];
    if (member && member.id) {
      removeMemberById(member.id, index);
    }
  }

  function onCardsClick(e) {
    // Handle remove clicks - use event delegation for dynamically added buttons
    const removeBtn = e.target.closest('.remove-staff-btn');
    if (removeBtn) {
      e.preventDefault();
      e.stopPropagation();
      const memberId = removeBtn.getAttribute('data-member-id');
      const memberIndex = removeBtn.getAttribute('data-member-index');
      if (memberId) {
        removeMemberById(memberId, memberIndex ? Number(memberIndex) : null);
      }
      return;
    }
    
    // Legacy support for data-remove attribute
    let removeEl = e.target;
    while (removeEl && removeEl !== cards) {
      const removeIdx = removeEl.getAttribute('data-remove');
      if (removeIdx !== null) {
        e.preventDefault();
        e.stopPropagation();
        return removeMember(Number(removeIdx));
      }
      removeEl = removeEl.parentElement;
    }
  }

  function onCardsInput(e) {
    const k = e.target.getAttribute('data-k');
    const i = e.target.getAttribute('data-i');
    if (k == null || i == null) return;
    const data = readData();
    if (e.target.type === 'checkbox') {
      data.members[Number(i)][k] = !!e.target.checked;
    } else {
      data.members[Number(i)][k] = e.target.value;
      
      // If role changed to co-owner, automatically check all permissions
      if (k === 'role' && e.target.value === 'co-owner') {
        const member = data.members[Number(i)];
        const memberId = member.id;
        member.can_manage_event = true;
        member.can_manage_staff = true;
        member.can_manage_ticket = true;
        member.can_manage_news = true;
        member.can_manage_attendance = true;
        member.can_manage_sales = true;
        
        // Update UI checkboxes immediately by finding them by their data attributes
        requestAnimationFrame(() => {
          const card = cards.querySelector(`[data-member-id="${memberId}"]`);
          if (card) {
            const checkboxes = card.querySelectorAll('input[type="checkbox"][data-k]');
            checkboxes.forEach(checkbox => {
              const permKey = checkbox.getAttribute('data-k');
              if (permKey && member[permKey] !== undefined) {
                checkbox.checked = member[permKey];
              }
            });
          }
        });
      }
    }
    writeData(data);
    queueAutosave();
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Ensure creator is present as owner in data
    try {
      const data = readData();
      data.members = data.members || [];
      const ownerId = ownerInfo?.getAttribute('data-owner-id');
      const ownerName = ownerInfo?.getAttribute('data-owner-name');
      const ownerEmail = ownerInfo?.getAttribute('data-owner-email');
      if (ownerId && !data.members.find(m => String(m.id) === String(ownerId))) {
        data.members.unshift({ 
          id: Number(ownerId), 
          name: ownerName, 
          email: ownerEmail, 
          role: 'owner', 
          can_manage_event: true,
          can_manage_staff: true, 
          can_manage_ticket: true, 
          can_manage_news: true, 
          can_manage_attendance: true,
          can_manage_sales: true 
        });
        writeData(data);
      }
    } catch {}

    // console.log('[Step5] Initializing, cards container:', cards);
    // console.log('[Step5] Initial data:', readData());
    renderCards();
    searchInput?.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const query = searchInput.value.trim();
      if (query.length > 0) {
        debounceTimer = setTimeout(() => search(query), 300);
      } else {
        if (autocomplete) autocomplete.classList.add('hidden');
      }
    });
    clearBtn?.addEventListener('click', () => {
      searchInput.value='';
      if (autocomplete) autocomplete.classList.add('hidden');
    });
    autocomplete?.addEventListener('click', (e) => {
      const add = e.target.getAttribute('data-add');
      if (add) {
        addMember(JSON.parse(add));
        autocomplete.classList.add('hidden');
        searchInput.value='';
      }
    });
    // Hide dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!autocomplete || !searchInput) return;
      if (autocomplete.contains(e.target) || e.target === searchInput) return;
      autocomplete.classList.add('hidden');
    });
    // Attach event listeners using event delegation (works for dynamically added cards)
    if (cards) {
      cards.addEventListener('click', onCardsClick);
      cards.addEventListener('input', onCardsInput);
      cards.addEventListener('change', onCardsInput);
    }
    
    // Add event listeners for staff settings fields
    const staffWelcomeMessage = document.getElementById('staff_welcome_message');
    const staffInstructions = document.getElementById('staff_instructions');
    
    staffWelcomeMessage?.addEventListener('input', queueAutosave);
    staffInstructions?.addEventListener('input', queueAutosave);
    
    // Debug flush button
    document.getElementById('debug-flush')?.addEventListener('click', async () => {
      // console.log('[Step5] Manual flush triggered');
      await flushOnExit();
    });
  });
})();