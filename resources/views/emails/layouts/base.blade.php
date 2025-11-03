<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Qricket — {{ $title ?? 'Email' }}</title>
  <style>
    body,table,td,a{ -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    table,td{ mso-table-lspace:0pt; mso-table-rspace:0pt; }
    img{ -ms-interpolation-mode:bicubic; }
    img{ border:0; height:auto; line-height:100%; outline:none; text-decoration:none; }
    table{ border-collapse:collapse !important; }
    body{ margin:0 !important; padding:0 !important; width:100% !important; }

    :root{
      --teal-dark: #064e47;
      --teal: #0d9488;
      --teal-light: #14b8a6;
      --bg: #f6fffd;
      --muted: #6b7280;
      --white: #ffffff;
    }

    @media screen and (max-width:600px){
      .container{ width:100% !important; }
      .stack{ display:block !important; width:100% !important; }
      .hero-title{ font-size:22px !important; }
    }
  </style>
</head>
<body style="background-color:var(--bg); font-family: 'Helvetica Neue', Arial, sans-serif;">
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center" style="padding:24px 16px;">
        <table class="container" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width:600px; background-color:var(--white); border-radius:12px; box-shadow:0 6px 18px rgba(6,78,71,0.06); overflow:hidden;">
          <tr>
            <td style="padding:20px 24px; background:linear-gradient(90deg,var(--teal) 0%, var(--teal-light) 100%); color:var(--white);">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="left" style="vertical-align:middle;">
                    <img src="{{ asset('Logo.svg') }}" width="140" height="36" alt="Qricket" style="display:block; border:0;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <span style="display:none; font-size:24px; font-weight:700; color:var(--white);">Qricket</span>
                  </td>
                  <td align="right" style="vertical-align:middle; font-size:20px; color:var(--white); font-weight:700;">
                    <strong>Qricket</strong>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:28px 28px 18px 28px; text-align:left;">
              <h1 class="hero-title" style="margin:0 0 8px 0; font-size:26px; color:var(--teal-dark);">{{ $heading ?? 'Email from Qricket' }}</h1>
              <p style="margin:0; color:var(--muted); font-size:15px; line-height:1.5;">{{ $subheading ?? 'Thank you for using Qricket!' }}</p>
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 24px 28px;">
              @yield('content')
            </td>
          </tr>
          <tr>
            <td style="padding:18px 28px 28px 28px; background-color:#fcfffd;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="font-size:13px; color:var(--muted);">
                    <p style="margin:0 0 10px 0;">Need help? Reply to this email or visit our <a href="{{ route('help.index') }}" style="color:var(--teal); text-decoration:underline;">help center</a>.</p>
                    <p style="margin:0 0 6px 0;">Qricket • Event Management Platform</p>
                    <p style="margin:0; font-size:12px; color:#9ca3af;">You received this email because you're using Qricket. <a href="#" style="color:var(--teal); text-decoration:none;">Unsubscribe</a></p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
