@extends('emails.layouts.base')

@section('title', 'Invoice - ' . config('app.name'))
@php
    $heading = $status === 'failed' ? 'Payment Failed - Invoice' : 'Invoice for Subscription';
    $subheading = $status === 'failed' ? 'We\'re sorry, but your payment could not be processed. Please review the details below and try again.' : 'Thank you for choosing ' . config('app.name') . '! Your subscription invoice is ready for payment.';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="stack" valign="top" style="padding-bottom:16px;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
            <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Hello there!</p>
            <p style="margin:0; color:var(--muted); font-size:14px;">{{ $status === 'failed' ? 'We\'re sorry, but your payment for ' . config('app.name') . ' could not be processed. Please review the details below and try again.' : 'Thank you for choosing ' . config('app.name') . '! Your subscription invoice is ready for payment.' }}</p>
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
            <h3 style="margin:0 0 12px 0; font-size:16px; color:var(--teal-dark); font-weight:600;">📋 Invoice Details</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
              @if($plan)
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">📦</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Plan</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">{{ $plan['name'] ?? 'Subscription Plan' }}</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">📝</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Description</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">{{ $plan['description'] ?? 'Premium subscription plan' }}</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">💰</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Price</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">₱{{ number_format($plan['price'] ?? 0, 2) }} {{ $plan['currency'] ?? 'PHP' }}</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">🔄</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Billing Period</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">Monthly</p>
                </td>
              </tr>
              @endif
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">📅</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Invoice Date</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">{{ now()->format('F j, Y') }}</p>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;">
                  <span style="font-size:16px; margin-right:8px;">{{ $status === 'failed' ? '❌' : '⏳' }}</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Status</span>
                  <p style="margin:4px 0 0 0; color:{{ $status === 'failed' ? '#ef4444' : '#f59e0b' }}; font-size:13px; font-weight:600;">{{ $status === 'failed' ? 'Payment Failed' : 'Pending Payment' }}</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:20px 0;">
      @if($status === 'failed')
        <a href="{{ $invoiceUrl }}" style="display:inline-block; text-decoration:none; padding:14px 28px; border-radius:8px; background-color:#ef4444; color:var(--white); font-weight:600; font-size:15px;">🔄 Retry Payment</a>
      @else
        <a href="{{ $invoiceUrl }}" style="display:inline-block; text-decoration:none; padding:14px 28px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">💳 Pay Now</a>
      @endif
    </td>
  </tr>
  <tr>
    <td style="padding:0 0 16px 0;">
      <p style="margin:0; color:var(--muted); font-size:14px; text-align:center;">{{ $status === 'failed' ? 'Click the "Retry Payment" button above to try your payment again. If you continue to experience issues, please contact our support team.' : 'Click the "Pay Now" button above to complete your subscription payment. You\'ll be redirected to our secure payment processor.' }}</p>
    </td>
  </tr>
  @if($plan && isset($plan['features']) && is_array($plan['features']))
  <tr>
    <td style="padding-top:16px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:16px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
            <h3 style="margin:0 0 12px 0; font-size:16px; color:var(--teal-dark); font-weight:600;">✨ Your {{ $plan['name'] }} subscription includes:</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
              @foreach($plan['features'] as $feature)
              <tr>
                <td style="padding:6px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px; color:var(--teal);">✓</span>
                  <span style="color:var(--muted); font-size:14px;">{{ $feature }}</span>
                </td>
              </tr>
              @endforeach
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  @endif
  <tr>
    <td style="padding:16px; border-radius:8px; background-color:#fef3c7; border:1px solid #f59e0b; color:#92400e; margin:16px 0;">
      <p style="margin:0; font-size:14px; line-height:1.5;"><strong>Security Note:</strong> This invoice link is secure and will expire after 24 hours for your protection. If you have any questions about this invoice, please contact our support team.</p>
    </td>
  </tr>
  <tr>
    <td style="padding-top:20px;">
      <p style="margin:0 0 8px 0; color:#374151; font-size:15px; line-height:1.6;">If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
      <p style="margin:0; color:#374151; font-size:15px; line-height:1.6;">Best regards,<br><strong>The {{ config('app.name') }} Team</strong></p>
    </td>
  </tr>
</table>
@endsection