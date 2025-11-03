@extends('emails.layouts.base')

@section('title', 'Your Certificate is Ready')
@php
    $heading = 'Your certificate is ready';
    $subheading = 'Congratulations on joining ' . $eventTitle . '. You can now view and download your certificate.';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="stack" valign="top" style="padding-bottom:16px;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
            <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Hi {{ $userName }}!</p>
            <p style="margin:0; color:var(--muted); font-size:14px;">Congratulations on joining <strong>{{ $eventTitle }}</strong>. You can now view and download your certificate.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="padding-top:12px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:16px; border-radius:8px; background-color:#0b1324; color:#e5e7eb; overflow:hidden;">
            <div style="background-image:url('{{ $eventThumbnail }}'); background-size:cover; background-position:center; height:140px; margin:-16px -16px 16px -16px;"></div>
            <div style="padding:0 0 0 0;">
              <div style="font-size:14px; font-weight:700; color:#ffffff; margin-bottom:4px;">{{ $eventTitle }}</div>
              <div style="color:#9ca3af; font-size:12px;">{{ $eventWhen }} @if($eventLocation) • {{ $eventLocation }} @endif</div>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="padding-top:16px;">
      <p style="margin:0 0 18px 0; color:#374151; font-size:15px; line-height:1.6;">Access your certificate:</p>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:0 0 18px 0;">
      <a href="{{ $certificateUrl }}" style="display:inline-block; text-decoration:none; padding:12px 18px; border-radius:9999px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">View Certificate</a>
    </td>
  </tr>
  <tr>
    <td>
      <p style="margin:0 0 12px 0; color:var(--muted); font-size:14px;">If the button doesn't work, copy and paste this link into your browser:</p>
      <div style="background-color:#f9fafb; padding:12px; border-radius:6px; word-break:break-all; font-family:monospace; font-size:13px; color:#6b7280;">
        <a href="{{ $certificateUrl }}" style="color:var(--teal); text-decoration:underline;">{{ $certificateUrl }}</a>
      </div>
    </td>
  </tr>
</table>
@endsection