<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventTicketController extends Controller
{
    /**
     * Load ticket settings from R2 storage.
     */
    private function loadTicketSettings(Event $event)
    {
        try {
            $eventDir = "events/{$event->id}";
            $disk = Storage::disk('r2');
            $settingsPath = $eventDir . '/ticket_settings.json';
            
            if ($disk->exists($settingsPath)) {
                $content = $disk->get($settingsPath);
                
                if ($content) {
                    $settings = json_decode($content, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        \Log::error("Failed to parse ticket settings JSON: " . json_last_error_msg());
                        return [];
                    }
                    
                    return $settings;
                }
            }
            
            // Return default settings if file doesn't exist
            return [
                'max_tickets_per_order' => 10,
                'refund_policy' => 'Refunds are available up to 7 days before the event. A processing fee may apply.',
                'allow_refunds' => true,
                'require_approval' => false,
                'allow_share_ticket' => true,
                'limit_per_user' => false,
                'max_tickets_per_user' => 1,
                'ticket_colors' => [
                    'primary' => '#10b981',
                    'secondary' => '#f59e0b',
                    'accent' => '#059669'
                ],
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error loading ticket settings from R2', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
            
            // Return default settings on error
            return [
                'max_tickets_per_order' => 10,
                'refund_policy' => 'Refunds are available up to 7 days before the event. A processing fee may apply.',
                'allow_refunds' => true,
                'require_approval' => false,
                'allow_share_ticket' => true,
                'limit_per_user' => false,
                'max_tickets_per_user' => 1,
                'ticket_colors' => [
                    'primary' => '#10b981',
                    'secondary' => '#f59e0b',
                    'accent' => '#059669'
                ],
            ];
        }
    }

    /**
     * Get available ticket types for an event.
     */
    public function getTicketTypes(Event $event)
    {
        try {
            \Log::info('API: Getting ticket types for event', ['event_id' => $event->id, 'event_title' => $event->title]);
            
            $ticketTypes = $event->eventTickets()
                ->where(function($query) {
                    $query->whereNull('sale_start')
                          ->orWhere('sale_start', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('sale_end')
                          ->orWhere('sale_end', '>=', now());
                })
                ->where(function($query) {
                    // Only show tickets that have available quantity (quantity - sold_count > 0 or quantity is null for unlimited)
                    $query->whereNull('quantity')
                          ->orWhereRaw('(quantity - COALESCE(sold_count, 0)) > 0');
                })
                ->get()
                ->map(function($ticket) {
                    $availableQuantity = $ticket->quantity ? ($ticket->quantity - ($ticket->sold_count ?? 0)) : null;
                    
                    return [
                        'id' => $ticket->id,
                        'name' => $ticket->name,
                        'description' => $ticket->description,
                        'price' => $ticket->price,
                        'quantity' => $availableQuantity,
                        'total_quantity' => $ticket->quantity,
                        'sold_count' => $ticket->sold_count ?? 0,
                        'sale_start' => $ticket->sale_start,
                        'sale_end' => $ticket->sale_end,
                    ];
                });

            // Load ticket settings to get limits and policies
            $ticketSettings = $this->loadTicketSettings($event);

            \Log::info('API: Found ticket types', ['count' => $ticketTypes->count(), 'ticket_types' => $ticketTypes->toArray()]);

            return response()->json([
                'success' => true,
                'ticket_types' => $ticketTypes,
                'settings' => [
                    'max_tickets_per_order' => $ticketSettings['max_tickets_per_order'] ?? 10,
                    'refund_policy' => $ticketSettings['refund_policy'] ?? null,
                    'allow_refunds' => $ticketSettings['allow_refunds'] ?? true,
                    'require_approval' => $ticketSettings['require_approval'] ?? false,
                    'allow_share_ticket' => $ticketSettings['allow_share_ticket'] ?? true,
                    'limit_per_user' => $ticketSettings['limit_per_user'] ?? false,
                    'max_tickets_per_user' => $ticketSettings['max_tickets_per_user'] ?? 1,
                    'ticket_colors' => $ticketSettings['ticket_colors'] ?? [
                        'primary' => '#10b981',
                        'secondary' => '#f59e0b',
                        'accent' => '#059669'
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('API: Error getting ticket types', [
                'event_id' => $event->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load ticket types',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
