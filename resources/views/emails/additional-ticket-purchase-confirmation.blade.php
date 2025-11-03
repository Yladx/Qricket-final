@extends('emails.layouts.base')

@section('content')
<div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; background-color: #ffffff;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
        <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">Additional Ticket Purchase Confirmation</h1>
        <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px;">Thank you for your purchase!</p>
    </div>

    <!-- Content -->
    <div style="padding: 40px 30px;">
        <div style="margin-bottom: 30px;">
            <h2 style="color: #333333; font-size: 24px; margin: 0 0 20px 0;">Hello {{ $user->first_name }}!</h2>
            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                Your additional ticket purchase has been confirmed. Here are the details of your purchase:
            </p>
        </div>

        <!-- Purchase Details -->
        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #333333; font-size: 20px; margin: 0 0 20px 0; border-bottom: 2px solid #667eea; padding-bottom: 10px;">Purchase Details</h3>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Purchase ID:</span>
                <span style="color: #333333;">{{ $purchase->purchase_id }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Event:</span>
                <span style="color: #333333;">{{ $event->title }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Plan:</span>
                <span style="color: #333333;">{{ $pricing->name ?? 'Additional Tickets' }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Additional Tickets:</span>
                <span style="color: #333333;">{{ number_format($purchase->additional_tickets) }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Amount:</span>
                <span style="color: #333333; font-weight: bold;">{{ $purchase->currency }} {{ number_format($purchase->amount, 2) }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Status:</span>
                <span style="color: #28a745; font-weight: bold; text-transform: uppercase;">{{ $purchase->status }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #666666; font-weight: bold;">Purchase Date:</span>
                <span style="color: #333333;">{{ $purchase->created_at->format('F j, Y \a\t g:i A') }}</span>
            </div>
        </div>

        <!-- Event Information -->
        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #333333; font-size: 20px; margin: 0 0 20px 0; border-bottom: 2px solid #667eea; padding-bottom: 10px;">Event Information</h3>
            
            <div style="margin-bottom: 15px;">
                <strong style="color: #333333;">Event Title:</strong>
                <p style="color: #666666; margin: 5px 0 0 0; font-size: 16px;">{{ $event->title }}</p>
            </div>
            
            <div style="margin-bottom: 15px;">
                <strong style="color: #333333;">Event Date:</strong>
                <p style="color: #666666; margin: 5px 0 0 0; font-size: 16px;">{{ $event->start_date->format('F j, Y \a\t g:i A') }}</p>
            </div>
            
            <div style="margin-bottom: 15px;">
                <strong style="color: #333333;">Location:</strong>
                <p style="color: #666666; margin: 5px 0 0 0; font-size: 16px;">{{ $event->location }}</p>
            </div>
            
            @if($event->description)
            <div>
                <strong style="color: #333333;">Description:</strong>
                <p style="color: #666666; margin: 5px 0 0 0; font-size: 16px; line-height: 1.6;">{{ Str::limit($event->description, 200) }}</p>
            </div>
            @endif
        </div>

        <!-- Next Steps -->
        <div style="background-color: #e3f2fd; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #1976d2; font-size: 20px; margin: 0 0 15px 0;">What's Next?</h3>
            <ul style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 10px;">Your additional tickets have been added to your account</li>
                <li style="margin-bottom: 10px;">You can now create more events with the increased ticket limit</li>
                <li style="margin-bottom: 10px;">Check your organizer dashboard to see your updated ticket limits</li>
                <li>Contact support if you have any questions about your purchase</li>
            </ul>
        </div>

        <!-- Action Button -->
        <div style="text-align: center; margin-bottom: 30px;">
            <a href="{{ route('organizer.dashboard') }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: bold; font-size: 16px; display: inline-block;">
                Go to Dashboard
            </a>
        </div>

        <!-- Support Information -->
        <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <p style="color: #666666; font-size: 14px; margin: 0 0 10px 0;">
                Need help? Contact our support team at 
                <a href="mailto:support@qricket.com" style="color: #667eea; text-decoration: none;">support@qricket.com</a>
            </p>
            <p style="color: #999999; font-size: 12px; margin: 0;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</div>
@endsection
