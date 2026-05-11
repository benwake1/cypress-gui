<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plan {{ $direction }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f1f5f9;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" role="presentation" style="max-width:560px;width:100%;">

          {{-- Wordmark --}}
          <tr>
            <td align="center" style="padding-bottom:24px;">
              <span style="font-size:22px;font-weight:700;color:#08111E;letter-spacing:-0.5px;">
                Signal<span style="color:#39D5FF;">Deck</span>
              </span>
            </td>
          </tr>

          {{-- Card --}}
          <tr>
            <td style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08),0 4px 16px rgba(0,0,0,0.06);">

              {{-- Header band --}}
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  @if($direction === 'upgraded')
                  <td style="background-color:#39D5FF;padding:28px 40px;">
                    <p style="margin:0 0 6px;font-size:12px;font-weight:600;color:rgba(8,17,30,0.55);text-transform:uppercase;letter-spacing:1.2px;">Plan upgraded</p>
                    <h1 style="margin:0;font-size:24px;font-weight:700;color:#08111E;line-height:1.3;">You're on {{ ucfirst($toPlan) }} now.</h1>
                  </td>
                  @else
                  <td style="background-color:#f59e0b;padding:28px 40px;">
                    <p style="margin:0 0 6px;font-size:12px;font-weight:600;color:rgba(8,17,30,0.55);text-transform:uppercase;letter-spacing:1.2px;">Plan changed</p>
                    <h1 style="margin:0;font-size:24px;font-weight:700;color:#08111E;line-height:1.3;">Switched to {{ ucfirst($toPlan) }}.</h1>
                  </td>
                  @endif
                </tr>
              </table>

              {{-- Body --}}
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td style="padding:36px 40px;">

                    @if($direction === 'upgraded')
                    <p style="margin:0 0 28px;font-size:15px;color:#334155;line-height:1.7;">
                      Your instance has been resized and is back online. You might have noticed a brief pause — usually 2–5 minutes. Everything should be running normally now.
                    </p>
                    @else
                    <p style="margin:0 0 28px;font-size:15px;color:#334155;line-height:1.7;">
                      Your instance has been resized and is back online. You might have noticed a brief pause — usually 2–5 minutes. If you need more capacity down the line, upgrading takes just a moment.
                    </p>
                    @endif

                    {{-- Plan change table --}}
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:32px;">
                      <tr>
                        <td style="padding:20px 24px;">
                          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                              <td style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:1.2px;padding-bottom:10px;">Previous</td>
                              <td style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:1.2px;padding-bottom:10px;text-align:center;">→</td>
                              <td style="font-size:11px;font-weight:600;color:#0891b2;text-transform:uppercase;letter-spacing:1.2px;padding-bottom:10px;text-align:right;">Current</td>
                            </tr>
                            <tr>
                              <td style="font-size:20px;font-weight:600;color:#94a3b8;">{{ ucfirst($fromPlan) }}</td>
                              <td></td>
                              <td style="font-size:20px;font-weight:700;color:#0f172a;text-align:right;">{{ ucfirst($toPlan) }}</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>

                    {{-- CTA --}}
                    <table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:32px;">
                      <tr>
                        <td style="background-color:#39D5FF;border-radius:8px;">
                          <a href="{{ $dashboardUrl }}/admin" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#08111E;text-decoration:none;letter-spacing:-0.2px;">
                            Go to your dashboard →
                          </a>
                        </td>
                      </tr>
                    </table>

                    <p style="margin:0;font-size:14px;color:#334155;line-height:1.7;">
                      Any trouble at all, reach us at <a href="mailto:hello@signaldeck.tech" style="color:#0891b2;text-decoration:none;">hello@signaldeck.tech</a>.<br>
                      <span style="color:#64748b;">— The SignalDeck team</span>
                    </p>

                  </td>
                </tr>
              </table>

            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="padding:28px 0 0;text-align:center;">
              <p style="margin:0 0 6px;font-size:12px;color:#94a3b8;">
                <a href="mailto:hello@signaldeck.tech" style="color:#94a3b8;text-decoration:none;">hello@signaldeck.tech</a>
              </p>
              <p style="margin:0;font-size:12px;color:#cbd5e1;">
                <a href="https://signaldeck.tech" style="color:#cbd5e1;text-decoration:none;">signaldeck.tech</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
