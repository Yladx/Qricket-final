@extends('emails.layouts.base')

@section('title', 'We\'d love your feedback - ' . $eventTitle)
@php
    $heading = 'We\'d love your feedback';
    $subheading = 'Help us improve Qricket and future events';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="stack" valign="top" style="padding-bottom:16px;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
            <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Hi {{ $userName }},</p>
            <p style="margin:0; color:var(--muted); font-size:14px;">Thanks for joining <strong>{{ $eventTitle }}</strong>. Your feedback helps us improve Qricket and future events. This will only take a minute.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="padding-top:12px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:16px; border-radius:8px; background-color:#0b1324; color:#e5e7eb;">
            <div style="background-image:url('{{ $eventThumbnail }}'); background-size:cover; background-position:center; height:140px; border-radius:6px; margin-bottom:12px;"></div>
            <div style="font-size:14px; font-weight:700; color:#ffffff; margin-bottom:4px;">{{ $eventTitle }}</div>
            <div style="color:#9ca3af; font-size:12px;">{{ $eventWhen }} @if($eventLocation) • {{ $eventLocation }} @endif</div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding-top:20px;">
      <a href="{{ $feedbackUrl }}" style="display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">Give Feedback</a>
    </td>
  </tr>
  <tr>
    <td style="padding-top:20px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
            <p style="margin:0; color:var(--muted); font-size:13px;">If the button doesn't work, copy and paste this link into your browser:</p>
            <p style="word-break: break-all; font-size:12px; margin:6px 0 0 0;">
              <a href="{{ $feedbackUrl }}" style="color:var(--teal); text-decoration:underline;">{{ $feedbackUrl }}</a>
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
@endsection