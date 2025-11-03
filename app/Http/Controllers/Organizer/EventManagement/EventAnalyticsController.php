<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventTicketInformation;
use App\Models\TicketPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EventAnalyticsController extends Controller
{
    public function getRecentSales(Request $request, Event $event)
    {
        $user = auth()->user();
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $user && $user->id === $event->user_id;
        $hasAccess = $isAdmin || $isOwner;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        try {
            $sales = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->with(['user:id,first_name,last_name,email', 'ticketInformation' => function($query) use ($event) {
                    $query->where('event_id', $event->id);
                }])
                ->latest('paid_at')
                ->latest('created_at')
                ->limit(20)
                ->get()
                ->map(function($purchase) use ($event) {
                    $user = $purchase->user;
                    
                    // Determine user name: try full name, then email, then "Guest"
                    $userName = 'Guest';
                    if ($user) {
                        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                        if (!empty($fullName) && $fullName !== ' ') {
                            $userName = $fullName;
                        } elseif (!empty($user->email)) {
                            $userName = $user->email;
                        }
                    }

                    return [
                        'id' => $purchase->id,
                        'user_name' => $userName,
                        'user_email' => $user?->email ?? null,
                        'amount' => (float) $purchase->total_amount,
                        'total_amount' => (float) $purchase->total_amount, // Also include for JS compatibility
                        'tickets' => $purchase->ticketInformation->where('event_id', $event->id)->count(),
                        'quantity' => (int) $purchase->quantity,
                        'status' => $purchase->status,
                        'purchased_at' => $purchase->paid_at 
                            ? $purchase->paid_at->format('Y-m-d H:i:s')
                            : $purchase->created_at->format('Y-m-d H:i:s'),
                        'paid_at' => $purchase->paid_at 
                            ? $purchase->paid_at->format('Y-m-d H:i:s')
                            : $purchase->created_at->format('Y-m-d H:i:s'),
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'sales' => $sales
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getRecentSales: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch recent sales',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSalesSummary(Request $request, Event $event)
    {
        try {
            $user = auth()->user();
            $isAdmin = Auth::guard('admin')->check();
            $isOwner = $user && $user->id === $event->user_id;
            $hasAccess = $isAdmin || $isOwner;
            
            if (!$hasAccess) {
                abort(403, 'Unauthorized access to this event.');
            }

            $salesStats = $this->calculateEventSalesStats($event);
            $salesTrend = $this->getEventSalesTrend($event);
            $ticketPerformance = $this->getTicketPerformance($event);

            return response()->json([
                'success' => true,
                'stats' => $salesStats,
                'trend' => $salesTrend,
                'ticket_performance' => $ticketPerformance->values() // Ensure it's a collection with values
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getSalesSummary: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch sales summary',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate comprehensive sales statistics for a specific event
     */
    private function calculateEventSalesStats(Event $event)
    {
        // Total revenue (paid purchases only) - use direct event_id
        $totalRevenue = (float) TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->sum('total_amount');

        // Total tickets sold - count ticket information records for paid purchases
        $totalTicketsSold = EventTicketInformation::where('event_id', $event->id)
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->count();

        // Total tickets checked in
        $totalCheckedIn = EventTicketInformation::where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->count();

        // Conversion rate (checked in / sold)
        $conversionRate = $totalTicketsSold > 0 ? ($totalCheckedIn / $totalTicketsSold) * 100 : 0;

        // Average ticket price
        $averageTicketPrice = $totalTicketsSold > 0 ? $totalRevenue / $totalTicketsSold : 0;

        // Revenue by ticket type - Simplified approach using unit_price matching
        $revenueByType = TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->select(
                'unit_price',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('unit_price')
            ->get()
            ->map(function($row) use ($event) {
                // Find matching ticket type by price
                $ticketType = $event->eventTickets()->where('price', $row->unit_price)->first();
                return [
                    'name' => $ticketType?->name ?? 'Ticket (₱' . number_format($row->unit_price, 2) . ')',
                    'price' => (float) $row->unit_price,
                    'count' => (int) $row->count,
                    'revenue' => (float) $row->revenue,
                ];
            })
            ->values();

        return [
            'totalRevenue' => $totalRevenue,
            'totalTicketsSold' => $totalTicketsSold,
            'totalCheckedIn' => $totalCheckedIn,
            'conversionRate' => round($conversionRate, 1),
            'averageTicketPrice' => round($averageTicketPrice, 2),
            'revenueByType' => $revenueByType,
        ];
    }

    private function getTicketPerformance(Event $event)
    {
        return $event->eventTickets()->get()->map(function($ticket) use ($event) {
            // Count purchases for this ticket type by matching unit_price to ticket price
            $purchases = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->where('unit_price', $ticket->price)
                ->get();
            
            $purchasesCount = $purchases->sum('quantity');
            $totalTicketsSold = EventTicketInformation::where('event_id', $event->id)
                ->whereHas('ticketPurchase', function($query) use ($ticket, $event) {
                    $query->where('event_id', $event->id)
                          ->where('status', 'paid')
                          ->where('unit_price', $ticket->price);
                })
                ->count();
            
            // Count checked-in tickets for this ticket type
            $checkedInCount = EventTicketInformation::where('event_id', $event->id)
                ->whereHas('ticketPurchase', function($query) use ($ticket, $event) {
                    $query->where('event_id', $event->id)
                          ->where('status', 'paid')
                          ->where('unit_price', $ticket->price);
                })
                ->whereNotNull('checked_in_at')
                ->count();
            
            // Use totalTicketsSold if it's more accurate than purchasesCount
            $actualSold = max($totalTicketsSold, $purchasesCount);
            
            return [
                'id' => $ticket->id,
                'name' => $ticket->name,
                'price' => (float) $ticket->price,
                'quantity' => $ticket->quantity,
                'total_sold' => (int) $actualSold,
                'checked_in' => (int) $checkedInCount,
                'remaining' => $ticket->quantity ? ($ticket->quantity - $actualSold) : null,
                'utilization' => $ticket->quantity > 0 ? round(($actualSold / $ticket->quantity) * 100, 1) : 0,
                'total_revenue' => (float) ($ticket->price * $actualSold),
            ];
        });
    }

    private function getEventSalesTrend(Event $event)
    {
        $trendData = TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE(COALESCE(paid_at, created_at)) as date'),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total_amount) as daily_revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Format dates and ensure we have proper structure for charts
        $formattedTrend = $trendData->map(function($day) {
            $dateObj = \Carbon\Carbon::parse($day->date);
            return [
                'date' => $day->date,
                'display_date' => $dateObj->format('M j'),
                'sales' => (int) $day->sales_count,
                'sales_count' => (int) $day->sales_count,
                'tickets_sold' => (int) $day->sales_count, // Use sales_count as tickets_sold
                'revenue' => (float) $day->daily_revenue,
                'daily_revenue' => (float) $day->daily_revenue,
            ];
        })->values();
        
        // If no data, return empty array structure
        if ($formattedTrend->isEmpty()) {
            // Return last 30 days with zeros
            $emptyTrend = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = \Carbon\Carbon::now()->subDays($i);
                $emptyTrend[] = [
                    'date' => $date->format('Y-m-d'),
                    'display_date' => $date->format('M j'),
                    'sales' => 0,
                    'sales_count' => 0,
                    'tickets_sold' => 0,
                    'revenue' => 0.0,
                    'daily_revenue' => 0.0,
                ];
            }
            return collect($emptyTrend)->values();
        }
        
        return $formattedTrend;
    }
}