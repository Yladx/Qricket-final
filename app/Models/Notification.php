<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SupportTicket;
use App\Models\Event;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'type',
        'action',
        'reference_id',
        'title',
        'message',
        'data',
        'is_read',
    ];

    protected $appends = ['title', 'message'];
    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
    ];

    public function getTitleAttribute(): string
    {
        // Handle organizer notifications
        if ($this->type === 'organizer') {
            switch ($this->action) {
                case 'verified':
                    return 'Organizer Verified';
                case 'accept':
                    return 'Organizer Verification Approved';
                case 'reject':
                    return 'Organizer Verification Rejected';
                default:
                    return 'Organizer Update';
            }
        }
        
        // Handle ticket notifications
        if ($this->type === 'ticket') {
            switch ($this->action) {
                case 'reply':
                    return 'Support Update';
                case 'share_request':
                    return 'Ticket Share Request';
                case 'share_accepted':
                    return 'Ticket Share Accepted';
                case 'share_declined':
                    return 'Ticket Share Declined';
                case 'refund_completed':
                    return 'Refund Processed';
                case 'refund_rejected':
                    return 'Refund Request Rejected';
                default:
                    return 'Ticket Update';
            }
        }
        
        // Handle event notifications
        if ($this->type === 'event') {
            switch ($this->action) {
                case 'accept':
                    return 'Event Approved';
                case 'reject':
                    return 'Event Rejected';
                case 'news':
                    return 'Event News Update';
                case 'event_ended':
                    return 'Event Ended';
                case 'feedback_request':
                    return 'Feedback Request';
                case 'staff_added':
                    return 'Added as Event Staff';
                case 'attended':
                    return 'Checked In Successfully';
                case 'payout_processed':
                    return 'Payout Processed';
                default:
                    return 'Event Update';
            }
        }
        
        // Handle support notifications
        if ($this->type === 'support') {
            switch ($this->action) {
                case 'admin_replied':
                    return 'Support Ticket Reply';
                case 'user_replied':
                    return 'Support Ticket Update';
                default:
                    return 'Support Update';
            }
        }
        
        return ucfirst((string) $this->type);
    }

    public function getMessageAttribute(): string
    {
        // Handle organizer notifications
        if ($this->type === 'organizer') {
            switch ($this->action) {
                case 'verified':
                    return 'Your organizer account has been verified. You can now access all organizer features.';
                case 'accept':
                    return 'Congratulations! Your organizer verification has been approved. You can now create and manage events.';
                case 'reject':
                    $verification = \App\Models\OrganizerVerification::find($this->reference_id);
                    $reason = $verification?->rejection_reason ?? 'Please check your submission and try again.';
                    return "Your organizer verification request was rejected. Reason: {$reason}";
                default:
                    return 'Organizer account update';
            }
        }
        
        // Handle ticket notifications
        if ($this->type === 'ticket') {
            if ($this->action === 'reply') {
                $ticket = SupportTicket::find($this->reference_id);
                $ticketNumber = $ticket?->ticket_number ? ('#' . $ticket->ticket_number) : '#' . (string) $this->reference_id;
                return 'Qricket Support replied to your ticket ' . $ticketNumber;
            }
            
            if ($this->action === 'refund_completed') {
                // Use the stored message from the database
                return $this->attributes['message'] ?? 'Your refund has been processed successfully.';
            }
            
            if ($this->action === 'refund_rejected') {
                // Use the stored message from the database
                return $this->attributes['message'] ?? 'Your refund request has been rejected.';
            }
            
            // Handle ticket share notifications - use stored message first, fallback to dynamic generation
            if (in_array($this->action, ['share_request', 'share_accepted', 'share_declined'])) {
                // Use stored message from database if available (more reliable)
                if (isset($this->attributes['message']) && !empty($this->attributes['message'])) {
                    return $this->attributes['message'];
                }
                
                // Fallback: Try to generate message dynamically if stored message is missing
                try {
                    $ticketShare = \App\Models\EventTicketShare::with(['sharer', 'recipient', 'event'])->find($this->reference_id);
                    
                    if (!$ticketShare) {
                        // Try to get info from data attribute
                        $data = $this->data ?? [];
                        $eventTitle = $data['event_title'] ?? 'Unknown Event';
                        
                        switch ($this->action) {
                            case 'share_request':
                                $sharerName = $data['sharer_name'] ?? 'Someone';
                                return "{$sharerName} wants to share a ticket for '{$eventTitle}' with you.";
                            case 'share_accepted':
                                $recipientName = $data['recipient_name'] ?? 'Someone';
                                return "{$recipientName} accepted your ticket share for '{$eventTitle}'.";
                            case 'share_declined':
                                $recipientName = $data['recipient_name'] ?? 'Someone';
                                return "{$recipientName} declined your ticket share for '{$eventTitle}'.";
                            default:
                                return 'Ticket share notification';
                        }
                    }
                    
                    $eventTitle = $ticketShare->event?->title ?? ($data['event_title'] ?? 'Unknown Event');
                    
                    switch ($this->action) {
                        case 'share_request':
                            $sharerName = $ticketShare->sharer?->full_name ?? ($data['sharer_name'] ?? 'Someone');
                            return "{$sharerName} wants to share a ticket for '{$eventTitle}' with you.";
                            
                        case 'share_accepted':
                            $recipientName = $ticketShare->recipient?->full_name ?? ($data['recipient_name'] ?? 'Someone');
                            return "{$recipientName} accepted your ticket share for '{$eventTitle}'.";
                            
                        case 'share_declined':
                            $recipientName = $ticketShare->recipient?->full_name ?? ($data['recipient_name'] ?? 'Someone');
                            return "{$recipientName} declined your ticket share for '{$eventTitle}'.";
                            
                        default:
                            return 'Ticket share notification';
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate ticket share notification message', [
                        'notification_id' => $this->id,
                        'action' => $this->action,
                        'reference_id' => $this->reference_id,
                        'error' => $e->getMessage()
                    ]);
                    return 'Ticket share notification';
                }
            }
            
            return 'Ticket update';
        }
        
        // Handle event notifications
        if ($this->type === 'event') {
            $event = Event::find($this->reference_id);
            $eventTitle = $event?->title ?? 'Untitled Event';
            
            switch ($this->action) {
                case 'accept':
                    return "Your event '{$eventTitle}' was approved and published.";
                case 'reject':
                    return "Your event '{$eventTitle}' was rejected. Please review the feedback and make necessary changes.";
                case 'news':
                    // For news notifications, use custom message if available
                    return $this->attributes['message'] ?? "New news from '{$eventTitle}'";
                case 'event_ended':
                    return "The event '{$eventTitle}' has ended. Thank you for attending!";
                case 'feedback_request':
                    return "Please share your feedback about '{$eventTitle}'. Your input helps us improve future events.";
                case 'staff_added':
                    return "You have been added as staff for the event '{$eventTitle}'. You can now help manage this event.";
                case 'attended':
                    // Use stored message from database if available, otherwise generate one
                    return $this->attributes['message'] ?? "You have been checked in to '{$eventTitle}'. Thank you for attending!";
                case 'payout_processed':
                    // Use stored message from database if available, otherwise generate one
                    return $this->attributes['message'] ?? "Your payout for '{$eventTitle}' has been processed.";
                default:
                    return "Event '{$eventTitle}' update";
            }
        }
        
        // Handle support notifications
        if ($this->type === 'support') {
            $supportTicket = SupportTicket::find($this->reference_id);
            $ticketNumber = $supportTicket?->ticket_number ? ('#' . $supportTicket->ticket_number) : '#' . (string) $this->reference_id;
            
            switch ($this->action) {
                case 'admin_replied':
                    return "An admin has replied to your support ticket {$ticketNumber}.";
                case 'user_replied':
                    return "You have replied to support ticket {$ticketNumber}.";
                default:
                    return "Support ticket {$ticketNumber} update";
            }
        }
        
        return ucfirst((string) $this->type);
    }
}


