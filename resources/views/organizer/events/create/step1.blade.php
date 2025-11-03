<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-slot name="title">Create Event - Step 1: Basic Information</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4">
            {{ __('Create Event - Step 1: Basic Information') }}
        </h2>

        @include('organizer.events.components.progress-bar', ['currentStep' => 1])
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div >
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form id="step1Form" method="POST" action="{{ route('organizer.events.step1.store') }}{{ $eventId ? '?id=' . $eventId : '' }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @include('organizer.events.forms.basic-info', ['eventId' => $eventId, 'eventData' => $eventData])

                        

                        <hr>
                        <!-- Navigation Buttons -->
                        <div class="flex items-center justify-between pt-6">
                            <div class="flex items-center space-x-3">
                                <button type="button" id="btnCancelDraft" class="inline-flex items-center px-6 py-3 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-lg font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-500 dark:active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Cancel
                                </button>
                                @if($eventId)
                                <button type="button" id="btnDeleteEvent" class="inline-flex items-center px-6 py-3 bg-red-100 dark:bg-red-900 border border-transparent rounded-lg font-medium text-sm text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Delete
                                </button>
                                @endif
                            </div>

                            <button type="submit" id="submitBtn" class="inline-flex items-center px-6 py-3 bg-qricket-500 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-qricket-600 focus:bg-qricket-600 active:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <span id="submitText">Next Step</span>
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" id="submitSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

   

    <!-- SweetAlert2 + Utilities (for consistent dark mode styling) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/sweetalert-utils.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[Step1] DOMContentLoaded');
            const cancelBtn = document.getElementById('btnCancelDraft');
            const deleteBtn = document.getElementById('btnDeleteEvent');
            const cancelForm = document.getElementById('cancelForm');
            console.log('[Step1] Elements', { hasCancelBtn: !!cancelBtn, hasCancelForm: !!cancelForm, eventId: '{{ $eventId }}' });
            let isPromptOpen = false;
            let skipUnloadPrompt = false;
            const eventsIndexUrl = "{{ route('organizer.events.index') }}";
            const cancelUrl = "{{ $eventId ? route('organizer.events.cancel', ['id' => $eventId]) : route('organizer.events.index') }}";

            function isEffectivelyEmpty() {
                const title = (document.getElementById('title')?.value || '').trim();
                const description = (document.getElementById('description')?.value || '').trim();
                const category = (document.getElementById('category')?.value || '').trim();
                const location = (document.getElementById('location')?.value || '').trim();
                const isTitleEmpty = title.length === 0 || title === 'Untitled Event';
                const isDescEmpty = description.length === 0;
                // Treat 'other' as valid info; only empty string is empty
                const isCategoryEmpty = category.length === 0;
                const isLocationEmpty = location.length === 0;
                const empty = isTitleEmpty && isDescEmpty && isCategoryEmpty && isLocationEmpty;
                console.log('[Step1] isEffectivelyEmpty', { title, descriptionLen: description.length, category, location, empty });
                return empty;
            }

            function ensureSweetAlertLoaded(callback) {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    console.log('[Step1] Swal present');
                    callback();
                    return;
                }
                console.log('[Step1] Loading SweetAlert script…');
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                script.onload = () => { console.log('[Step1] SweetAlert script loaded'); callback(); };
                script.onerror = () => { console.warn('[Step1] SweetAlert script failed to load'); callback(); };
                document.head.appendChild(script);
            }

            function onCancelClick(e) {
                console.log('[Step1] Cancel clicked');
                e.preventDefault();
                if (isPromptOpen) { console.log('[Step1] Prompt already open; ignoring'); return; }
                
                ensureSweetAlertLoaded(() => {
                    if (window.Swal) {
                        isPromptOpen = true;
                        window.Swal.fire({
                            title: 'Cancel Event Creation?',
                            text: '{{ $eventId ? "This will delete the event and all your changes will be lost." : "This will cancel the event creation process." }}',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Leave',
                            cancelButtonText: 'Stay',
                            confirmButtonColor: '#dc2626',
                            cancelButtonColor: '#059669',
                            reverseButtons: true
                        }).then((result) => {
                            isPromptOpen = false;
                            if (result.isConfirmed) {
                                // User chose "Leave" - delete event if it exists
                                console.log('[Step1] User chose Leave - deleting event');
                                deleteEventAndRedirect();
                            } else {
                                // User chose "Stay" - do nothing
                                console.log('[Step1] User chose Stay - remaining on page');
                            }
                        });
                    } else {
                        const confirmed = confirm('{{ $eventId ? "Cancel event creation? This will delete the event and all your changes will be lost." : "Cancel event creation?" }}');
                        if (confirmed) {
                            deleteEventAndRedirect();
                        }
                    }
                });
            }

            function deleteEventAndRedirect() {
                @if($eventId)
                    // Delete the event from database
                    const deleteUrl = "{{ route('organizer.events.destroy', $eventId) }}";
                    const token = '{{ csrf_token() }}';
                    
                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    }).then(() => {
                        console.log('[Step1] Event deleted, redirecting to events list');
                        temporarilyDisableBeforeUnload(() => {
                            window.location.replace("{{ route('organizer.events.index') }}");
                        });
                    }).catch((error) => {
                        console.error('[Step1] Error deleting event:', error);
                        // Still redirect even if delete fails
                        temporarilyDisableBeforeUnload(() => {
                            window.location.replace("{{ route('organizer.events.index') }}");
                        });
                    });
                @else
                    // No event to delete, just redirect
                    console.log('[Step1] No event to delete, redirecting to events list');
                    temporarilyDisableBeforeUnload(() => {
                        window.location.replace("{{ route('organizer.events.index') }}");
                    });
                @endif
            }

            if (cancelBtn && cancelForm) {
                cancelBtn.addEventListener('click', function(e) {
                    onCancelClick(e);
                });
            }

            // Delegated fallback in case direct binding fails or button is re-rendered
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('#btnCancelDraft');
                if (!btn) return;
                console.log('[Step1] Delegated cancel handler fired');
                onCancelClick(e);
            });

            if (deleteBtn) {
                deleteBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    ensureSweetAlertLoaded(() => {
                        const run = () => {
                            const url = "{{ $eventId ? route('organizer.events.destroy', $eventId) : '' }}";
                            if (!url) return;
                            const token = '{{ csrf_token() }}';
                            if (window.Swal) {
                                window.Swal.fire({
                                    title: 'Delete this event?',
                                    text: 'This action cannot be undone.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Delete',
                                    confirmButtonColor: '#d33',
                                    cancelButtonText: 'Cancel'
                                }).then((res) => {
                                    if (!res.isConfirmed) return;
                                    temporarilyDisableBeforeUnload(() => {
                                        fetch(url, { method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, credentials: 'same-origin' }).finally(() => {
                                            window.location.replace("{{ route('organizer.events.index') }}");
                                        });
                                    });
                                });
                            } else {
                                if (confirm('Delete this event? This cannot be undone.')) {
                                    temporarilyDisableBeforeUnload(() => {
                                        fetch(url, { method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, credentials: 'same-origin' }).finally(() => {
                                            window.location.replace("{{ route('organizer.events.index') }}");
                                        });
                                    });
                                }
                            }
                        };
                        run();
                    });
                });
            }

            // Removed sendCancelIfEmpty - we no longer auto-delete drafts

            // Removed pagehide auto-cancel - drafts are preserved when users leave

            // SweetAlert confirm on in-page navigations (links) and back button
            function confirmLeave(callback) {
                ensureSweetAlertLoaded(() => {
                    if (window.Swal) {
                        window.Swal.fire({
                            title: 'Leave this page?',
                            text: '{{ $eventId ? "This will delete the event and all your changes will be lost." : "This will cancel the event creation process." }}',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Leave',
                            cancelButtonText: 'Stay',
                            confirmButtonColor: '#dc2626',
                            cancelButtonColor: '#059669',
                            reverseButtons: true
                        }).then((res) => {
                            if (res.isConfirmed) {
                                // User chose "Leave" - delete event if it exists
                                deleteEventAndRedirect();
                            }
                            callback(!!res.isConfirmed);
                        });
                    } else {
                        const ok = confirm('{{ $eventId ? "Leave this page? This will delete the event and all your changes will be lost." : "Leave this page? This will cancel the event creation process." }}');
                        if (ok) {
                            deleteEventAndRedirect();
                        }
                        callback(ok);
                    }
                });
            }

            function temporarilyDisableBeforeUnload(fn) {
                // Ensure the native prompt does not appear for this navigation
                skipUnloadPrompt = true;
                window.removeEventListener('beforeunload', beforeUnloadGuard);
                window.onbeforeunload = null;
                try { fn(); } finally { /* do not reattach to avoid race */ }
            }

            // Disable native beforeunload prompt on Step 1 to avoid double dialogs
            function beforeUnloadGuard(e) { /* disabled */ }
            window.onbeforeunload = null;
            try { window.removeEventListener('beforeunload', beforeUnloadGuard); } catch(_) {}

            // Intercept link clicks
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]');
                if (!link) return;
                // Allow our explicit actions: Next Step submit, Cancel button (handled separately)
                const href = link.getAttribute('href') || '';
                if (href.startsWith('#') || link.hasAttribute('download') || link.target === '_blank') return;
                // If form is non-empty, let it go
                if (!isEffectivelyEmpty()) return;
                e.preventDefault();
                console.log('[Step1] Link navigation intercepted for SweetAlert', href);
                if (isPromptOpen) { console.log('[Step1] Prompt already open; ignoring'); return; }
                isPromptOpen = true;
                confirmLeave((ok) => {
                    isPromptOpen = false;
                    if (ok) {
                        // Just navigate to the destination - no need to delete draft
                        temporarilyDisableBeforeUnload(() => { window.location.href = href; });
                    }
                });
            }, true);

            // AJAX Form Submission
            const form = document.getElementById('step1Form');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');

            if (form && submitBtn) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    // Show loading state
                    submitBtn.disabled = true;
                    submitText.textContent = 'Saving...';
                    submitSpinner.classList.remove('hidden');

                    try {
                        const formData = new FormData(form);
                        
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Creation flow: no blocking modal, go straight to next step
                            window.location.href = data.redirect_url;
                        } else {
                            // Show error message
                            let errorMessage = data.message || 'An error occurred while saving.';
                            
                            if (data.errors && Array.isArray(data.errors)) {
                                errorMessage = data.errors.join('<br>');
                            } else if (data.error_details) {
                                // Convert validation errors to readable format
                                const errorList = [];
                                for (const field in data.error_details) {
                                    errorList.push('<strong>' + field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ') + ':</strong> ' + data.error_details[field][0]);
                                }
                                errorMessage = errorList.join('<br>');
                            }

                            if (window.Swal) {
                                await Swal.fire({
                                    title: 'Validation Error',
                                    html: errorMessage,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                alert(errorMessage.replace(/<br>/g, '\n'));
                            }
                        }
                    } catch (error) {
                        console.error('Step 1 submission error:', error);
                        
                        if (window.Swal) {
                            await Swal.fire({
                                title: 'Error!',
                                text: 'Network error occurred while saving. Please check your connection and try again.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Network error occurred while saving. Please check your connection and try again.');
                        }
                    } finally {
                        // Reset button state
                        submitBtn.disabled = false;
                        submitText.textContent = 'Next Step';
                        submitSpinner.classList.add('hidden');
                    }
                });
            }

            // Intercept back/forward via popstate
            window.addEventListener('popstate', function(e) {
                if (!isEffectivelyEmpty()) return;
                console.log('[Step1] popstate intercepted for SweetAlert');
                history.pushState(null, document.title, window.location.href);
                if (isPromptOpen) { console.log('[Step1] Prompt already open; ignoring'); return; }
                isPromptOpen = true;
                confirmLeave((ok) => {
                    isPromptOpen = false;
                    if (ok) {
                        temporarilyDisableBeforeUnload(() => { history.back(); });
                    }
                });
            });
            // Prime a state so we can catch back once
            try { history.pushState(null, document.title, window.location.href); } catch(_) {}
        });
    </script>

</x-app-layout>






