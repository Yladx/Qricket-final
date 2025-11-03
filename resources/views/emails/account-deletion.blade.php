@extends('emails.layouts.base')

@section('title', 'Account Deletion - Qricket')
@php
    $heading = 'Account Deletion Confirmation';
    $subheading = 'Your Qricket account has been scheduled for deletion.';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="stack" valign="top" style="padding-bottom:16px;">
            <p style="margin:0 0 12px 0; color:var(--muted); font-size:15px; line-height:1.6;">
                Hello {{ $user->first_name ?? 'there' }},
            </p>
            <p style="margin:0 0 12px 0; color:var(--muted); font-size:15px; line-height:1.6;">
                This email confirms that your Qricket account ({{ $user->email }}) has been scheduled for deletion.
            </p>
        </td>
    </tr>
    <tr>
        <td class="stack" valign="top" style="padding-bottom:16px;">
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding:12px; border-radius:10px; background-color:rgba(239, 68, 68, 0.06);">
                        <p style="margin:0 0 8px 0; font-weight:600; color:#dc2626;">Important Information</p>
                        <ul style="margin:0; padding-left:18px; color:var(--muted); font-size:14px; line-height:1.8;">
                            <li>Your account will be permanently deleted in <strong>30 days</strong> from today.</li>
                            <li>During this period, you can restore your account by contacting support.</li>
                            <li>After 30 days, all your data will be permanently removed and cannot be recovered.</li>
                            <li>If you did not request this deletion, please contact us immediately.</li>
                        </ul>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="stack" valign="top" style="padding-bottom:16px;">
            <p style="margin:0 0 12px 0; color:var(--muted); font-size:15px; line-height:1.6;">
                If you change your mind and want to restore your account, please contact our support team within the next 30 days. We're here to help!
            </p>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding-top:12px;">
            <a href="{{ route('help.index') }}" style="display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">Contact Support</a>
        </td>
    </tr>
    <tr>
        <td class="stack" valign="top" style="padding-top:20px;">
            <p style="margin:0 0 8px 0; color:var(--muted); font-size:14px; line-height:1.6;">
                Thank you for being a part of Qricket. We're sorry to see you go.
            </p>
        </td>
    </tr>
</table>
@endsection

