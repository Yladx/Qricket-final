@extends('emails.layouts.base')

@section('title', 'Password Reset - Qricket')
@php
    $heading = 'Reset your password';
    $subheading = 'We received a request to reset your password for your Qricket account. Click the button below to create a new password.';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="stack" valign="top" style="padding-bottom:16px;">
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
                        <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Security Notice</p>
                        <ul style="margin:0; padding-left:18px; color:var(--muted); font-size:14px;">
                            <li>This password reset link will expire in 60 minutes.</li>
                            <li>If you didn't request this reset, please ignore this email.</li>
                            <li>For security, we recommend changing your password regularly.</li>
                        </ul>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding-top:12px;">
            <a href="{{ $resetUrl }}" style="display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">Reset Password</a>
        </td>
    </tr>
    <tr>
        <td style="padding-top:22px;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="50%" valign="top" style="padding-right:8px;">
                        <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
                            <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">Secure Account</p>
                            <p style="margin:0; color:var(--muted); font-size:13px;">Keep your account safe with strong passwords.</p>
                        </div>
                    </td>
                    <td width="50%" valign="top" style="padding-left:8px;">
                        <div style="padding:12px; border-radius:8px; background-color:#ffffff; border:1px solid rgba(13,148,136,0.06);">
                            <p style="margin:0 0 6px 0; font-weight:700; color:var(--teal-dark);">Quick Access</p>
                            <p style="margin:0; color:var(--muted); font-size:13px;">Get back to creating amazing events quickly.</p>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection