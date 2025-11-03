@extends('emails.layouts.base')

@section('title', 'Ticket Purchase Confirmation - ' . $event->title)
@php
    $heading = 'Purchase Confirmed!';
    $subheading = 'Your tickets for ' . $event->title . ' are ready';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="stack" valign="top" style="padding-bottom:16px;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
            <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Hello {{ $customer->name ?? $user->name }}!</p>
            <p style="margin:0; color:var(--muted); font-size:14px;">Thank you for your purchase! Your tickets for <strong>"{{ $event->title }}"</strong> are confirmed and ready.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="padding-top:12px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:16px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
            <h3 style="margin:0 0 12px 0; font-size:16px; color:var(--teal-dark); font-weight:600;">Event Details</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:6px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Event:</span>
                  <span style="color:var(--muted); font-size:13px; margin-left:8px;">{{ $event->title }}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Date:</span>
                  <span style="color:var(--muted); font-size:13px; margin-left:8px;">{{ $event->start_date ? $event->start_date->format('M d, Y g:i A') : 'TBD' }}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Location:</span>
                  <span style="color:var(--muted); font-size:13px; margin-left:8px;">{{ $event->location ?? 'TBD' }}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Ticket Type:</span>
                  <span style="color:var(--muted); font-size:13px; margin-left:8px;">General Admission</span>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0;">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Price:</span>
                  <span style="color:var(--muted); font-size:13px; margin-left:8px; font-weight:700; color:#28a745;">₱299.00</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding-top:20px;">
      <a href="{{ url('/events') }}" style="display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">View Event Details</a>
    </td>
  </tr>
  <tr>
    <td style="padding-top:20px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="50%" valign="top" style="padding-right:8px;">
            <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
              <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">QR Code</p>
              <p style="margin:0; color:var(--muted); font-size:13px;">Your tickets include QR codes for easy check-in.</p>
            </div>
          </td>
          <td width="50%" valign="top" style="padding-left:8px;">
            <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
              <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">Event Updates</p>
              <p style="margin:0; color:var(--muted); font-size:13px;">You'll receive updates about this event.</p>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
@endsection