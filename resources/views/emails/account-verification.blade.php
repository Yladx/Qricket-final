@extends('emails.layouts.base')

@section('content')
<p style="margin:0 0 16px 0; color:#374151; font-size:15px; line-height:1.6;">Hello {{ $user->first_name ?? 'there' }},</p>

<p style="margin:0 0 20px 0; color:#374151; font-size:15px; line-height:1.6;">Thank you for registering with Qricket! To complete your account setup and start using our event management platform, please verify your email address by clicking the button below:</p>

<div style="text-align:center; margin:24px 0;">
    <a href="{{ $verificationUrl }}" style="display:inline-block; background-color:#0d9488; color:#ffffff; padding:14px 28px; text-decoration:none; border-radius:8px; font-weight:600; font-size:15px; line-height:1.4;">Verify Email Address</a>
</div>

<div style="background-color:#fef3c7; border:1px solid #f59e0b; color:#92400e; padding:16px; border-radius:8px; margin:20px 0;">
    <p style="margin:0; font-size:14px; line-height:1.5;"><strong>Important:</strong> This verification link will expire in 24 hours. If you don't verify your email within this time, you'll need to request a new verification email.</p>
</div>

<p style="margin:0 0 12px 0; color:#374151; font-size:15px; line-height:1.6;">If the button above doesn't work, you can copy and paste this link into your browser:</p>
<div style="background-color:#f9fafb; padding:12px; border-radius:6px; margin:12px 0; word-break:break-all; font-family:monospace; font-size:13px; color:#6b7280;">
    {{ $verificationUrl }}
</div>

<p style="margin:20px 0 12px 0; color:#374151; font-size:15px; line-height:1.6;">Once verified, you'll be able to:</p>
<ul style="margin:0 0 20px 0; padding-left:20px; color:#374151; font-size:15px; line-height:1.6;">
    <li style="margin-bottom:6px;">Create and manage events</li>
    <li style="margin-bottom:6px;">Purchase tickets</li>
    <li style="margin-bottom:6px;">Access all Qricket features</li>
    <li style="margin-bottom:6px;">Receive important notifications</li>
</ul>

<p style="margin:0; color:#6b7280; font-size:14px; line-height:1.6;">If you didn't create an account with Qricket, please ignore this email.</p>
@endsection

@php
    $title = 'Verify Your Email';
    $heading = 'Verify Your Email Address';
    $subheading = 'Complete your account setup to get started';
@endphp
