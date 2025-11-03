@extends('emails.layouts.base')

@section('title', 'Payment Confirmation - ' . $planDetails['name'])
@php
    $heading = 'Payment Confirmed!';
    $subheading = 'Your subscription is now active';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="stack" valign="top" style="padding-bottom:16px;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
            <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Hello {{ $user->name }}!</p>
            <p style="margin:0; color:var(--muted); font-size:14px;">Your payment has been processed successfully. Your <strong>{{ $planDetails['name'] }}</strong> subscription is now active!</p>
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
            <h3 style="margin:0 0 12px 0; font-size:16px; color:var(--teal-dark); font-weight:600;">Payment Details</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:6px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Plan:</span>
                  <span style="color:var(--muted); font-size:13px; margin-left:8px;">{{ $planDetails['name'] }}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Amount:</span>
                  <span style="color:var(--muted); font-size:13px; margin-left:8px;">{{ $planDetails['price'] }}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Transaction ID:</span>
                  <span style="color:var(--muted); font-size:13px; margin-left:8px;">{{ $transactionId ?? 'N/A' }}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0;">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Status:</span>
                  <span style="color:#28a745; font-size:13px; margin-left:8px; font-weight:600;">Completed</span>
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
      @if($isTicketPurchase)
        <a href="{{ route('dashboard') }}" style="display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">View Dashboard</a>
      @else
        <a href="{{ route('subscription.current') }}" style="display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">Manage Subscription</a>
      @endif
    </td>
  </tr>
  <tr>
    <td style="padding-top:20px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="50%" valign="top" style="padding-right:8px;">
            <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
              <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">🎉 Welcome!</p>
              <p style="margin:0; color:var(--muted); font-size:13px;">You now have access to all premium features.</p>
            </div>
          </td>
          <td width="50%" valign="top" style="padding-left:8px;">
            <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
              <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">📧 Support</p>
              <p style="margin:0; color:var(--muted); font-size:13px;">Need help? Contact our support team.</p>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
@endsection