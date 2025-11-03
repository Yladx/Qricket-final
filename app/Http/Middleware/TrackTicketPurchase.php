<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\EventActivityLog;
use Pusher\Pusher;
use Symfony\Component\HttpFoundation\Response;

class TrackTicketPurchase {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Track successful ticket purchases
        if ($request->isMethod('POST') && 
            ($request->route()->getName() === 'tickets.purchase' || 
             $request->route()->getName() === 'tickets.purchase.success')) {
            
            $user = Auth::user();
            
            if (!$user || Auth::guard('admin')->check()) {
                return $response;
            }
            
            try {
                // Get event ID from request
                $eventId = $request->input('event_id') ?? $request->route('event')?->id;
                
                if (!$eventId) {
                    return $response;
                }
                
                // Log the ticket purchase
                EventActivityLog::create([
                    'event_id' => $eventId,
                    'user_id' => $user->id,
                    'action' => 'purchase_ticket',
                    'description' => "User purchased ticket for event",
                    'metadata' => json_encode([
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'ticket_quantity' => $request->input('quantity', 1),
                        'total_amount' => $request->input('total_amount'),
                        'timestamp' => now()->toISOString()
                    ])
                ]);
                
                // Trigger Pusher event for real-time updates
                $this->triggerPusherEvent('ticket-purchased', [
                    'event_id' => $eventId,
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'quantity' => $request->input('quantity', 1),
                    'total_amount' => $request->input('total_amount'),
                    'timestamp' => now()->toISOString()
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error tracking ticket purchase: ' . $e->getMessage());
            }
        }
        
        return $response;
    }
    
    /**
     * Trigger Pusher event
     */
    private function triggerPusherEvent($event, $data)
    {
        try {
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );
            
            $pusher->trigger('event-updates', $event, $data);
        } catch (\Exception $e) {
            Log::error('Error triggering Pusher event: ' . $e->getMessage());
        }
    }
}
