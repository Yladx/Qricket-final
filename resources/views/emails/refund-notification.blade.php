@extends('emails.layouts.base')

@section('content')
<div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; background-color: #ffffff;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 30px; text-align: center;">
        <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">Refund Processed</h1>
        <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px;">Your refund has been successfully processed</p>
    </div>

    <!-- Content -->
    <div style="padding: 40px 30px;">
        <div style="margin-bottom: 30px;">
            <h2 style="color: #333333; font-size: 24px; margin: 0 0 20px 0;">Hello {{ $user->first_name }}!</h2>
            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                We're writing to inform you that your refund has been successfully processed. Here are the details:
            </p>
        </div>

        <!-- Refund Details -->
        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #333333; font-size: 20px; margin: 0 0 20px 0; border-bottom: 2px solid #28a745; padding-bottom: 10px;">Refund Details</h3>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Refund ID:</span>
                <span style="color: #333333;">{{ $refundData['id'] ?? 'N/A' }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Refund Type:</span>
                <span style="color: #333333;">{{ ucfirst($refundType) }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Amount:</span>
                <span style="color: #28a745; font-weight: bold;">{{ $refundData['currency'] ?? 'USD' }} {{ number_format($refundData['amount'] ?? 0, 2) }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Status:</span>
                <span style="color: #28a745; font-weight: bold; text-transform: uppercase;">{{ $refundData['status'] ?? 'COMPLETED' }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: #666666; font-weight: bold;">Processed Date:</span>
                <span style="color: #333333;">{{ isset($refundData['created']) ? \Carbon\Carbon::parse($refundData['created'])->format('F j, Y \a\t g:i A') : now()->format('F j, Y \a\t g:i A') }}</span>
            </div>
            
            @if(isset($refundData['reason']))
            <div style="margin-top: 15px;">
                <span style="color: #666666; font-weight: bold;">Reason:</span>
                <p style="color: #333333; margin: 5px 0 0 0; font-size: 16px;">{{ $refundData['reason'] }}</p>
            </div>
            @endif
        </div>

        <!-- Processing Information -->
        <div style="background-color: #e8f5e8; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #28a745; font-size: 20px; margin: 0 0 15px 0;">Processing Information</h3>
            <ul style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 10px;">The refund has been processed to your original payment method</li>
                <li style="margin-bottom: 10px;">It may take 3-5 business days to appear in your account</li>
                <li style="margin-bottom: 10px;">You will receive a separate notification from your payment provider</li>
                <li>Contact support if you don't see the refund within 5 business days</li>
            </ul>
        </div>

        <!-- Next Steps -->
        <div style="background-color: #e3f2fd; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #1976d2; font-size: 20px; margin: 0 0 15px 0;">What's Next?</h3>
            <ul style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 10px;">Check your bank account or credit card statement</li>
                <li style="margin-bottom: 10px;">The refund will appear as a credit to your account</li>
                <li style="margin-bottom: 10px;">Keep this email as proof of the refund transaction</li>
                <li>Contact us if you have any questions about this refund</li>
            </ul>
        </div>

        <!-- Action Button -->
        <div style="text-align: center; margin-bottom: 30px;">
            <a href="{{ route('dashboard') }}" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: bold; font-size: 16px; display: inline-block;">
                Go to Dashboard
            </a>
        </div>

        <!-- Support Information -->
        <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <p style="color: #666666; font-size: 14px; margin: 0 0 10px 0;">
                Need help? Contact our support team at 
                <a href="mailto:support@qricket.com" style="color: #28a745; text-decoration: none;">support@qricket.com</a>
            </p>
            <p style="color: #999999; font-size: 12px; margin: 0;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</div>
@endsection
