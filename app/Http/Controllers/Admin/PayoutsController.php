<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPayoutRequest;
use App\Models\AdminActivityLog;
use App\Events\AdminDashboardUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutsController extends Controller
{
    public function index()
    {
        $pending = EventPayoutRequest::with('event','organizer')->whereIn('status',['pending'])->latest()->paginate(20);
        $approved = EventPayoutRequest::with('event','organizer')->whereIn('status',['approved','released'])->latest()->paginate(20);
        $history = EventPayoutRequest::with('event','organizer')->whereIn('status',['paid','rejected'])->latest()->paginate(20);
        
        return view('admin.payouts.index', compact('pending','approved','history'));
    }

    public function approve(Request $request, EventPayoutRequest $payout)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $payout->update([
            'status' => 'approved', 
            'notes' => $payout->notes . "\n\nAdmin Notes: " . ($request->input('notes') ?? 'Approved by admin')
        ]);

        // Create database notification record for organizer
        try {
            $payout->load('organizer', 'event');
            if ($payout->organizer) {
                $payoutMessage = "Your payout request for \"{$payout->event->title}\" has been approved. Net amount: ₱" . number_format($payout->net_amount, 2) . ".";
                
                \App\Models\Notification::create([
                    'user_id' => $payout->organizer_id,
                    'admin_id' => Auth::guard('admin')->id(),
                    'type' => 'event',
                    'action' => 'payout_processed',
                    'reference_id' => $payout->event_id,
                    'title' => 'Payout Approved',
                    'message' => $payoutMessage,
                    'is_read' => false,
                    'data' => [
                        'payout_request_id' => $payout->id,
                        'event_id' => $payout->event_id,
                        'event_title' => $payout->event->title,
                        'net_amount' => $payout->net_amount,
                        'total_earnings' => $payout->total_earnings,
                        'qricket_fee' => $payout->qricket_fee,
                        'status' => $payout->status,
                    ],
                ]);
                
                // Send real-time notification
                \App\Services\NotificationService::sendUserNotification(
                    $payout->organizer_id,
                    $payoutMessage,
                    'success',
                    'Payout Approved'
                );
                
                \Log::info('Payout approval notification created', [
                    'organizer_id' => $payout->organizer_id,
                    'payout_request_id' => $payout->id,
                    'event_id' => $payout->event_id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create payout approval notification', [
                'error' => $e->getMessage(),
                'organizer_id' => $payout->organizer_id,
                'payout_request_id' => $payout->id,
            ]);
        }

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('payout_approve', [
            'payout_id' => $payout->id,
            'event_id' => $payout->event_id,
            'organizer_id' => $payout->organizer_id,
            'amount' => $payout->amount,
            'notes' => $request->input('notes'),
        ], 'Approved payout request #' . $payout->id . ' for event: ' . $payout->event->title);
        
        try { 
            event(new \App\Events\AdminMetricsPing('payout:approved')); 
        } catch (\Throwable $e) {}
        
        // Trigger admin dashboard update
        try {
            $adminController = new \App\Http\Controllers\Admin\DashboardAnalyticsController();
            $adminController->triggerUpdate(null, 'payout_approved');
        } catch (\Exception $e) {
            \Log::error('Failed to trigger admin dashboard update after payout approval: ' . $e->getMessage());
        }
        
        return back()->with('success','Payout approved successfully.');
    }

    public function reject(Request $request, EventPayoutRequest $payout)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);
        
        $payout->update([
            'status' => 'rejected', 
            'notes' => $payout->notes . "\n\nRejection Reason: " . $request->input('reason')
        ]);

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('payout_reject', [
            'payout_id' => $payout->id,
            'event_id' => $payout->event_id,
            'organizer_id' => $payout->organizer_id,
            'amount' => $payout->amount,
            'reason' => $request->input('reason'),
        ], 'Rejected payout request #' . $payout->id . ' for event: ' . $payout->event->title);
        
        try { 
            event(new \App\Events\AdminMetricsPing('payout:rejected')); 
        } catch (\Throwable $e) {}
        
        // Trigger admin dashboard update
        try {
            $adminController = new \App\Http\Controllers\Admin\DashboardAnalyticsController();
            $adminController->triggerUpdate(null, 'payout_rejected');
        } catch (\Exception $e) {
            \Log::error('Failed to trigger admin dashboard update after payout rejection: ' . $e->getMessage());
        }
        
        return back()->with('success','Payout rejected.');
    }

    public function release(EventPayoutRequest $payout)
    {
        $payout->update([
            'status' => 'released',
            'notes' => $payout->notes . "\n\nReleased for collection by admin"
        ]);
        
        // Create database notification record for organizer
        try {
            $payout->load('organizer', 'event');
            if ($payout->organizer) {
                $payoutMessage = "Your payout for \"{$payout->event->title}\" has been released. Amount: ₱" . number_format($payout->net_amount, 2) . ". You can now collect it.";
                
                \App\Models\Notification::create([
                    'user_id' => $payout->organizer_id,
                    'admin_id' => Auth::guard('admin')->id(),
                    'type' => 'event',
                    'action' => 'payout_processed',
                    'reference_id' => $payout->event_id,
                    'title' => 'Payout Released',
                    'message' => $payoutMessage,
                    'is_read' => false,
                    'data' => [
                        'payout_request_id' => $payout->id,
                        'event_id' => $payout->event_id,
                        'event_title' => $payout->event->title,
                        'net_amount' => $payout->net_amount,
                        'total_earnings' => $payout->total_earnings,
                        'qricket_fee' => $payout->qricket_fee,
                        'status' => $payout->status,
                    ],
                ]);
                
                // Send real-time notification
                \App\Services\NotificationService::sendUserNotification(
                    $payout->organizer_id,
                    $payoutMessage,
                    'success',
                    'Payout Released'
                );
                
                \Log::info('Payout release notification created', [
                    'organizer_id' => $payout->organizer_id,
                    'payout_request_id' => $payout->id,
                    'event_id' => $payout->event_id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create payout release notification', [
                'error' => $e->getMessage(),
                'organizer_id' => $payout->organizer_id,
                'payout_request_id' => $payout->id,
            ]);
        }
        
        try { 
            event(new \App\Events\AdminMetricsPing('payout:released')); 
        } catch (\Throwable $e) {}
        
        // Trigger admin dashboard update
        try {
            $adminController = new \App\Http\Controllers\Admin\DashboardAnalyticsController();
            $adminController->triggerUpdate(null, 'payout_released');
        } catch (\Exception $e) {
            \Log::error('Failed to trigger admin dashboard update after payout release: ' . $e->getMessage());
        }
        
        return back()->with('success','Payout released for collection.');
    }
}


