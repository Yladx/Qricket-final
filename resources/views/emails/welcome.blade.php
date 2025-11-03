@extends('emails.layouts.base')

@section('title', 'Welcome to Qricket!')
@php
    $heading = 'Welcome to Qricket!';
    $subheading = 'Your journey to amazing events starts here';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="stack" valign="top" style="padding-bottom:16px;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
            <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Hello {{ $user->name }}!</p>
            <p style="margin:0; color:var(--muted); font-size:14px;">Welcome to <strong>Qricket</strong> - the ultimate platform for creating, managing, and promoting your events!</p>
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
            <h3 style="margin:0 0 12px 0; font-size:16px; color:var(--teal-dark); font-weight:600;">🚀 What you can do with Qricket:</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">📅</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Create Events</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">Design and customize your events with our easy-to-use tools</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">🎫</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Sell Tickets</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">Set up ticket sales with multiple pricing tiers and options</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">📊</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Track Analytics</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">Monitor your event performance with detailed insights</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;">
                  <span style="font-size:16px; margin-right:8px;">💰</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Manage Payouts</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">Get paid quickly and securely for your successful events</p>
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
      <a href="{{ url('/organizer/events') }}" style="display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">Create Your First Event</a>
    </td>
  </tr>
  <tr>
    <td style="padding-top:20px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="50%" valign="top" style="padding-right:8px;">
            <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
              <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">💡 Getting Started</p>
              <p style="margin:0; color:var(--muted); font-size:13px;">Complete your profile setup for better event visibility.</p>
            </div>
          </td>
          <td width="50%" valign="top" style="padding-left:8px;">
            <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
              <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">📞 Support</p>
              <p style="margin:0; color:var(--muted); font-size:13px;">Our support team is here to help you succeed.</p>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
@endsection