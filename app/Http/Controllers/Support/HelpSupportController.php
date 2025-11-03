<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HelpSupportController extends Controller
{
    /**
     * Display the help and support page.
     */
    public function index()
    {
        return view('help-support');
    }

    /**
     * Display the tutorials page.
     */
    public function tutorials()
    {
        return view('tutorials.index');
    }

    /**
     * Store a new support ticket.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create the support ticket
        $ticket = SupportTicket::create([
            'user_id' => Auth::id(), // Will be null for guest users
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
            'priority' => $this->determinePriority($request->subject, $request->message),
        ]);

        // Create the initial reply (user's message)
        SupportReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_admin_reply' => false,
        ]);

        // Create admin notification for new support ticket
        try {
            $userName = $request->name;
            $userEmail = $request->email;
            
            // Try to get user if authenticated
            if (Auth::check()) {
                $user = Auth::user();
                $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
                $userEmail = $user->email;
            }
            
            $notificationMessage = "{$userName} submitted a new support ticket: {$request->subject}";
            
            $notification = \App\Models\Notification::create([
                'user_id' => null, // Admin notification
                'admin_id' => null, // For all admins
                'type' => 'support',
                'action' => 'reply', // Using existing action for new ticket
                'reference_id' => $ticket->id,
                'title' => 'New Support Ticket',
                'message' => $notificationMessage,
                'data' => [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'user_id' => Auth::id(),
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'subject' => $request->subject,
                    'priority' => $ticket->priority,
                ],
                'is_read' => false,
            ]);

            // Broadcast admin notification with notification ID
            try {
                event(new \App\Events\AdminNotification(
                    $notificationMessage,
                    'info',
                    'New Support Ticket',
                    $notification->id
                ));
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast admin notification for support ticket: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create admin notification for support ticket: ' . $e->getMessage());
        }

        return redirect()->route('help.index')
            ->with('success', "Support ticket created. Ticket #{$ticket->ticket_number}.");
    }

    /**
     * Determine priority based on subject and message content.
     */
    private function determinePriority(string $subject, string $message): string
    {
        $urgentKeywords = ['urgent', 'critical', 'emergency', 'down', 'broken', 'not working'];
        $highKeywords = ['issue', 'problem', 'error', 'bug', 'help'];
        
        $content = strtolower($subject . ' ' . $message);
        
        foreach ($urgentKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                return 'urgent';
            }
        }
        
        foreach ($highKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                return 'high';
            }
        }
        
        return 'medium';
    }
}