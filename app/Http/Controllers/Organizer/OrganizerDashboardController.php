<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventActivityLog;
use App\Models\EventStaff;
use App\Models\TicketPurchase;
use App\Services\XenditFeeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrganizerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $view = $request->get('view', 'all'); // all | owner | staff
        
        // Base query scoped by view
        $eventsQuery = Event::query();
        if ($view === 'owner') {
            $eventsQuery->where('user_id', $user->id);
        } elseif ($view === 'staff') {
            // Staff should only see published events
            $eventsQuery->where('status', 'published')
                ->whereHas('staff', function($q) use ($user) { $q->where('user_id', $user->id); });
        } else { // all
            $eventsQuery->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere(function($qq) use ($user) {
                      $qq->where('status', 'published')
                         ->whereHas('staff', function($sub) use ($user) { $sub->where('user_id', $user->id); });
                  });
            });
        }

        // Get organizer's events (events created by user OR where user is staff)
        $events = $eventsQuery
            ->with(['eventTickets', 'activityLogs', 'staff'])
            ->latest()
            ->paginate(10);

        // Resolve event IDs for filtered scope
        $eventIds = (clone $eventsQuery)->pluck('id');

        // Calculate statistics
        $totalEvents = $eventIds->count();
            
        $activeEvents = Event::whereIn('id', $eventIds)->where('status', 'published')->count();
            
        $draftEvents = Event::whereIn('id', $eventIds)->where('status', 'draft')->count();
            
        $upcomingEvents = Event::whereIn('id', $eventIds)->where('start_date', '>', now())->count();

        // Get recent activity
        $recentActivity = EventActivityLog::whereIn('event_id', $eventIds)
            ->with(['event', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        // Get upcoming events
        $upcomingEventsList = Event::whereIn('id', $eventIds)
            ->where('start_date', '>', now())
            ->where('status', 'published')
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        // Get top events by revenue
        $topEvents = $this->getTopEvents($user);
        if ($topEvents instanceof \Illuminate\Support\Collection) {
            $topEvents = $topEvents->whereIn('id', $eventIds->all())->values();
        }

        // Get recent sales across all events
        $recentSales = $this->getRecentSales($user);
        if ($recentSales instanceof \Illuminate\Support\Collection) {
            $recentSales = $recentSales->whereIn('event_id', $eventIds->all())->values();
        }

        // Calculate sales statistics filtered by event IDs
        $salesStats = $this->calculateSalesStatsForEvents($eventIds);
        
        // Check if user can manage sales for any of the events
        $canManageSales = $this->canUserManageSales($user, $eventIds);

        // Get revenue trend data for chart
        $revenueTrend = $this->getRevenueTrendForEvents($eventIds);

        // Build lightweight calendar events for the front-end calendar (current month)
        $calendarMonthStart = Carbon::now()->startOfMonth();
        $calendarMonthEnd = Carbon::now()->endOfMonth();
        
        // Use the same view filter logic for calendar events
        $calendarQuery = Event::query();
        if ($view === 'owner') {
            $calendarQuery->where('user_id', $user->id);
        } elseif ($view === 'staff') {
            $calendarQuery->where('status', 'published')
                ->whereHas('staff', function($q) use ($user) { 
                    $q->where('user_id', $user->id); 
                });
        } else { // all
            $calendarQuery->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere(function($qq) use ($user) {
                      $qq->where('status', 'published')
                         ->whereHas('staff', function($sub) use ($user) { 
                             $sub->where('user_id', $user->id); 
                         });
                  });
            });
        }
        
        $calendarEvents = $calendarQuery
            ->whereBetween('start_date', [$calendarMonthStart, $calendarMonthEnd])
            ->where('status', 'published')
            ->orderBy('start_date')
            ->get(['id','title','start_date','end_date','location'])
            ->map(function($e){
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'date' => optional($e->start_date)->toDateString(),
                    'time' => optional($e->start_date)->format('H:i'),
                    'location' => $e->location,
                ];
            });

        return view('organizer.dashboard', compact(
            'events',
            'totalEvents',
            'activeEvents',
            'draftEvents',
            'upcomingEvents',
            'recentActivity',
            'upcomingEventsList',
            'topEvents',
            'recentSales',
            'salesStats',
            'revenueTrend',
            'calendarEvents',
            'canManageSales'
        ));
    }

    private function calculateSalesStatsForEvents($eventIds)
    {
        $paid = TicketPurchase::whereHas('ticketInformation', function($q) use ($eventIds){ $q->whereIn('event_id', $eventIds); })
            ->where('status','paid');
        
        // Get comprehensive revenue statistics with fee breakdown
        $revenueStats = $paid->selectRaw('
            SUM(total_amount) as gross_revenue,
            SUM(COALESCE(transaction_fee, 0)) as total_transaction_fees,
            SUM(COALESCE(vat_amount, 0)) as total_vat,
            SUM(COALESCE(net_amount, total_amount)) as net_revenue,
            COUNT(*) as total_orders
        ')->first();
        
        $totalRevenue = $revenueStats->gross_revenue ?? 0;
        $netRevenue = $revenueStats->net_revenue ?? 0;
        $totalFees = ($revenueStats->total_transaction_fees ?? 0) + ($revenueStats->total_vat ?? 0);
        
        $totalTicketsSold = \App\Models\EventTicketInformation::whereIn('event_id', $eventIds)
            ->whereHas('ticketPurchase', function($q){ $q->where('status','paid'); })
            ->count();
        
        // Calculate unclaimed payout from ended events using net amount
        $unclaimedPayout = Event::whereIn('id', $eventIds)
            ->where('end_date', '<', now()) // Only ended events
            ->whereDoesntHave('payoutRequests', function($q) {
                $q->whereIn('status', ['pending', 'released', 'paid']);
            })
            ->with(['ticketPurchases' => function($q) {
                $q->where('status', 'paid');
            }])
            ->get()
            ->sum(function($event) {
                return $event->ticketPurchases->sum(function($purchase) {
                    return $purchase->net_amount ?? $purchase->total_amount;
                });
            });
        
        $thisMonthRevenue = (clone $paid)->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('total_amount');
        $averageOrderValue = $totalTicketsSold > 0 ? $totalRevenue / $totalTicketsSold : 0;
        
        return [
            'totalRevenue' => $totalRevenue,
            'netRevenue' => $netRevenue,
            'totalFees' => $totalFees,
            'transactionFees' => $revenueStats->total_transaction_fees ?? 0,
            'vatAmount' => $revenueStats->total_vat ?? 0,
            'totalTicketsSold' => $totalTicketsSold,
            'averageOrderValue' => $averageOrderValue,
            'thisMonthRevenue' => $thisMonthRevenue,
            'unclaimedPayout' => $unclaimedPayout,
            'totalOrders' => $revenueStats->total_orders ?? 0,
        ];
    }

    private function getRevenueTrendForEvents($eventIds)
    {
        $from = now()->subDays(30);
        return TicketPurchase::whereHas('ticketInformation', function($q) use ($eventIds){ $q->whereIn('event_id', $eventIds); })
            ->where('status','paid')
            ->where('paid_at','>=',$from)
            ->select(DB::raw('DATE(paid_at) as d'), DB::raw('SUM(total_amount) as t'))
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->map(function($row){ return ['date'=>$row->d, 'total'=>$row->t]; });
    }

    /**
     * Get organizer's events for API
     */
    public function getEvents()
    {
        $user = Auth::user();
        
        $events = Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->with(['eventTickets', 'staff'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'events' => $events
        ]);
    }

    /**
     * Get organizer statistics
     */
    public function getStats()
    {
        $user = Auth::user();
        
        $stats = [
            'totalEvents' => Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })->count(),
            
            'activeEvents' => Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })->where('status', 'published')->count(),
            
            'draftEvents' => Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })->where('status', 'draft')->count(),
            
            'upcomingEvents' => Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })->where('start_date', '>', now())->count(),
            
            'totalRevenue' => 0, // Would need to calculate from registrations
            'thisMonthRevenue' => 0, // Would need to calculate from registrations
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Get recent activity for organizer
     */
    public function getRecentActivity()
    {
        $user = Auth::user();
        
        $activities = EventActivityLog::whereHas('event', function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->with(['event', 'user'])
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }

    /**
     * Get upcoming events for organizer
     */
    public function getUpcomingEvents()
    {
        $user = Auth::user();
        
        $events = Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->where('start_date', '>', now())
            ->where('status', 'published')
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'events' => $events
        ]);
    }

    /**
     * Calculate comprehensive sales statistics for organizer
     */
    private function calculateOrganizerSalesStats($user)
    {
        // Get all events where user is creator OR staff
        $eventIds = Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })->pluck('id');

        if ($eventIds->isEmpty()) {
            return [
                'totalRevenue' => 0,
                'totalTicketsSold' => 0,
                'totalOrders' => 0,
                'averageOrderValue' => 0,
                'conversionRate' => 0,
                'thisMonthRevenue' => 0,
                'thisMonthTicketsSold' => 0,
                'pendingRevenue' => 0,
            ];
        }

        // Total revenue (paid purchases only)
        $totalRevenue = TicketPurchase::whereIn('event_id', $eventIds)
            ->where('status', 'paid')
            ->sum('total_amount');

        // Total tickets sold
        $totalTicketsSold = TicketPurchase::whereIn('event_id', $eventIds)
            ->where('status', 'paid')
            ->sum('quantity');

        // Total orders
        $totalOrders = TicketPurchase::whereIn('event_id', $eventIds)
            ->where('status', 'paid')
            ->count();

        // Average order value
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Conversion rate (paid vs total purchases)
        $totalPurchases = TicketPurchase::whereIn('event_id', $eventIds)->count();
        $conversionRate = $totalPurchases > 0 ? ($totalOrders / $totalPurchases) * 100 : 0;

        // This month's sales
        $thisMonthRevenue = TicketPurchase::whereIn('event_id', $eventIds)
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total_amount');

        $thisMonthTicketsSold = TicketPurchase::whereIn('event_id', $eventIds)
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('quantity');

        // Pending revenue
        $pendingRevenue = TicketPurchase::whereIn('event_id', $eventIds)
            ->where('status', 'pending')
            ->sum('total_amount');

        return [
            'totalRevenue' => $totalRevenue,
            'totalTicketsSold' => $totalTicketsSold,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'conversionRate' => $conversionRate,
            'thisMonthRevenue' => $thisMonthRevenue,
            'thisMonthTicketsSold' => $thisMonthTicketsSold,
            'pendingRevenue' => $pendingRevenue,
        ];
    }

    /**
     * Get top events by revenue for organizer
     */
    private function getTopEvents($user)
    {
        $eventIds = Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })->pluck('id');

        if ($eventIds->isEmpty()) {
            return collect();
        }

        $events = Event::whereIn('id', $eventIds)
            ->with(['eventTickets'])
            ->get()
            ->map(function($event) {
                // Calculate event sales stats
                $purchases = TicketPurchase::where('event_id', $event->id)
                    ->where('status', 'paid')
                    ->get();

                $event->total_revenue = $purchases->sum('total_amount');
                $event->total_tickets_sold = $purchases->sum('quantity');
                $event->total_orders = $purchases->count();

                return $event;
            })
            ->sortByDesc('total_revenue')
            ->take(5);

        return $events;
    }

    /**
     * Get recent sales across all organizer's events
     */
    private function getRecentSales($user)
    {
        $eventIds = Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })->pluck('id');

        if ($eventIds->isEmpty()) {
            return collect();
        }

        return TicketPurchase::whereIn('event_id', $eventIds)
            ->where('status', 'paid')
            ->with(['user:id,first_name,last_name', 'event:id,title'])
            ->latest('paid_at')
            ->limit(10)
            ->get();
    }

    /**
     * Get revenue trend data for the last 12 months
     */
    private function getRevenueTrend($user)
    {
        // Get all events where user is creator OR staff
        $eventIds = Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })->pluck('id');

        if ($eventIds->isEmpty()) {
            // Return empty data for last 12 months
            return $this->getEmptyRevenueTrend();
        }

        // Get revenue data for the last 12 months
        $revenueData = TicketPurchase::whereIn('event_id', $eventIds)
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('YEAR(paid_at) as year'),
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Create array for last 12 months with revenue data
        $trendData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthName = $date->format('M');
            
            $monthData = $revenueData->first(function($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });
            
            $trendData[] = [
                'month' => $monthName,
                'revenue' => $monthData ? $monthData->revenue : 0
            ];
        }

        return $trendData;
    }

    /**
     * Get empty revenue trend data for last 12 months
     */
    private function getEmptyRevenueTrend()
    {
        $trendData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            
            $trendData[] = [
                'month' => $monthName,
                'revenue' => 0
            ];
        }
        
        return $trendData;
    }

    /**
     * Generate and download analytics report
     */
    public function generateReport(Request $request)
    {
        $user = Auth::user();
        $view = $request->get('view', 'all');
        $month = $request->get('month', now()->format('Y-m'));
        $sortBy = $request->get('sort', 'revenue'); // revenue, date, tickets
        $sortOrder = $request->get('order', 'desc'); // asc, desc
        
        // Parse month filter
        $monthStart = Carbon::parse($month . '-01')->startOfMonth();
        $monthEnd = Carbon::parse($month . '-01')->endOfMonth();
        
        // Get event IDs based on view filter - only published events
        $eventsQuery = Event::where('status', 'published');
        if ($view === 'owner') {
            $eventsQuery->where('user_id', $user->id);
        } elseif ($view === 'staff') {
            // For staff view, we don't want to include these events (staff events should be filtered out)
            // This should probably be handled differently
            $eventsQuery->whereHas('staff', function($q) use ($user) { $q->where('user_id', $user->id); });
        } else {
            // For 'all' view, show owner's events and staff events
            $eventsQuery->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('staff', function($sub) use ($user) { 
                      $sub->where('user_id', $user->id); 
                  });
            });
        }
        
        $eventIds = $eventsQuery->pluck('id');
        
        // Get events with revenue data
        $events = Event::whereIn('id', $eventIds)
            ->with(['eventTickets', 'staff'])
            ->get()
            ->map(function($event) use ($monthStart, $monthEnd) {
                // Calculate actual revenue for the month
                $actualRevenue = TicketPurchase::where('event_id', $event->id)
                    ->where('status', 'paid')
                    ->whereBetween('paid_at', [$monthStart, $monthEnd])
                    ->sum('total_amount');
                
                // Calculate expected revenue (if event has tickets)
                $expectedRevenue = 0;
                if ($event->eventTickets->isNotEmpty()) {
                    foreach ($event->eventTickets as $ticket) {
                        $expectedRevenue += ($ticket->price * $ticket->quantity);
                    }
                }
                
                // Calculate tickets sold
                $ticketsSold = TicketPurchase::where('event_id', $event->id)
                    ->where('status', 'paid')
                    ->whereBetween('paid_at', [$monthStart, $monthEnd])
                    ->sum('quantity');
                
                return [
                    'event' => $event,
                    'actual_revenue' => $actualRevenue,
                    'expected_revenue' => $expectedRevenue,
                    'tickets_sold' => $ticketsSold ?? 0,
                    'order_count' => TicketPurchase::where('event_id', $event->id)
                        ->where('status', 'paid')
                        ->whereBetween('paid_at', [$monthStart, $monthEnd])
                        ->count(),
                ];
            });
        
        // Sort events
        $events = $events->sortBy(function($item) use ($sortBy, $sortOrder) {
            $value = match($sortBy) {
                'revenue' => $item['actual_revenue'],
                'date' => optional($item['event']->start_date)->timestamp ?? 0,
                'tickets' => $item['tickets_sold'],
                default => $item['actual_revenue'],
            };
            return $sortOrder === 'desc' ? -$value : $value;
        })->values();
        
        // Calculate statistics
        $totalActualRevenue = $events->sum('actual_revenue');
        $totalExpectedRevenue = $events->sum('expected_revenue');
        $totalTicketsSold = $events->sum('tickets_sold');
        $totalOrderCount = $events->sum('order_count');
        $averageOrderValue = $totalOrderCount > 0 ? $totalActualRevenue / $totalOrderCount : 0;
        
        // Prepare report data
        $reportData = [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'organizer_name' => $user->full_name,
            'organizer_email' => $user->email,
            'month' => Carbon::parse($month)->format('F Y'),
            'filter' => ucfirst($view),
            'events' => $events->map(function($item) {
                $event = $item['event'];
                return [
                    'title' => $event->title,
                    'category' => $event->category,
                    'start_date' => $event->start_date ? $event->start_date->format('Y-m-d H:i:s') : '',
                    'end_date' => $event->end_date ? $event->end_date->format('Y-m-d H:i:s') : '',
                    'location' => $event->location,
                    'ticket_types' => $event->eventTickets->map(function($ticket) {
                        return $ticket->name . ' (₱' . number_format($ticket->price, 2) . ')';
                    })->implode(', '),
                    'actual_revenue' => $item['actual_revenue'],
                    'expected_revenue' => $item['expected_revenue'],
                    'tickets_sold' => $item['tickets_sold'],
                    'orders' => $item['order_count'],
                ];
            })->toArray(),
            'statistics' => [
                'total_events' => $events->count(),
                'total_actual_revenue' => $totalActualRevenue,
                'total_expected_revenue' => $totalExpectedRevenue,
                'revenue_percentage' => $totalExpectedRevenue > 0 ? ($totalActualRevenue / $totalExpectedRevenue) * 100 : 0,
                'total_tickets_sold' => $totalTicketsSold,
                'total_orders' => $totalOrderCount,
                'average_order_value' => $averageOrderValue,
            ],
        ];
        
        // Generate Excel file using XlsxGenerator style
        $filename = 'organizer_report_' . $month . '_' . now()->format('His') . '.xls';
        $tempFile = sys_get_temp_dir() . '/' . $filename;
        
        // Generate Excel XML
        $xml = $this->generateOrganizerReportXML($reportData);
        file_put_contents($tempFile, $xml);
        
        // Return download
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * Generate Excel XML for organizer report
     */
    private function generateOrganizerReportXML($reportData)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
        
        // Styles (Enhanced from XlsxGenerator)
        $xml .= '<Styles>';
        $xml .= '<Style ss:ID="Title"><Font ss:Bold="1" ss:Size="16" ss:Color="#FFFFFF"/><Interior ss:Color="#0F766E" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="Date"><Font ss:Italic="1" ss:Size="10" ss:Color="#6B7280"/><Interior ss:Color="#F8F9FA" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="Header"><Font ss:Bold="1" ss:Size="12" ss:Color="#FFFFFF"/><Interior ss:Color="#14B8A6" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="SubHeader"><Font ss:Bold="1" ss:Size="11" ss:Color="#FFFFFF"/><Interior ss:Color="#059669" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="Currency"><NumberFormat ss:Format="₱#,##0.00"/><Font ss:Color="#059669" ss:Bold="1"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="EvenRow"><Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="OddRow"><Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="Stats"><Font ss:Bold="1" ss:Size="11" ss:Color="#1F2937"/><Interior ss:Color="#F3F4F6" ss:Pattern="Solid"/><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '<Style ss:ID="StatsValue"><Font ss:Bold="1" ss:Size="11" ss:Color="#059669"/><Interior ss:Color="#ECFDF5" ss:Pattern="Solid"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $xml .= '</Styles>';
        
        // Summary Sheet
        $xml .= '<Worksheet ss:Name="Summary">';
        $xml .= '<Table>';
        
        // Title
        $xml .= '<Row ss:Height="30">';
        $xml .= '<Cell ss:MergeAcross="5" ss:StyleID="Title"><Data ss:Type="String">Organizer Revenue Report - ' . htmlspecialchars($reportData['month']) . '</Data></Cell>';
        $xml .= '</Row>';
        
        // Info
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:MergeAcross="5" ss:StyleID="Date"><Data ss:Type="String">Generated: ' . htmlspecialchars($reportData['generated_at']) . '</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:MergeAcross="5" ss:StyleID="Date"><Data ss:Type="String">Organizer: ' . htmlspecialchars($reportData['organizer_name']) . ' (' . htmlspecialchars($reportData['organizer_email']) . ')</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:MergeAcross="5" ss:StyleID="Date"><Data ss:Type="String">Filter: ' . htmlspecialchars($reportData['filter']) . ' Events</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '<Row></Row>';
        
        // Statistics Section
        $xml .= '<Row ss:Height="25">';
        $xml .= '<Cell ss:MergeAcross="5" ss:StyleID="SubHeader"><Data ss:Type="String">SUMMARY STATISTICS</Data></Cell>';
        $xml .= '</Row>';
        
        $stats = $reportData['statistics'];
        
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:StyleID="Stats"><Data ss:Type="String">Total Events</Data></Cell>';
        $xml .= '<Cell ss:MergeAcross="4" ss:StyleID="StatsValue"><Data ss:Type="Number">' . $stats['total_events'] . '</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:StyleID="Stats"><Data ss:Type="String">Total Actual Revenue</Data></Cell>';
        $xml .= '<Cell ss:MergeAcross="4" ss:StyleID="Currency"><Data ss:Type="Number">' . $stats['total_actual_revenue'] . '</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:StyleID="Stats"><Data ss:Type="String">Total Expected Revenue</Data></Cell>';
        $xml .= '<Cell ss:MergeAcross="4" ss:StyleID="Currency"><Data ss:Type="Number">' . $stats['total_expected_revenue'] . '</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:StyleID="Stats"><Data ss:Type="String">Revenue Achievement</Data></Cell>';
        $xml .= '<Cell ss:MergeAcross="4" ss:StyleID="StatsValue"><Data ss:Type="String">' . number_format($stats['revenue_percentage'], 2) . '%</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:StyleID="Stats"><Data ss:Type="String">Total Tickets Sold</Data></Cell>';
        $xml .= '<Cell ss:MergeAcross="4" ss:StyleID="StatsValue"><Data ss:Type="Number">' . $stats['total_tickets_sold'] . '</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:StyleID="Stats"><Data ss:Type="String">Total Orders</Data></Cell>';
        $xml .= '<Cell ss:MergeAcross="4" ss:StyleID="StatsValue"><Data ss:Type="Number">' . $stats['total_orders'] . '</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '<Row ss:Height="20">';
        $xml .= '<Cell ss:StyleID="Stats"><Data ss:Type="String">Average Order Value</Data></Cell>';
        $xml .= '<Cell ss:MergeAcross="4" ss:StyleID="Currency"><Data ss:Type="Number">' . $stats['average_order_value'] . '</Data></Cell>';
        $xml .= '</Row>';
        
        $xml .= '</Table>';
        $xml .= '</Worksheet>';
        
        // Events Sheet
        $xml .= '<Worksheet ss:Name="Events">';
        $xml .= '<Table>';
        
        // Title
        $xml .= '<Row ss:Height="30">';
        $xml .= '<Cell ss:MergeAcross="9" ss:StyleID="Title"><Data ss:Type="String">Event Revenue Details</Data></Cell>';
        $xml .= '</Row>';
        
        // Headers
        $xml .= '<Row ss:Height="25">';
        $headers = ['Title', 'Category', 'Start Date', 'End Date', 'Location', 'Ticket Types', 'Actual Revenue', 'Expected Revenue', 'Tickets Sold', 'Orders'];
        foreach ($headers as $header) {
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>';
        }
        $xml .= '</Row>';
        
        // Data rows
        $rowNumber = 0;
        foreach ($reportData['events'] as $event) {
            $rowNumber++;
            $rowStyle = $rowNumber % 2 == 0 ? 'EvenRow' : 'OddRow';
            
            $xml .= '<Row ss:Height="20">';
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . htmlspecialchars($event['title']) . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . htmlspecialchars($event['category'] ?? '') . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . htmlspecialchars($event['start_date']) . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . htmlspecialchars($event['end_date']) . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . htmlspecialchars($event['location'] ?? '') . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="String">' . htmlspecialchars($event['ticket_types']) . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $event['actual_revenue'] . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $event['expected_revenue'] . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . $event['tickets_sold'] . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="Number">' . $event['orders'] . '</Data></Cell>';
            $xml .= '</Row>';
        }
        
        $xml .= '</Table>';
        $xml .= '</Worksheet>';
        $xml .= '</Workbook>';
        
        return $xml;
    }

    /**
     * Check if user can manage sales for any of the given events
     */
    private function canUserManageSales($user, $eventIds)
    {
        // If user is owner of any event, they can manage sales
        $isOwnerOfAnyEvent = Event::whereIn('id', $eventIds)
            ->where('user_id', $user->id)
            ->exists();
            
        if ($isOwnerOfAnyEvent) {
            return true;
        }
        
        // Check if user is staff with sales management permission for any event
        $hasSalesPermission = EventStaff::whereIn('event_id', $eventIds)
            ->where('user_id', $user->id)
            ->where('can_manage_sales', true)
            ->exists();
            
        return $hasSalesPermission;
    }
}