<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminDashboardUpdated;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardAnalyticsController extends Controller
{
    /**
     * Timeseries data for user activity chart.
     */
    public function userActivityTimeseries(Request $request)
    {
        $range = $request->query('range', '14d');

        if ($range === 'month') {
            $year = now()->year;
            $rows = \DB::table('activity_logs')
                ->selectRaw('MONTH(created_at) as m, COUNT(*) as c')
                ->whereYear('created_at', $year)
                ->groupBy('m')
                ->orderBy('m')
                ->get();
            $byMonth = collect($rows)->keyBy('m');
            $labels = [];
            $data = [];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = date('M', mktime(0, 0, 0, $m, 1));
                $data[] = (int)($byMonth[$m]->c ?? 0);
            }
            return response()->json(['labels' => $labels, 'data' => $data, 'range' => 'month']);
        } elseif ($range === 'year') {
            $startYear = now()->year - 4;
            $rows = \DB::table('activity_logs')
                ->selectRaw('YEAR(created_at) as y, COUNT(*) as c')
                ->whereYear('created_at', '>=', $startYear)
                ->groupBy('y')
                ->orderBy('y')
                ->get();
            $byYear = collect($rows)->keyBy('y');
            $labels = [];
            $data = [];
            for ($y = $startYear; $y <= now()->year; $y++) {
                $labels[] = (string)$y;
                $data[] = (int)($byYear[$y]->c ?? 0);
            }
            return response()->json(['labels' => $labels, 'data' => $data, 'range' => 'year']);
        } else {
            $days = 14;
            $rows = \DB::table('activity_logs')
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('d')
                ->orderBy('d')
                ->get();
            $byDate = collect($rows)->keyBy('d');
            $labels = [];
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $labels[] = $date;
                $data[] = (int)($byDate[$date]->c ?? 0);
            }
            return response()->json(['labels' => $labels, 'data' => $data, 'range' => '14d']);
        }
    }

    /**
     * Aggregate admin dashboard metrics for panels.
     */
    public function metrics(Request $request)
    {
        // Traffic (last 24h)
        $since = now()->subDay();
        $pv = (int) \DB::table('event_activity_logs')->where('created_at','>=',$since)->count();
        $uv = (int) \DB::table('event_activity_logs')->where('created_at','>=',$since)->distinct('user_id')->count('user_id');
        $dau = (int) \DB::table('activity_logs')->whereDate('created_at', today())->distinct('user_id')->count('user_id');
        $wau = (int) \DB::table('activity_logs')->where('created_at','>=', now()->subDays(7))->distinct('user_id')->count('user_id');

        // Top referrers (from metadata->referrer)
        $refRows = \DB::table('event_activity_logs')
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.referrer')) as ref, COUNT(*) as c")
            ->whereNotNull('metadata')
            ->where('created_at','>=',$since)
            ->groupBy('ref')
            ->orderByDesc('c')
            ->limit(5)
            ->get();

        // Commerce
        $gross = (float) \App\Models\TicketPurchase::where('status','paid')->sum('total_amount');
        $pending = (float) \App\Models\TicketPurchase::where('status','pending')->sum('total_amount');
        $refunds = (float) (\App\Models\TicketPurchase::where('status','refunded')->sum('total_amount') ?? 0);
        // Calculate platform fee as 5% of gross revenue (typical platform fee)
        $platformFeePercentage = 0.05; // 5%
        $fees = $gross * $platformFeePercentage;
        $net = max($gross - $fees, 0);
        $failed = (int) \App\Models\TicketPurchase::where('status','failed')->count();

        // Conversion rates
        $views = (int) \DB::table('event_activity_logs')->where('action','event_viewed')->count();
        $saves = (int) (\DB::table('saved_events')->count() ?? 0);
        $orders = (int) \App\Models\TicketPurchase::where('status','paid')->count();
        $checkins = (int) \DB::table('event_ticket_information')->whereNotNull('checked_in_at')->count();
        $conv_view_save = $views ? round(($saves/$views)*100, 1) : 0;
        $conv_save_order = $saves ? round(($orders/$saves)*100, 1) : 0;
        $conv_order_checkin = $orders ? round(($checkins/$orders)*100, 1) : 0;

        // Event stats
        $active = (int) \App\Models\Event::where('status','published')->count();
        $upcoming = (int) \App\Models\Event::where('status','published')->where('start_date','>=', now())->count();
        $pendingApproval = (int) \App\Models\Event::where('status','pending_approval')->count();

        $topSelling = \DB::table('event_ticket_information')
            ->select('event_id', \DB::raw('COUNT(*) as tickets'))
            ->groupBy('event_id')->orderByDesc('tickets')->limit(5)->get();
        $mostViewed = \DB::table('event_activity_logs')
            ->select('event_id', \DB::raw('COUNT(*) as views'))
            ->whereNotNull('event_id')
            ->groupBy('event_id')->orderByDesc('views')->limit(5)->get();

        return response()->json([
            'traffic' => [ 'pv24h'=>$pv, 'uv24h'=>$uv, 'dau'=>$dau, 'wau'=>$wau, 'topReferrers'=>$refRows ],
            'commerce' => [ 'gross'=>$gross, 'net'=>$net, 'pending'=>$pending, 'refunds'=>$refunds, 'failures'=>$failed ],
            'conversion' => [ 'view_save'=>$conv_view_save, 'save_order'=>$conv_save_order, 'order_checkin'=>$conv_order_checkin ],
            'events' => [ 'active'=>$active, 'upcoming'=>$upcoming, 'pending'=>$pendingApproval, 'topSelling'=>$topSelling, 'mostViewed'=>$mostViewed ],
        ]);
    }

    /**
     * Trigger admin dashboard update event
     */
    public function triggerUpdate($adminId = null, $type = 'general')
    {
        // Get current metrics
        $metrics = $this->metrics(request());
        $metricsData = $metrics->getData(true);
        
        // Broadcast to all admins or specific admin
        if ($adminId) {
            broadcast(new AdminDashboardUpdated($adminId, $metricsData, $type));
        } else {
            // Broadcast to all admins (using a general admin ID)
            broadcast(new AdminDashboardUpdated('all', $metricsData, $type));
        }
        
        return response()->json(['message' => 'Admin dashboard update triggered']);
    }
}


