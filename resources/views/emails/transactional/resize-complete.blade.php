<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plan {{ $direction }}</title>
</head>
<body style="margin:0;padding:0;background-color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#0f172a;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" role="presentation" style="max-width:560px;width:100%;">

          {{-- Wordmark --}}
          <tr>
            <td align="center" style="padding-bottom:32px;">
              <span style="font-size:22px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">
                Signal<span style="color:#00d4aa;">Deck</span>
              </span>
            </td>
          </tr>

          {{-- Card --}}
          <tr>
            <td style="background-color:#1e293b;border-radius:12px;overflow:hidden;">

              {{-- Header band — teal for upgrade, amber for downgrade --}}
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  @if($direction === 'upgraded')
                  <td style="background:linear-gradient(135deg,#00d4aa 0%,#0891b2 100%);padding:28px 40px;">
                    <p style="margin:0 0 6px;font-size:12px;font-weight:600;color:rgba(0,0,0,0.55);text-transform:uppercase;letter-spacing:1.2px;">Plan upgraded</p>
                    <h1 style="margin:0;font-size:24px;font-weight:700;color:#0f172a;line-height:1.3;">You're now on the {{ ucfirst($toPlan) }} plan.</h1>
                  </td>
                  @else
                  <td style="background-color:#f59e0b;padding:28px 40px;">
                    <p style="margin:0 0 6px;font-size:12px;font-weight:600;color:rgba(0,0,0,0.55);text-transform:uppercase;letter-spacing:1.2px;">Plan downgraded</p>
                    <h1 style="margin:0;font-size:24px;font-weight:700;color:#0f172a;line-height:1.3;">You're now on the {{ ucfirst($toPlan) }} plan.</h1>
                  </td>
                  @endif
                </tr>
              </table>

              {{-- Body --}}
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td style="padding:36px 40px;">

                    <p style="margin:0 0 28px;font-size:15px;color:#94a3b8;line-height:1.7;">
                      Your instance has been resized and is back online. There may have been a brief interruption of 2–5 minutes during the transition.
                    </p>

                    {{-- Plan change table --}}
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#0f172a;border-radius:8px;margin-bottom:32px;">
                      <tr>
                        <td style="padding:20px 24px;">
                          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                              <td style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:1.2px;padding-bottom:10px;">Previous</td>
                              <td style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:1.2px;padding-bottom:10px;text-align:center;">→</td>
                              <td style="font-size:11px;font-weight:600;color:#00d4aa;text-transform:uppercase;letter-spacing:1.2px;padding-bottom:10px;text-align:right;">Current</td>
                            </tr>
                            <tr>
                              <td style="font-size:20px;font-weight:600;color:#64748b;">{{ ucfirst($fromPlan) }}</td>
                              <td></td>
                              <td style="font-size:20px;font-weight:700;color:#ffffff;text-align:right;">{{ ucfirst($toPlan) }}</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>

                    {{-- CTA --}}
                    <table cellpadding="0" cellspacing="0" role="presentation">
                      <tr>
                        <td style="background-color:#00d4aa;border-radius:8px;">
                          <a href="{{ $dashboardUrl }}/admin" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#0f172a;text-decoration:none;letter-spacing:-0.2px;">
                            Go to your dashboard →
                          </a>
                        </td>
                      </tr>
                    </table>

                  </td>
                </tr>
              </table>

            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="padding:28px 0 0;text-align:center;">
              <p style="margin:0;font-size:12px;color:#334155;">
                Questions? <a href="mailto:hello@signaldeck.tech" style="color:#00d4aa;text-decoration:none;">hello@signaldeck.tech</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
