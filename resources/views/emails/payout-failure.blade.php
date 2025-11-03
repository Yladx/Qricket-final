@extends('emails.layouts.base')

@section('title', 'Payout Failed - ' . $event->title)
@php
    $heading = 'Payout Processing Failed';
    $subheading = 'We\'re sorry to inform you that your payout for the event "' . $event->title . '" could not be processed.';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="text-align: center; margin-bottom: 25px;">
            <h2 style="color: var(--teal-dark); font-size: 22px; margin-bottom: 10px;">Hello {{ $organizer->name }}!</h2>
            <p style="color: var(--muted); font-size: 16px;">We're sorry to inform you that your payout for the event <strong>"{{ $event->title }}"</strong> could not be processed.</p>
            
            <div style="font-size: 32px; font-weight: 700; color: #dc3545; margin: 20px 0; text-align: center;">
                ₱{{ number_format($payout->net_amount, 2) }}
            </div>
            
            <p style="color: var(--muted); font-size: 15px;">Don't worry - your funds are safe and we're working to resolve this issue.</p>
        </td>
    </tr>
    
    <tr>
        <td style="background: #f8f9fa; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #dc3545;">
            <h3 style="color: var(--teal-dark); font-size: 18px; margin-bottom: 15px; font-weight: 600;">Payout Details</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: var(--muted); font-size: 14px;">Event</td>
                                <td style="color: var(--teal-dark); font-weight: 500;">{{ $event->title }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: var(--muted); font-size: 14px;">Event Date</td>
                                <td style="color: var(--teal-dark); font-weight: 500;">{{ $event->start_date ? $event->start_date->format('M d, Y') : 'TBD' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: var(--muted); font-size: 14px;">Gross Amount</td>
                                <td style="color: var(--teal-dark); font-weight: 500;">₱{{ number_format($payout->total_earnings, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: var(--muted); font-size: 14px;">Qricket Fee</td>
                                <td style="color: var(--teal-dark); font-weight: 500;">₱{{ number_format($payout->qricket_fee, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: var(--muted); font-size: 14px;">Net Amount</td>
                                <td style="font-weight: 700; color: #dc3545;">₱{{ number_format($payout->net_amount, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: var(--muted); font-size: 14px;">Request Date</td>
                                <td style="color: var(--teal-dark); font-weight: 500;">{{ $payout->created_at ? $payout->created_at->format('M d, Y g:i A') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: var(--muted); font-size: 14px;">Status</td>
                                <td style="color: var(--teal-dark); font-weight: 500;">
                                    <span style="background: #dc3545; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">❌ Failed</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    
    <tr>
        <td style="background: #f8d7da; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #dc3545;">
            <h4 style="margin-top: 0; color: #721c24; font-size: 16px; font-weight: 600;">🚨 Failure Reason</h4>
            <p style="margin: 10px 0; color: #721c24;"><strong>{{ $failureReason }}</strong></p>
            <p style="margin: 10px 0; color: #721c24;">This could be due to incorrect bank account details, network issues, or bank processing delays.</p>
        </td>
    </tr>
    
    <tr>
        <td style="text-align: center;">
            <a href="{{ url('/organizer/events') }}" style="display: inline-block; background: #dc3545; color: white; padding: 14px 28px; text-decoration: none; border-radius: 6px; margin: 20px 0; font-weight: 600; font-size: 16px;">
                Update Payment Details
            </a>
        </td>
    </tr>
    
    <tr>
        <td style="background: #e8f5e8; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid var(--teal);">
            <h4 style="margin-top: 0; color: var(--teal); font-size: 16px; font-weight: 600;">🛠️ What You Can Do</h4>
            <p style="margin: 10px 0; color: #495057;"><strong>1. Verify Your Bank Details:</strong> Ensure your account number and bank information are correct.</p>
            <p style="margin: 10px 0; color: #495057;"><strong>2. Contact Support:</strong> Our team is ready to help you resolve this issue quickly.</p>
            <p style="margin: 10px 0; color: #495057;"><strong>3. Retry Payout:</strong> Once details are verified, you can request a new payout.</p>
            <p style="margin: 10px 0; color: #495057;"><strong>4. Alternative Methods:</strong> We can explore other payment methods if needed.</p>
        </td>
    </tr>
    
    <tr>
        <td style="text-align: center; margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 6px;">
            <p style="color: var(--muted); font-size: 16px; margin-bottom: 8px;">We apologize for any inconvenience caused.</p>
            <p style="color: var(--muted); font-size: 14px;">Our support team is available 24/7 to assist you with this matter.</p>
        </td>
    </tr>
</table>
@endsection