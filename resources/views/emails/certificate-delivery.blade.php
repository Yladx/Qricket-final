@extends('emails.layouts.base')

@section('title', 'Your Certificate is Ready!')
@php
    $heading = 'Your Certificate is Ready!';
    $subheading = 'Congratulations on completing ' . $event->title . '. Your certificate is now available for download.';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="stack" valign="top" style="padding-bottom:16px;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
            <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Hello {{ $participant->first_name ?? 'there' }}!</p>
            <p style="margin:0; color:var(--muted); font-size:14px;">Congratulations! We're excited to inform you that your certificate for the event you attended is now ready for download.</p>
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
            <h3 style="margin:0 0 12px 0; font-size:16px; color:var(--teal-dark); font-weight:600;">🎉 Event Details</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">📅</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">{{ $event->title }}</span>
                </td>
              </tr>
              @if($event->start_date)
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">🕐</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Event Date</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">{{ \Carbon\Carbon::parse($event->start_date)->format('F j, Y \a\t g:i A') }}</p>
                </td>
              </tr>
              @endif
              @if($event->end_date)
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid rgba(13,148,136,0.1);">
                  <span style="font-size:16px; margin-right:8px;">🏁</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">End Date</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">{{ \Carbon\Carbon::parse($event->end_date)->format('F j, Y \a\t g:i A') }}</p>
                </td>
              </tr>
              @endif
              @if($event->location)
              <tr>
                <td style="padding:8px 0;">
                  <span style="font-size:16px; margin-right:8px;">📍</span>
                  <span style="font-weight:600; color:var(--teal-dark); font-size:14px;">Location</span>
                  <p style="margin:4px 0 0 0; color:var(--muted); font-size:13px;">{{ $event->location }}</p>
                </td>
              </tr>
              @endif
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="padding-top:16px;">
      <p style="margin:0 0 16px 0; color:#374151; font-size:15px; line-height:1.6;">Your participation and engagement in this event have been recognized, and we're pleased to provide you with this certificate as a token of your achievement.</p>
    </td>
  </tr>
  @if($certificateUrl)
  <tr>
    <td align="center" style="padding:20px 0;">
      <a href="{{ $certificateUrl }}" style="display:inline-block; text-decoration:none; padding:14px 28px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">📄 View Your Certificate</a>
    </td>
  </tr>
  <tr>
    <td style="text-align:center; padding-bottom:16px;">
      <p style="margin:0; color:var(--muted); font-size:14px;">Click the button above to view and print your certificate</p>
    </td>
  </tr>
  @endif
  <tr>
    <td style="padding:16px; border-radius:8px; background-color:#fef3c7; border:1px solid #f59e0b; color:#92400e; margin:16px 0;">
      <p style="margin:0; font-size:14px; line-height:1.5;"><strong>Important:</strong> This certificate link is unique to you and should be kept secure. You can view and print your certificate at any time using this link. To print, simply press <strong>Ctrl+P</strong> (or <strong>Cmd+P</strong> on Mac) or click the print button when viewing your certificate.</p>
    </td>
  </tr>
  @if($certificateUrl)
  <tr>
    <td style="padding-top:16px;">
      <p style="margin:0 0 12px 0; color:#6b7280; font-size:14px;">If the button above doesn't work, you can copy and paste this link into your browser:</p>
      <div style="background-color:#f9fafb; padding:12px; border-radius:6px; word-break:break-all; font-family:monospace; font-size:13px; color:#6b7280;">
        {{ $certificateUrl }}
      </div>
    </td>
  </tr>
  @endif
  <tr>
    <td style="padding-top:20px;">
      <p style="margin:0 0 8px 0; color:#374151; font-size:15px; line-height:1.6;">Thank you for participating in our event. We hope you had a great experience and look forward to seeing you at future events!</p>
      <p style="margin:0; color:#374151; font-size:15px; line-height:1.6;">Best regards,<br><strong>The Qricket Team</strong></p>
    </td>
  </tr>
</table>
@endsection