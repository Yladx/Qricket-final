<x-app-layout>
    <x-slot name="title">Organizer Dashboard</x-slot>
    <x-slot name="header">
   
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ __('Organizer Dashboard') }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Manage your events and track performance
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <button onclick="openReportModal()" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generate Report
                    </button>
                    
                    @if(auth()->user()->isVerifiedOrganizer() || Auth::guard('admin')->check())
                        <a href="{{ route('organizer.events.step1', ['new' => 1]) }}" class="bg-qricket-500 hover:bg-qricket-600 text-white px-6 py-2 rounded-lg transition-colors duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Event
                        </a>
                    @else
                        <a href="{{ route('organizer.verification') }}" class="bg-qricket-500 hover:bg-qricket-600 text-white px-6 py-2 rounded-lg transition-colors duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Get verified now to create an event
                        </a>
                    @endif
                </div>
            </div>
    
    </x-slot>

    <div class="py-6 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto">
            
            <!-- View Filter -->
            <div class="mb-4">
                @php($currentView = request('view', 'all'))
                <div class="flex items-center gap-2 text-xs">
                    <button type="button" data-view="all" class="dash-view-btn px-3 py-1.5 rounded-full border border-gray-300 dark:border-gray-700 {{ $currentView==='all' ? 'bg-qricket-500 text-white border-transparent' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">All</button>
                    <button type="button" data-view="owner" class="dash-view-btn px-3 py-1.5 rounded-full border border-gray-300 dark:border-gray-700 {{ $currentView==='owner' ? 'bg-qricket-500 text-white border-transparent' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">My Posted</button>
                    <button type="button" data-view="staff" class="dash-view-btn px-3 py-1.5 rounded-full border border-gray-300 dark:border-gray-700 {{ $currentView==='staff' ? 'bg-qricket-500 text-white border-transparent' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">I'm Staff</button>
                </div>
            </div>

            <!-- Event Statistics - Compact -->
            <div class="mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-blue-500 rounded flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Event Statistics</h3>
                            </div>
                            
                            <div class="flex space-x-6">
                <!-- Total Events -->
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $totalEvents }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Total</div>
                                </div>
                                
                                <!-- Active Events -->
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $activeEvents }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Active</div>
                                </div>
                                
                                <!-- Draft Events -->
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">{{ $draftEvents }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Draft</div>
                                </div>
                                
                                <!-- Upcoming Events -->
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-purple-600 dark:text-purple-400">{{ $upcomingEvents }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Upcoming</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Analytics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                @if($canManageSales)
                <!-- Total Revenue -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Revenue</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="totalRevenue">₱{{ number_format($salesStats['totalRevenue'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Tickets Sold -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tickets Sold</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="totalTicketsSold">{{ number_format($salesStats['totalTicketsSold'] ?? 0) }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Free: {{ number_format($salesStats['freeTicketsSold'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($canManageSales)
                <!-- This Month Revenue -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Month</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="thisMonthRevenue">₱{{ number_format($salesStats['thisMonthRevenue'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

          
            </div>

            @if($canManageSales)
            <!-- Additional Sales Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <!-- Average Order Value -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-pink-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg Order Value</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($salesStats['averageOrderValue'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Removed Conversion Rate card as requested -->

                <!-- Total Spent on Additional Tickets -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Spent on Additional Tickets</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($salesStats['additionalTicketSpend'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Growth -->
                @if($isOwner ?? false)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Unclaimed Payout</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="unclaimedPayout">₱{{ number_format(max(0, $salesStats['unclaimedPayout'] ?? 0), 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            

       
            <!-- Calendar + Revenue Trend inline (move to top) -->
            <div class="mt-8 mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10 items-stretch">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Upcoming Events Calendar</h3>
                            <div class="flex items-center gap-2 text-xs">
                                <button id="cal-prev" class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700">Prev</button>
                                <span id="cal-title" class="text-gray-700 dark:text-gray-300"></span>
                                <button id="cal-next" class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700">Next</button>
                            </div>
                        </div>
                        <div id="calendar" class="grid grid-cols-7 gap-2 text-xs"></div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function(){
                                var monthEvents = @json($calendarEvents ?? []);
                                var titleEl = document.getElementById('cal-title');
                                var calEl = document.getElementById('calendar');
                                if(!calEl) return;
                                var current = new Date();
                                function render(){
                                    calEl.innerHTML='';
                                    var y=current.getFullYear(), m=current.getMonth();
                                    var first=new Date(y,m,1), start=first.getDay();
                                    var days=new Date(y,m+1,0).getDate();
                                    if(titleEl){ titleEl.textContent = current.toLocaleString(undefined,{month:'long', year:'numeric'}); }
                                    ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(function(h){ var d=document.createElement('div'); d.className='text-center font-medium text-gray-600 dark:text-gray-300 py-1'; d.textContent=h; calEl.appendChild(d); });
                                    for(var i=0;i<start;i++){ var emp=document.createElement('div'); emp.className='p-2'; calEl.appendChild(emp); }
                                    for(var day=1; day<=days; day++){
                                        var cell=document.createElement('div'); cell.className='border border-gray-200 dark:border-gray-700 rounded p-2 min-h-[64px]';
                                        var num=document.createElement('div'); num.className='text-xs text-gray-500 dark:text-gray-400'; num.textContent=day; cell.appendChild(num);
                                        var iso=y+'-'+String(m+1).padStart(2,'0')+'-'+String(day).padStart(2,'0');
                                        monthEvents.filter(function(ev){ return ev.date===iso; }).forEach(function(ev){
                                            var b=document.createElement('div'); b.className='mt-1 text-[10px] px-1.5 py-0.5 rounded bg-qricket-100 dark:bg-qricket-900 text-qricket-700 dark:text-qricket-300 truncate'; b.textContent=ev.title; cell.appendChild(b);
                                        });
                                        calEl.appendChild(cell);
                                    }
                                }
                                render();
                                var prev=document.getElementById('cal-prev'), next=document.getElementById('cal-next');
                                if(prev){ prev.addEventListener('click', function(){ current.setMonth(current.getMonth()-1); render(); }); }
                                if(next){ next.addEventListener('click', function(){ current.setMonth(current.getMonth()+1); render(); }); }
                            });
                        </script>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Revenue Trend</h3>
                            <form id="revenue-filter" class="text-xs flex items-center gap-2">
                                <select id="revenue-range" class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded px-2 py-1">
                                    <option value="30">Last 30 days</option>
                                    <option value="90">Last 90 days</option>
                                    <option value="365">Last 12 months</option>
                                </select>
                            </form>
                        </div>
                        <div class="h-64 md:h-[22rem] lg:h-full min-h-[16rem]">
                            <canvas id="revenueChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-10 items-stretch">
                <!-- Top Performing Events -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg lg:col-span-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Top Events</h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">By Revenue</span>
                        </div>
                        
                        <div id="top-events-container">
                            @if($topEvents->count() > 0)
                                <div class="space-y-4">
                                    @foreach($topEvents as $event)
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4" data-event-id="{{ $event->id }}">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ $event->title }}
                                                    </h4>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $event->start_date->format('M j, Y') }}
                                                    </p>
                                                    <div class="flex items-center mt-2 space-x-4">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400" data-stat="tickets-sold-{{ $event->id }}">
                                                            {{ number_format($event->total_tickets_sold ?? 0) }} tickets
                                                        </span>
                                <!-- Removed total orders display as requested -->
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold text-green-600 dark:text-green-400" data-stat="revenue-{{ $event->id }}">
                                                        ₱{{ number_format($event->total_revenue ?? 0, 2) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No sales data</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Revenue data will appear here once tickets are sold.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg lg:col-span-1">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Recent Sales</h3>
                        
                        <div id="recent-sales-container">
                            @if($recentSales->count() > 0)
                                <div class="space-y-4">
                                    @foreach($recentSales as $sale)
                                        <div class="flex items-start space-x-3" data-sale-id="{{ $sale->id }}">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $sale->user->first_name }} {{ $sale->user->last_name }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                    {{ $sale->event->title }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                                    {{ $sale->quantity }}x {{ $sale->ticketType->name ?? 'Ticket' }} • ₱{{ number_format($sale->total_amount, 2) }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                                    {{ $sale->paid_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No recent sales</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sales will appear here once customers purchase tickets.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Events -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg lg:col-span-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Events</h3>
                            <a href="{{ route('organizer.events.index') }}" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-500 text-sm">
                                View All
                            </a>
                        </div>
                        @if($events->count() > 0)
                            <div class="space-y-4">
                                @foreach($events->take(5) as $event)
                                    <div class="relative border border-gray-200 dark:border-gray-700 rounded-lg p-4 overflow-hidden">
                                        @php($thumb = $event->thumbnail_url ?? $event->poster_url ?? null)
                                        @if($thumb)
                                            <div class="absolute inset-0 bg-cover bg-center opacity-20 filter grayscale" style="background-image:url('{{ $thumb }}')"></div>
                                            <div class="absolute inset-0 bg-black/10"></div>
                                        @endif
                                        <div class="relative flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $event->title }}</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-300 truncate">{{ $event->location }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $event->start_date->format('M j, Y') }}</p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @if($event->status === 'draft')
                                                    <a href="{{ route('organizer.events.step1', ['continue' => $event->id]) }}" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-500 text-sm">Continue</a>
                                                @else
                                                    <a href="{{ route('organizer.events.manage', $event) }}" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-500 text-sm">Manage</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No events</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new event.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- External Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <!-- Dashboard Configuration -->
    <script>
        // Make revenue chart data available to JavaScript
        window.revenueChartData = @json($revenueTrend);
        
        // Dashboard configuration
        window.DashboardConfig = {
            pusherAppKey: '{{ config('broadcasting.connections.pusher.key') }}',
            pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
    
    <!-- Dashboard Event System -->
    <!-- Guard against missing compiled assets -->
    @php($dashAssets = [
        asset('js/events/dashboard/pusher-config.js'),
        asset('js/events/dashboard/stats-updater.js'),
        asset('js/events/dashboard/sales-updater.js'),
        asset('js/events/dashboard/events-updater.js'),
        asset('js/events/dashboard/activity-updater.js'),
        asset('js/events/dashboard/chart-manager.js'),
        asset('js/events/dashboard/index.js'),
    ])
    @foreach($dashAssets as $src)
        <script>
            (function(){
                var s=document.createElement('script'); s.src='{{ $src }}'; s.defer=true;
                s.onerror=function(){ console.warn('Dashboard asset missing:', this.src); };
                document.currentScript.parentNode.insertBefore(s, document.currentScript);
            })();
        </script>
    @endforeach
    <script>
        (function(){
            const userId = {{ auth()->id() }};
            
            function refreshOrganizerMetrics(){
                try{
                    var url = new URL('{{ route('realtime.metrics') }}', window.location.origin);
                    url.searchParams.set('scope','organizer_dashboard');
                    fetch(url.toString(), { credentials:'same-origin' })
                        .then(function(r){ return r.json(); })
                        .then(function(data){
                            if(!data) return;
                            var map = {
                                totalRevenue: data.net,
                                thisMonthRevenue: undefined, // not provided by realtime endpoint
                                unclaimedPayout: undefined // not provided by realtime endpoint
                            };
                            Object.keys(map).forEach(function(k){
                                if(typeof map[k] === 'undefined') return;
                                var el = document.querySelector('[data-stat="'+k+'"]');
                                if(el){ el.textContent = '₱' + (Number(map[k]||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}); }
                            });
                        }).catch(function(){});
                }catch(e){}
            }
            
            function updateOrganizerMetricsFromEvent(eventData) {
                if (!eventData) return;
                
                // Handle SalesReportUpdated event structure
                if (eventData.sales_data && eventData.event_stats) {
                    // Update total tickets sold if provided
                    if (eventData.event_stats.total_tickets_sold !== undefined) {
                        var ticketsEl = document.querySelector('[data-stat="totalTicketsSold"]');
                        if (ticketsEl) {
                            ticketsEl.textContent = Number(eventData.event_stats.total_tickets_sold).toLocaleString();
                        }
                    }
                    
                    // Update total revenue if provided
                    if (eventData.event_stats.total_revenue !== undefined) {
                        var revenueEl = document.querySelector('[data-stat="totalRevenue"]');
                        if (revenueEl) {
                            revenueEl.textContent = '₱' + parseFloat(eventData.event_stats.total_revenue).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                        }
                    }
                    
                    // Update event-specific revenue in the events list
                    if (eventData.event_id && eventData.sales_data.total_amount !== undefined) {
                        var eventRevenueEl = document.querySelector('[data-stat="revenue-'+eventData.event_id+'"]');
                        if (eventRevenueEl) {
                            eventRevenueEl.textContent = '₱' + parseFloat(eventData.sales_data.total_amount).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                        }
                    }
                }
            }
            
            document.addEventListener('visibilitychange', function(){ if(!document.hidden) refreshOrganizerMetrics(); });
            window.addEventListener('load', refreshOrganizerMetrics);
            
            // Real-time updates are now handled by organizer-dashboard-realtime.js
        })();
    </script>
    <script>
        // Organizer view filter + section updater
        (function(){
            function parseRevenueDataFromHtml(html){
                var m = html.match(/window\.revenueChartData\s*=\s*(\[.*?\]);/s);
                if(m && m[1]){ try { return JSON.parse(m[1]); } catch(e) { return null; } }
                return null;
            }
            function swapInner(selector, doc){
                var target = document.querySelector(selector);
                var source = doc.querySelector(selector);
                if(target && source){ target.innerHTML = source.innerHTML; }
            }
            function updateStats(doc){
                ['totalRevenue','totalTicketsSold','thisMonthRevenue','unclaimedPayout','averageOrderValue'].forEach(function(key){
                    var to = document.querySelector('[data-stat="'+key+'"]');
                    var from = doc.querySelector('[data-stat="'+key+'"]');
                    if(to && from){ 
                        // For unclaimedPayout, clamp to 0 to prevent negatives
                        if(key === 'unclaimedPayout') {
                            var value = parseFloat(from.textContent.replace(/[₱,]/g, '')) || 0;
                            to.textContent = '₱' + Math.max(0, value).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                        } else {
                            to.textContent = from.textContent; 
                        }
                    }
                });
            }
            function setActive(view){
                document.querySelectorAll('.dash-view-btn').forEach(function(btn){
                    if(btn.getAttribute('data-view')===view){
                        btn.classList.add('bg-qricket-500','text-white');
                        btn.classList.remove('bg-gray-100','dark:bg-gray-700','text-gray-700','dark:text-gray-200');
                    } else {
                        btn.classList.remove('bg-qricket-500','text-white');
                        btn.classList.add('bg-gray-100','dark:bg-gray-700','text-gray-700','dark:text-gray-200');
                    }
                });
            }
            function updateForView(view){
                var url = new URL(window.location.href);
                url.searchParams.set('view', view);
                fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                    .then(function(r){ return r.text(); })
                    .then(function(html){
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        // Replace top events and recent sales blocks
                        swapInner('#top-events-container', doc);
                        swapInner('#recent-sales-container', doc);
                        // Update stats numbers
                        updateStats(doc);
                        // Update revenue chart data
                        var data = parseRevenueDataFromHtml(html);
                        if(data && window.DashboardCharts && typeof window.DashboardCharts.updateData==='function'){
                            window.DashboardCharts.updateData(data);
                        }
                        // Update URL without reload
                        window.history.replaceState({}, '', url.toString());
                        setActive(view);
                    })
                    .catch(function(e){ console.warn('Dashboard refresh failed', e); });
            }
            document.addEventListener('DOMContentLoaded', function(){
                // Initialize active button state on page load
                var urlParams = new URLSearchParams(window.location.search);
                var currentView = urlParams.get('view') || 'all';
                setActive(currentView);
                
                document.querySelectorAll('.dash-view-btn').forEach(function(btn){
                    btn.addEventListener('click', function(){ updateForView(this.getAttribute('data-view')); });
                });
            });
        })();

        // Revenue filter handling
        document.addEventListener('DOMContentLoaded', function(){
            var select = document.getElementById('revenue-range');
            if(!select) return;
            select.addEventListener('change', function(){
                var days = parseInt(this.value, 10) || 30;
                if(window.DashboardCharts && typeof window.DashboardCharts.updateRevenueRange === 'function'){
                    window.DashboardCharts.updateRevenueRange(days);
                }
            });
        });
    </script>
    
    <!-- Report Generation Modal -->
    <div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Generate Report</h3>
                <button onclick="closeReportModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="reportForm" method="GET" action="{{ route('organizer.dashboard.report') }}">
                <input type="hidden" name="view" value="{{ request('view', 'all') }}">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Month</label>
                    <input type="month" name="month" value="{{ now()->format('Y-m') }}" 
                           class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-lg focus:ring-qricket-500 focus:border-qricket-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                    <select name="sort" class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-lg focus:ring-qricket-500 focus:border-qricket-500">
                        <option value="revenue">Revenue</option>
                        <option value="date">Date</option>
                        <option value="tickets">Tickets Sold</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort Order</label>
                    <select name="order" class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-lg focus:ring-qricket-500 focus:border-qricket-500">
                        <option value="desc">Descending</option>
                        <option value="asc">Ascending</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeReportModal()" 
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-white bg-qricket-500 rounded-lg hover:bg-qricket-600 transition-colors">
                        Generate & Download
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openReportModal() {
            document.getElementById('reportModal').classList.remove('hidden');
        }
        
        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('reportModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReportModal();
            }
        });
    </script>
    
    <!-- Real-time organizer dashboard updates -->
    <script src="{{ asset('js/organizer-dashboard-realtime.js') }}?v={{ time() }}"></script>
</x-app-layout>

