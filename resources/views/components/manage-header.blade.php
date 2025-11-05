@props(['event'])

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl md:text-3xl leading-tight m-0 text-teal-600 dark:text-teal-400">
                {{ $event->title }}
            </h2>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    @if($event->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                    @elseif($event->status === 'pending_approval') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                    @elseif($event->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                    {{ ucfirst(str_replace('_', ' ', $event->status)) }}
                </span>
                @php
                    $isAdmin = \Illuminate\Support\Facades\Auth::guard('admin')->check();
                    
                    // Context-aware back button routing
                    $currentUrl = request()->url();
                    $backRoute = route('events.browse'); // Default fallback
                    
                    if ($isAdmin) {
                        // If admin, check if we're in admin context
                        if (str_contains($currentUrl, '/admin/')) {
                            $backRoute = route('admin.events.approval.index');
                        }
                    } else {
                        // If organizer, check if we're in organizer context
                        if (str_contains($currentUrl, '/organizer/events/')) {
                            $backRoute = route('organizer.events.index');
                        }
                    }
                @endphp
                <a href="{{ $backRoute }}" class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="mt-1 flex items-center flex-wrap gap-x-6 gap-y-2">
                <p class="text-sm text-gray-600 dark:text-gray-400 m-0">Event Management Dashboard</p>
                <!-- Tabs inline with subtitle -->
                <nav class="-mb-px flex items-center gap-4 flex-wrap ml-0 md:ml-4" aria-label="Tabs">
                    @php
                        $isAdmin = \Illuminate\Support\Facades\Auth::guard('admin')->check();
                        $user = auth()->user();
                        $staff = $event->staff->firstWhere('user_id', $user?->id);
                        $isOwner = $user && (int)$event->user_id === (int)$user->id;
                        $isAdminEvent = $event->user_id === null && $isAdmin;
                        $eventEnded = optional($event->end_date)->isPast();
                        // Admins have full access to all navigation
                        $canManageAttendance = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_attendance ?? false) || ($staff?->role === 'organizer');
                        $canManageTicket = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_ticket ?? false) || ($staff?->role === 'organizer');
                        $canManageEvent = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_event ?? false) || ($staff?->role === 'organizer');
                        $canManageNews = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_news ?? false) || ($staff?->role === 'organizer');
                        $canManageSales = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_sales ?? false) || ($staff?->role === 'organizer');
                        
                        // Route helper function
                        $route = function($name, $params = []) use ($isAdmin, $event) {
                            if ($isAdmin) {
                                // Map route names to correct admin routes
                                $adminRouteMap = [
                                    'manage' => 'admin.event.manage',
                                    'attendance' => 'admin.event.attendance',
                                    'tickets' => 'admin.event.tickets',
                                    'staff' => 'admin.event.staff',
                                    'payout' => 'admin.event.payout',
                                    'edit.basic' => 'admin.event.edit.basic',
                                    'edit.layout' => 'admin.event.edit.layout',
                                    'edit.certificate' => 'admin.event.edit.certificate',
                                    'info.basic' => 'admin.event.info.basic',
                                    'info.layout' => 'admin.event.info.layout',
                                    'info.certificate' => 'admin.event.info.certificate',
                                    'news' => 'admin.event.news',
                                    'activity-log' => 'admin.event.activity-log',
                                    'feedback' => 'admin.event.feedback',
                                ];
                                
                                $routeName = $adminRouteMap[$name] ?? 'admin.event.' . $name;
                                return route($routeName, array_merge([$event], $params));
                            } else {
                                // Map route names to correct organizer routes
                                $routeMap = [
                                    'manage' => 'organizer.events.manage',
                                    'attendance' => 'organizer.events.attendance',
                                    'tickets' => 'organizer.events.tickets',
                                    'staff' => 'organizer.events.staff',
                                    'payout' => 'organizer.events.payout',
                                    'edit.basic' => 'organizer.events.edit.basic',
                                    'edit.layout' => 'organizer.events.edit.layout',
                                    'edit.certificate' => 'organizer.events.edit.certificate',
                                    'info.basic' => 'organizer.events.info.basic',
                                    'info.layout' => 'organizer.events.info.layout',
                                    'info.certificate' => 'organizer.events.info.certificate',
                                    'news' => 'organizer.events.news.index',
                                    'activity-log' => 'organizer.events.activity-log',
                                    'feedback' => 'organizer.events.feedback',
                                ];
                                
                                $routeName = $routeMap[$name] ?? 'organizer.events.' . $name;
                                return route($routeName, array_merge([$event], $params));
                            }
                        };
                    @endphp
                    <a href="{{ $route('manage') }}" class="whitespace-nowrap border-b-2 {{ request()->routeIs('organizer.events.manage') || request()->routeIs('admin.event.manage') ? 'border-qricket-600 text-qricket-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100' }} px-1 pb-1 text-sm font-medium">Overview</a>
                    @if($canManageAttendance)
                        <a href="{{ $route('attendance') }}" class="whitespace-nowrap border-b-2 {{ request()->routeIs('organizer.events.attendance') || request()->routeIs('admin.event.attendance') ? 'border-qricket-600 text-qricket-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100' }} px-1 pb-1 text-sm font-medium">Attendance</a>
                    @endif
                    @if($canManageTicket || $canManageSales)
                        <div class="relative">
                            <button type="button" id="ticketSalesDropdownButton-{{ $event->id }}" class="whitespace-nowrap border-b-2 {{ request()->routeIs('organizer.events.tickets') || request()->routeIs('organizer.events.manage') || request()->routeIs('admin.event.tickets') || request()->routeIs('admin.event.manage') ? 'border-qricket-600 text-qricket-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100' }} px-1 pb-1 text-sm font-medium inline-flex items-center">
                                Ticket & Sales
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="ticketSalesDropdownMenu-{{ $event->id }}" class="absolute hidden mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-50 min-w-[200px] right-0">
                                @if($canManageTicket)
                                <a href="{{ $route('tickets') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Ticket Information</a>
                                @endif
                                @if($canManageSales)
                                <a href="{{ $route('sales') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Sales Report</a>
                                @endif
                                @if($isOwner || $isAdmin)
                                @php
                                    try {
                                        $ticketLimitsRoute = $isAdmin ? route('admin.event.ticket-limits', $event) : route('organizer.events.ticket-limits', $event);
                                    } catch (\Exception $e) {
                                        // Fallback if route doesn't exist yet
                                        $ticketLimitsRoute = $isAdmin ? '/admin/event/' . $event->id . '/ticket-limits' : route('organizer.events.ticket-limits', $event);
                                    }
                                @endphp
                                <a href="{{ $ticketLimitsRoute }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Purchase Additional Tickets</a>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if($canManageEvent)
                        @php
                            // Resolve Staff URL robustly with fallbacks to avoid accidental '/' redirects
                            try {
                                $staffUrl = $route('staff');
                            } catch (\Throwable $e) {
                                try {
                                    $staffUrl = $isAdmin ? route('admin.event.staff', $event) : route('organizer.events.staff', $event);
                                } catch (\Throwable $e2) {
                                    $staffUrl = $isAdmin ? url('/admin/events/'.$event->id.'/staff') : url('/organizer/events/'.$event->id.'/staff');
                                }
                            }
                        @endphp
                        <a href="{{ $staffUrl }}" class="whitespace-nowrap border-b-2 {{ request()->routeIs('organizer.events.staff') || request()->routeIs('admin.event.staff') ? 'border-qricket-600 text-qricket-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100' }} px-1 pb-1 text-sm font-medium">Staff</a>
                    @endif
                    @if($canManageEvent || $canManageNews)
                    <!-- Edit Event / Event Info dropdown -->
                    <div class="relative">
                        @php
                            // Reuse variables from earlier definition
                            $canManageEventForUser = $isAdmin || $isOwner || ($staff?->can_manage_event ?? false) || ($staff?->role === 'organizer');
                            // Show "Event Info" if event ended, admin, or user can't manage event
                            $dropdownLabel = ($eventEnded || $isAdmin || !$canManageEventForUser) ? 'Event Info' : 'Edit Event';
                            $useInfoRoutes = $eventEnded || !$canManageEvent;
                        @endphp
                        <button type="button" id="editEventDropdownButton-{{ $event->id }}" class="whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 px-1 pb-1 text-sm font-medium inline-flex items-center">
                            {{ $dropdownLabel }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="editEventDropdownMenu-{{ $event->id }}" class="absolute hidden mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-50 min-w-[200px] right-0">
                            @if($useInfoRoutes)
                                <a href="{{ $route('info.basic') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Basic Info</a>
                                <a href="{{ $route('info.layout') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Layout Info</a>
                                <a href="{{ $route('info.certificate') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Certificate Info</a>
                            @else
                                <a href="{{ $route('edit.basic') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">{{ $canManageEventForUser ? 'Basic' : 'Basic Info' }}</a>
                                <a href="{{ $route('edit.layout') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">{{ $canManageEventForUser ? 'Layout' : 'Layout Info' }}</a>
                                <a href="{{ $route('edit.certificate') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">{{ $canManageEventForUser ? 'Certificate' : 'Certificate Info' }}</a>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($canManageNews)
                    <a href="{{ $route('news') }}" class="whitespace-nowrap border-b-2 {{ request()->routeIs('organizer.events.news.*') || request()->routeIs('admin.event.news.*') ? 'border-qricket-600 text-qricket-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100' }} px-1 pb-1 text-sm font-medium">News</a>
                    @endif
                    <a href="{{ $route('activity-log') }}" class="whitespace-nowrap border-b-2 {{ request()->routeIs('organizer.events.activity-log') || request()->routeIs('admin.event.activity-log') ? 'border-qricket-600 text-qricket-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100' }} px-1 pb-1 text-sm font-medium">Activity</a>
                    @php
                        // Reuse eventEnded from earlier definition
                        $canManageFeedback = $isAdmin || $isOwner || ($staff?->can_manage_event ?? false) || ($staff?->role === 'organizer');
                    @endphp
                    @if($eventEnded && $canManageFeedback)
                    <a href="{{ $route('feedback') }}" class="whitespace-nowrap border-b-2 {{ request()->routeIs('organizer.events.feedback') || request()->routeIs('admin.event.feedback') ? 'border-qricket-600 text-qricket-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100' }} px-1 pb-1 text-sm font-medium">Feedback</a>
                    @endif
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('editEventDropdownButton-{{ $event->id }}');
            const menu = document.getElementById('editEventDropdownMenu-{{ $event->id }}');
            if (!btn || !menu) return;

            let isOpen = false;

            function closeDropdown() {
                menu.classList.add('hidden');
                isOpen = false;
                document.removeEventListener('click', handleOutsideClick);
                document.removeEventListener('touchstart', handleOutsideClick);
            }

            function openDropdown() {
                menu.classList.remove('hidden');
                isOpen = true;
                // Position dropdown to avoid clipping
                const rect = btn.getBoundingClientRect();
                const menuRect = menu.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                
                // Check if dropdown would be clipped on the right
                if (rect.right + menuRect.width > viewportWidth) {
                    menu.style.right = '0';
                    menu.style.left = 'auto';
                } else {
                    menu.style.left = '0';
                    menu.style.right = 'auto';
                }
                
                setTimeout(() => {
                    document.addEventListener('click', handleOutsideClick);
                    document.addEventListener('touchstart', handleOutsideClick);
                }, 0);
            }

            function handleOutsideClick(e) {
                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                    closeDropdown();
                }
            }

            function toggleDropdown(e) {
                e.stopPropagation();
                
                if (isOpen) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            }

            // Enhanced mobile touch handling
            btn.addEventListener('click', toggleDropdown);
            btn.addEventListener('touchstart', function(e) {
                e.stopPropagation();
                toggleDropdown(e);
            }, { passive: true });
            
            // Additional mobile touch handling
            btn.addEventListener('touchend', function(e) {
                e.stopPropagation();
            }, { passive: true });
            
            // Handle touch on the button itself
            btn.style.touchAction = 'manipulation';

            // Prevent dropdown from closing when clicking inside it
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Close dropdown when scrolling on mobile
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                if (isOpen) {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(closeDropdown, 150);
                }
            }, { passive: true });
        });
    })();
</script>

<style>
    /* Hide scrollbars for webkit browsers */
    nav::-webkit-scrollbar {
        display: none;
    }
    
    /* Ensure dropdowns are above everything on mobile */
    @media (max-width: 768px) {
        .relative .absolute {
            z-index: 9999 !important;
        }
    }
</style>
<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const btnTS = document.getElementById('ticketSalesDropdownButton-{{ $event->id }}');
            const menuTS = document.getElementById('ticketSalesDropdownMenu-{{ $event->id }}');
            if (!btnTS || !menuTS) return;

            let isOpen = false;

            function closeDropdown() {
                menuTS.classList.add('hidden');
                isOpen = false;
                document.removeEventListener('click', handleOutsideClick);
                document.removeEventListener('touchstart', handleOutsideClick);
            }

            function openDropdown() {
                menuTS.classList.remove('hidden');
                isOpen = true;
                // Position dropdown to avoid clipping
                const rect = btnTS.getBoundingClientRect();
                const menuRect = menuTS.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                
                // Check if dropdown would be clipped on the right
                if (rect.right + menuRect.width > viewportWidth) {
                    menuTS.style.right = '0';
                    menuTS.style.left = 'auto';
                } else {
                    menuTS.style.left = '0';
                    menuTS.style.right = 'auto';
                }
                
                setTimeout(() => {
                    document.addEventListener('click', handleOutsideClick);
                    document.addEventListener('touchstart', handleOutsideClick);
                }, 0);
            }

            function handleOutsideClick(e) {
                if (!menuTS.contains(e.target) && !btnTS.contains(e.target)) {
                    closeDropdown();
                }
            }

            function toggleDropdown(e) {
                e.stopPropagation();
                
                if (isOpen) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            }

            // Enhanced mobile touch handling
            btnTS.addEventListener('click', toggleDropdown);
            btnTS.addEventListener('touchstart', function(e) {
                e.stopPropagation();
                toggleDropdown(e);
            }, { passive: true });
            
            // Additional mobile touch handling
            btnTS.addEventListener('touchend', function(e) {
                e.stopPropagation();
            }, { passive: true });
            
            // Handle touch on the button itself
            btnTS.style.touchAction = 'manipulation';

            // Prevent dropdown from closing when clicking inside it
            menuTS.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Close dropdown when scrolling on mobile
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                if (isOpen) {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(closeDropdown, 150);
                }
            }, { passive: true });
        });
    })();
</script>

<script>
// Simple dropdown solution for mobile
document.addEventListener('DOMContentLoaded', function() {
    // Find all dropdown buttons and menus
    const dropdownButtons = document.querySelectorAll('[id$="DropdownButton"]');
    
    dropdownButtons.forEach(button => {
        const buttonId = button.id;
        const menuId = buttonId.replace('Button', 'Menu');
        const menu = document.getElementById(menuId);
        
        if (!menu) return;
        
        // Add click event listener
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close all other dropdowns
            document.querySelectorAll('[id$="DropdownMenu"]').forEach(otherMenu => {
                if (otherMenu !== menu) {
                    otherMenu.style.display = 'none';
                    otherMenu.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            if (menu.style.display === 'block' || !menu.classList.contains('hidden')) {
                menu.style.display = 'none';
                menu.classList.add('hidden');
            } else {
                menu.style.display = 'block';
                menu.classList.remove('hidden');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.style.display = 'none';
                menu.classList.add('hidden');
            }
        });
        
        // Close dropdown on scroll
        window.addEventListener('scroll', function() {
            menu.style.display = 'none';
            menu.classList.add('hidden');
        }, { passive: true });
    });
});
</script>