@extends('emails.layouts.base')

@section('title', $pending ? 'Payment Invoice' : 'Invoice Receipt')
@php
    $heading = $pending ? 'Payment Invoice' : 'Invoice Receipt';
    $subheading = $pending ? 'Thank you for your order. Please complete your payment using the secure link below.' : 'Thanks for your purchase. Your invoice is ready.';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="stack" valign="top" style="padding-bottom:16px;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
            <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Hi {{ $purchase->customer_details['name'] ?? ($purchase->user->first_name ?? 'there') }}!</p>
            <p style="margin:0; color:var(--muted); font-size:14px;">{{ $pending ? 'Thank you for your order. Please complete your payment using the secure link below.' : 'Thanks for your purchase. Your invoice is ready.' }}</p>
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
            <h3 style="margin:0 0 12px 0; font-size:16px; color:var(--teal-dark); font-weight:600;">🎫 Purchase Details</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">🆔</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Purchase ID</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">{{ $purchase->purchase_id }}</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">💰</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Amount</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">₱{{ number_format($purchase->total_amount, 2) }}</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">{{ $pending ? '⏳' : '✅' }}</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Status</span>
                  <p style="margin:4px 0 0 0; color:{{ $pending ? '#f59e0b' : '#10b981' }}; font-size:13px; font-weight:600;">{{ strtoupper($purchase->status) }}</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">🎪</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Event</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">{{ $purchase->event->title ?? 'Event' }}</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;">
                  <span style="font-size:16px; margin-right:8px;">📅</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Date</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">{{ now()->format('M d, Y g:i A') }}</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
                @if($invoiceUrl)
  <tr>
    <td align="center" style="padding:20px 0;">
      <a href="{{ $invoiceUrl }}" style="display:inline-block; text-decoration:none; padding:14px 28px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">{{ $pending ? '💳 Pay Invoice' : '📄 View Invoice' }}</a>
    </td>
  </tr>
  <tr>
    <td style="text-align:center; padding-bottom:16px;">
      <p style="margin:0; color:var(--muted); font-size:14px;">Click the button above to {{ $pending ? 'complete your payment' : 'view your invoice' }}</p>
    </td>
  </tr>
                @endif
  <tr>
    <td style="padding-top:16px;">
      <p style="margin:0 0 12px 0; color:#6b7280; font-size:14px;">If the button doesn't work, copy this link into your browser:</p>
                @if($invoiceUrl)
      <div style="background-color:#f9fafb; padding:12px; border-radius:6px; word-break:break-all; font-family:monospace; font-size:13px; color:#6b7280;">
        <a href="{{ $invoiceUrl }}" style="color:var(--teal); text-decoration:underline;">{{ $invoiceUrl }}</a>
            </div>
            @endif
    </td>
  </tr>
  <tr>
    <td style="padding-top:20px;">
      <p style="margin:0; color:#6b7280; font-size:14px;">This email was sent by Qricket.</p>
    </td>
  </tr>
</table>
@endsection