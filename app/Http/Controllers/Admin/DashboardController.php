<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\SupportTicket;
use App\Models\EventTicket;
use App\Models\TicketPurchase;
use App\Models\Subscription;
use App\Models\TicketLimitPurchase;
use App\Services\XenditFeeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get comprehensive revenue statistics with fee breakdown
        $revenueStats = $this->getRevenueStats();
        
        // Get basic statistics
        $stats = [
            'total_events' => Event::count(),
            'active_events' => Event::where('status', 'active')->count(),
            'pending_events' => Event::where('status', 'pending_approval')->count(),
            'total_users' => User::count(),
            'total_organizers' => User::where('role', 'organizer')->count(),
            'open_tickets' => SupportTicket::where('status', 'open')->count(),
            'total_revenue' => $revenueStats['total_gross'],
            'net_revenue' => $revenueStats['total_net'],
            'total_fees' => $revenueStats['total_fees'],
            'total_vat' => $revenueStats['total_vat'],
            'tickets_sold' => EventTicket::sum('quantity'),
        ];

        // Get recent events
        $recentEvents = Event::with('organizer')
            ->latest()
            ->limit(5)
            ->get();

        // Get pending approvals
        $pendingApprovals = Event::where('status', 'pending_approval')
            ->with('organizer')
            ->latest()
            ->limit(5)
            ->get();

        // Get recent users
        $recentUsers = User::latest()
            ->limit(5)
            ->get();

        // Get recent support tickets
        $recentTickets = SupportTicket::with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Get monthly statistics for chart
        $monthlyStats = $this->getMonthlyStats();

        return view('admin.dashboard', compact(
            'stats',
            'revenueStats',
            'recentEvents',
            'pendingApprovals',
            'recentUsers',
            'recentTickets',
            'monthlyStats'
        ));
    }

    public function analytics()
    {
        $analytics = [
            'events_by_status' => Event::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'events_by_month' => Event::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get(),
            'users_by_role' => User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get(),
            'revenue_by_month' => EventTicket::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as revenue')
            )
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get(),
        ];

        return response()->json($analytics);
    }

    private function getMonthlyStats()
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'events' => Event::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'users' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'revenue' => EventTicket::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount'),
            ];
        }

        return $months;
    }
    
    /**
     * Get comprehensive revenue statistics with fee breakdown
     */
    private function getRevenueStats()
    {
        // Ticket purchases revenue
        $ticketRevenue = TicketPurchase::where('status', 'paid')
            ->selectRaw('
                SUM(total_amount) as gross_revenue,
                SUM(COALESCE(transaction_fee, 0)) as total_transaction_fees,
                SUM(COALESCE(vat_amount, 0)) as total_vat,
                SUM(COALESCE(net_amount, total_amount)) as net_revenue,
                COUNT(*) as total_transactions
            ')
            ->first();
        
        // Subscription revenue
        $subscriptionRevenue = Subscription::where('status', 'active')
            ->selectRaw('
                SUM(amount) as gross_revenue,
                SUM(COALESCE(transaction_fee, 0)) as total_transaction_fees,
                SUM(COALESCE(vat_amount, 0)) as total_vat,
                SUM(COALESCE(net_amount, amount)) as net_revenue,
                COUNT(*) as total_subscriptions
            ')
            ->first();
        
        // Additional tickets revenue
        $additionalTicketsRevenue = TicketLimitPurchase::where('status', 'paid')
            ->selectRaw('
                SUM(amount) as gross_revenue,
                SUM(COALESCE(transaction_fee, 0)) as total_transaction_fees,
                SUM(COALESCE(vat_amount, 0)) as total_vat,
                SUM(COALESCE(net_amount, amount)) as net_revenue,
                COUNT(*) as total_purchases
            ')
            ->first();
        
        // Calculate totals
        $totalGross = ($ticketRevenue->gross_revenue ?? 0) + 
                     ($subscriptionRevenue->gross_revenue ?? 0) + 
                     ($additionalTicketsRevenue->gross_revenue ?? 0);
        
        $totalTransactionFees = ($ticketRevenue->total_transaction_fees ?? 0) + 
                               ($subscriptionRevenue->total_transaction_fees ?? 0) + 
                               ($additionalTicketsRevenue->total_transaction_fees ?? 0);
        
        $totalVat = ($ticketRevenue->total_vat ?? 0) + 
                   ($subscriptionRevenue->total_vat ?? 0) + 
                   ($additionalTicketsRevenue->total_vat ?? 0);
        
        $totalNet = ($ticketRevenue->net_revenue ?? 0) + 
                   ($subscriptionRevenue->net_revenue ?? 0) + 
                   ($additionalTicketsRevenue->net_revenue ?? 0);
        
        return [
            'total_gross' => round($totalGross, 2),
            'total_transaction_fees' => round($totalTransactionFees, 2),
            'total_vat' => round($totalVat, 2),
            'total_fees' => round($totalTransactionFees + $totalVat, 2),
            'total_net' => round($totalNet, 2),
            'breakdown' => [
                'tickets' => [
                    'gross' => round($ticketRevenue->gross_revenue ?? 0, 2),
                    'fees' => round($ticketRevenue->total_transaction_fees ?? 0, 2),
                    'vat' => round($ticketRevenue->total_vat ?? 0, 2),
                    'net' => round($ticketRevenue->net_revenue ?? 0, 2),
                    'transactions' => $ticketRevenue->total_transactions ?? 0,
                ],
                'subscriptions' => [
                    'gross' => round($subscriptionRevenue->gross_revenue ?? 0, 2),
                    'fees' => round($subscriptionRevenue->total_transaction_fees ?? 0, 2),
                    'vat' => round($subscriptionRevenue->total_vat ?? 0, 2),
                    'net' => round($subscriptionRevenue->net_revenue ?? 0, 2),
                    'transactions' => $subscriptionRevenue->total_subscriptions ?? 0,
                ],
                'additional_tickets' => [
                    'gross' => round($additionalTicketsRevenue->gross_revenue ?? 0, 2),
                    'fees' => round($additionalTicketsRevenue->total_transaction_fees ?? 0, 2),
                    'vat' => round($additionalTicketsRevenue->total_vat ?? 0, 2),
                    'net' => round($additionalTicketsRevenue->net_revenue ?? 0, 2),
                    'transactions' => $additionalTicketsRevenue->total_purchases ?? 0,
                ],
            ],
        ];
    }
        }