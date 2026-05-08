<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your SignalDeck instance is ready</title>
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

              {{-- Header band --}}
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td style="background:linear-gradient(135deg,#00d4aa 0%,#0891b2 100%);padding:32px 40px;">
                    <p style="margin:0 0 8px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.75);text-transform:uppercase;letter-spacing:1.2px;">Instance ready</p>
                    <h1 style="margin:0;font-size:26px;font-weight:700;color:#ffffff;line-height:1.3;">Your dashboard is live.</h1>
                  </td>
                </tr>
              </table>

              {{-- Body --}}
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td style="padding:36px 40px;">

                    <p style="margin:0 0 28px;font-size:15px;color:#94a3b8;line-height:1.7;">
                      SignalDeck is up and running. Your dedicated instance is fully isolated — your test data stays on your server.
                    </p>

                    {{-- CTA button --}}
                    <table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:36px;">
                      <tr>
                        <td style="background-color:#00d4aa;border-radius:8px;">
                          <a href="{{ $dashboardUrl }}/admin" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#0f172a;text-decoration:none;letter-spacing:-0.2px;">
                            Open your dashboard →
                          </a>
                        </td>
                      </tr>
                    </table>

                    {{-- Credentials block --}}
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#0f172a;border-radius:8px;margin-bottom:32px;">
                      <tr>
                        <td style="padding:20px 24px;">
                          <p style="margin:0 0 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:1.2px;">Your login credentials</p>
                          <table cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                              <td style="padding:5px 0;font-size:13px;color:#64748b;padding-right:20px;white-space:nowrap;">URL</td>
                              <td style="padding:5px 0;font-size:13px;color:#e2e8f0;font-family:'Courier New',monospace;">{{ $dashboardUrl }}</td>
                            </tr>
                            <tr>
                              <td style="padding:5px 0;font-size:13px;color:#64748b;padding-right:20px;white-space:nowrap;">Email</td>
                              <td style="padding:5px 0;font-size:13px;color:#e2e8f0;font-family:'Courier New',monospace;">{{ $customerEmail }}</td>
                            </tr>
                            @if($adminPassword)
                            <tr>
                              <td style="padding:5px 0;font-size:13px;color:#64748b;padding-right:20px;white-space:nowrap;">Password</td>
                              <td style="padding:5px 0;font-size:13px;color:#e2e8f0;font-family:'Courier New',monospace;">{{ $adminPassword }}</td>
                            </tr>
                            @endif
                          </table>
                          @if($adminPassword)
                          <p style="margin:14px 0 0;font-size:12px;color:#475569;line-height:1.5;">Change this password in your profile settings after logging in.</p>
                          @endif
                        </td>
                      </tr>
                    </table>

                    {{-- Getting started steps --}}
                    <p style="margin:0 0 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:1.2px;">Get started in 3 steps</p>
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:32px;">
                      <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #0f172a;">
                          <span style="font-size:12px;color:#00d4aa;font-weight:700;margin-right:12px;">01</span>
                          <span style="font-size:14px;color:#94a3b8;">Log in and invite your team members</span>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #0f172a;">
                          <span style="font-size:12px;color:#00d4aa;font-weight:700;margin-right:12px;">02</span>
                          <span style="font-size:14px;color:#94a3b8;">Create a project and copy your API key</span>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:10px 0;">
                          <span style="font-size:12px;color:#00d4aa;font-weight:700;margin-right:12px;">03</span>
                          <span style="font-size:14px;color:#94a3b8;">Add the reporter to your Cypress / Playwright config</span>
                        </td>
                      </tr>
                    </table>

                    {{-- macOS app callout --}}
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#0f172a;border-radius:8px;border-left:3px solid #00d4aa;">
                      <tr>
                        <td style="padding:16px 20px;">
                          <p style="margin:0 0 5px;font-size:13px;font-weight:600;color:#e2e8f0;">Get the macOS app</p>
                          <p style="margin:0;font-size:13px;color:#64748b;line-height:1.6;">
                            SignalDeck CI gives you a native menu bar with live run status.
                            Enter <span style="color:#e2e8f0;font-family:'Courier New',monospace;">{{ $dashboardUrl }}</span> in the setup screen.
                          </p>
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
              <p style="margin:0 0 6px;font-size:12px;color:#334155;">
                Questions? Email <a href="mailto:hello@signaldeck.tech" style="color:#00d4aa;text-decoration:none;">hello@signaldeck.tech</a>
              </p>
              <p style="margin:0;font-size:12px;color:#1e293b;">
                <a href="https://signaldeck.tech" style="color:#334155;text-decoration:none;">signaldeck.tech</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
