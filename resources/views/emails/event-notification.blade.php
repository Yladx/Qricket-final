@extends('emails.layouts.base')

@section('title', 'Event Update - ' . $event->title)
@php
    $heading = 'Event Update';
    $subheading = 'We have an important update about the event "' . $event->title . '" organized by ' . $organizer->name . '.';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="text-align: center; margin-bottom: 30px;">
            <h2 style="color: var(--teal-dark); font-size: 24px; margin-bottom: 15px;">Event Update Notification</h2>
            <p style="color: var(--muted); font-size: 16px; line-height: 1.6;">We have an important update about the event <strong style="color: #495057;">"{{ $event->title }}"</strong> organized by {{ $organizer->name }}.</p>
        </td>
    </tr>
    
    <tr>
        <td style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 30px; border-radius: 12px; margin: 30px 0; border-left: 5px solid var(--teal); box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3 style="color: var(--teal-dark); font-size: 20px; margin-bottom: 20px; font-weight: 600;">Event Information</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: #495057; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Event Title</td>
                                <td style="color: var(--teal-dark); font-weight: 500; font-size: 15px;">{{ $event->title }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: #495057; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Organizer</td>
                                <td style="color: var(--teal-dark); font-weight: 500; font-size: 15px;">{{ $organizer->name }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: #495057; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Event Date</td>
                                <td style="color: var(--teal-dark); font-weight: 500; font-size: 15px;">{{ $event->start_date ? $event->start_date->format('M d, Y g:i A') : 'TBD' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: #495057; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Location</td>
                                <td style="color: var(--teal-dark); font-weight: 500; font-size: 15px;">{{ $event->location ?? 'TBD' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-weight: 600; color: #495057; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Status</td>
                                <td style="color: var(--teal-dark); font-weight: 500; font-size: 15px;">
                                    <span style="background: linear-gradient(135deg, var(--teal) 0%, var(--teal-light) 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">{{ ucfirst($event->status ?? 'Active') }}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    
    <tr>
        <td style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #2196f3;">
            <h4 style="margin-top: 0; color: #1976d2; font-size: 16px; font-weight: 600;">📢 Update Details</h4>
            <p style="margin: 12px 0; color: #424242;">This is a test notification email to demonstrate the event notification system. In a real scenario, this would contain specific updates about the event such as:</p>
            <ul style="margin: 12px 0; padding-left: 20px; color: #424242;">
                <li style="margin: 8px 0;">Schedule changes</li>
                <li style="margin: 8px 0;">Venue updates</li>
                <li style="margin: 8px 0;">Additional ticket releases</li>
                <li style="margin: 8px 0;">Special announcements</li>
                <li style="margin: 8px 0;">Weather updates (for outdoor events)</li>
            </ul>
        </td>
    </tr>
    
    <tr>
        <td style="text-align: center;">
            <a href="{{ url('/events') }}" style="display: inline-block; background: linear-gradient(135deg, var(--teal) 0%, var(--teal-light) 100%); color: white; padding: 16px 32px; text-decoration: none; border-radius: 8px; margin: 25px 0; font-weight: 600; font-size: 16px; box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);">
                View Event Details
            </a>
        </td>
    </tr>
    
    <tr>
        <td style="text-align: center; margin: 40px 0; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
            <p style="color: var(--muted); font-size: 16px; margin-bottom: 10px;">Stay updated with the latest information about this event!</p>
            <p style="color: var(--muted); font-size: 14px;">Thank you for your interest in Qricket events.</p>
        </td>
    </tr>
</table>
@endsection