<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Pusher\Pusher;

class TestRealTimeController extends Controller
{
    /**
     * Test real-time event triggering
     */
    public function testEventView(Request $request)
    {
        $eventId = $request->input('event_id', 1);
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        try {
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );
            
            $data = [
                'event_id' => $eventId,
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'timestamp' => now()->toISOString(),
                'test' => true
            ];
            
            $pusher->trigger('event-updates', 'event-viewed', $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Test event-viewed triggered',
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to trigger Pusher event: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test event save triggering
     */
    public function testEventSave(Request $request)
    {
        $eventId = $request->input('event_id', 1);
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        try {
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );
            
            $data = [
                'event_id' => $eventId,
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'action' => 'save_event',
                'timestamp' => now()->toISOString(),
                'test' => true
            ];
            
            $pusher->trigger('event-updates', 'event-saved', $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Test event-saved triggered',
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to trigger Pusher event: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test ticket purchase triggering
     */
    public function testTicketPurchase(Request $request)
    {
        $eventId = $request->input('event_id', 1);
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        try {
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );
            
            $data = [
                'event_id' => $eventId,
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'quantity' => 2,
                'total_amount' => 100.00,
                'timestamp' => now()->toISOString(),
                'test' => true
            ];
            
            $pusher->trigger('event-updates', 'ticket-purchased', $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Test ticket-purchased triggered',
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to trigger Pusher event: ' . $e->getMessage()
            ], 500);
        }
    }
}
