@extends('emails.layouts.base')

@section('title', 'Event News - ' . $event->title)
@php
    $heading = 'Event News Update';
    $subheading = 'New updates for ' . $event->title;
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td class="stack" valign="top" style="padding-bottom:16px;">
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
            <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Hello {{ $user->name }}!</p>
            <p style="margin:0; color:var(--muted); font-size:14px;">We have exciting news about <strong>"{{ $event->title }}"</strong> that you have tickets for!</p>
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
            <h3 style="margin:0 0 12px 0; font-size:16px; color:var(--teal-dark); font-weight:600;">📢 Latest News</h3>
            <div style="padding:12px; background-color:#f8f9fa; border-radius:6px; margin-bottom:12px;">
              <h4 style="margin:0 0 8px 0; font-size:15px; color:var(--teal-dark); font-weight:600;">{{ $news->title }}</h4>
              <div style="margin:0; color:var(--muted); font-size:14px; line-height:1.5;">{!! nl2br(e($news->body)) !!}</div>
            </div>
            @if(!empty($news->attachments) && is_array($news->attachments))
              @foreach(array_slice($news->attachments, 0, 3) as $imageUrl)
              <div style="text-align:center; margin:12px 0;">
                <img src="{{ $imageUrl }}" alt="{{ $news->title }}" style="max-width:100%; height:auto; border-radius:6px;">
              </div>
              @endforeach
            @endif
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
                <td style="padding:6px 0;">
                  <span style="font-weight:600; color:var(--teal-dark); font-size:13px;">Location:</span>
                  <span style="color:var(--muted); font-size:13px; margin-left:8px;">{{ $event->location ?? 'TBD' }}</span>
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
      <a href="{{ route('events.show', $event) }}" style="display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">View Event Details</a>
    </td>
  </tr>
  <tr>
    <td style="padding-top:20px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="50%" valign="top" style="padding-right:8px;">
            <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
              <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">📱 Stay Updated</p>
              <p style="margin:0; color:var(--muted); font-size:13px;">Follow us for more event updates and news.</p>
            </div>
          </td>
          <td width="50%" valign="top" style="padding-left:8px;">
            <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
              <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">🎫 Your Tickets</p>
              <p style="margin:0; color:var(--muted); font-size:13px;">Your tickets are safe and ready for the event.</p>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
@endsection
