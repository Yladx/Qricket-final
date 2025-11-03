@extends('emails.layouts.base')

@section('title', 'Payout Processed - ' . $event->title)
@php
    $heading = 'Payout Processed Successfully';
    $subheading = 'Great news! We\'ve successfully processed your payout for the event "' . $event->title . '".';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="text-align: center; margin-bottom: 25px;">
            <h2 style="color: var(--teal-dark); font-size: 22px; margin-bottom: 10px;">Hello {{ $organizer->name }}!</h2>
            <p style="color: var(--muted); font-size: 16px;">Great news! We've successfully processed your payout for the event <strong>"{{ $event->title }}"</strong>.</p>
            
            <div style="font-size: 32px; font-weight: 700; color: var(--teal); margin: 20px 0; text-align: center;">
                ₱{{ number_format($payout->net_amount, 2) }}
            </div>
            
            <p style="color: var(--muted); font-size: 15px;">Your payout has been sent to your registered bank account.</p>
        </td>
    </tr>
    
    <tr>
        <td style="background: #f8f9fa; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 4px solid var(--teal);">
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
                                <td style="font-weight: 700; color: var(--teal);">₱{{ number_format($payout->net_amount, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: var(--muted); font-size: 14px;">Payout Date</td>
                                <td style="color: var(--teal-dark); font-weight: 500;">{{ $payout->processed_at ? $payout->processed_at->format('M d, Y g:i A') : 'Processing...' }}</td>
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
                                    <span style="background: var(--teal); color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Paid</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    
    <tr>
        <td style="text-align: center;">
            <a href="{{ url('/organizer/events') }}" style="display: inline-block; background: var(--teal); color: white; padding: 14px 28px; text-decoration: none; border-radius: 6px; margin: 20px 0; font-weight: 600; font-size: 16px;">
                View My Events
            </a>
        </td>
    </tr>
    
    <tr>
        <td style="background: #e8f5e8; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid var(--teal);">
            <h4 style="margin-top: 0; color: var(--teal); font-size: 16px; font-weight: 600;">Important Notes</h4>
            <ul style="margin: 10px 0; padding-left: 20px; color: #495057;">
                <li style="margin: 6px 0;">Bank processing times may vary (usually 1-3 business days)</li>
                <li style="margin: 6px 0;">Check your bank account for the deposit</li>
                <li style="margin: 6px 0;">Contact your bank if you don't see the deposit within 3 business days</li>
                <li style="margin: 6px 0;">Keep this email as your payout receipt</li>
            </ul>
        </td>
    </tr>
    
    <tr>
        <td style="text-align: center; margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 6px;">
            <p style="color: var(--muted); font-size: 16px; margin-bottom: 8px;">Thank you for using Qricket!</p>
            <p style="color: var(--muted); font-size: 14px;">If you have any questions, please contact our support team.</p>
        </td>
    </tr>
</table>
@endsection