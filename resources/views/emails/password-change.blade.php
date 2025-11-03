@extends('emails.layouts.base')

@section('title', 'Password Changed - Qricket')
@php
    $heading = 'Password Changed Successfully';
    $subheading = 'Your Qricket account password has been updated.';
@endphp

@section('content')
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="stack" valign="top" style="padding-bottom:16px;">
            <p style="margin:0 0 12px 0; color:var(--muted); font-size:15px; line-height:1.6;">
                Hello {{ $user->first_name ?? 'there' }},
            </p>
            <p style="margin:0 0 12px 0; color:var(--muted); font-size:15px; line-height:1.6;">
                This email confirms that your password for your Qricket account ({{ $user->email }}) has been successfully changed.
            </p>
        </td>
    </tr>
    <tr>
        <td class="stack" valign="top" style="padding-bottom:16px;">
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding:12px; border-radius:10px; background-color:rgba(13,148,136,0.06);">
                        <p style="margin:0 0 8px 0; font-weight:600; color:var(--teal-dark);">Security Notice</p>
                        <ul style="margin:0; padding-left:18px; color:var(--muted); font-size:14px; line-height:1.8;">
                            <li>If you made this change, you can safely ignore this email.</li>
                            <li>If you did not change your password, please contact us immediately.</li>
                            <li>For your security, we recommend using a strong, unique password.</li>
                            <li>Never share your password with anyone.</li>
                        </ul>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding-top:12px;">
            <a href="{{ route('help.index') }}" style="display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; background-color:var(--teal); color:var(--white); font-weight:600; font-size:15px;">Get Help</a>
        </td>
    </tr>
    <tr>
        <td class="stack" valign="top" style="padding-top:20px;">
            <p style="margin:0 0 8px 0; color:var(--muted); font-size:14px; line-height:1.6;">
                Stay secure!<br>
                The Qricket Team
            </p>
        </td>
    </tr>
</table>
@endsection

