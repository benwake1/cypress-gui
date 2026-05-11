<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your SignalDeck CI instance is ready</title>
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
                  <td style="background-color:#39D5FF;padding:32px 40px;">
                    <p style="margin:0 0 8px;font-size:12px;font-weight:600;color:rgba(8,17,30,0.55);text-transform:uppercase;letter-spacing:1.2px;">You're in</p>
                    <h1 style="margin:0;font-size:26px;font-weight:700;color:#08111E;line-height:1.3;">Your instance is ready.</h1>
                  </td>
                </tr>
              </table>

              {{-- Body --}}
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td style="padding:36px 40px;">

                    <p style="margin:0 0 28px;font-size:15px;color:#334155;line-height:1.7;">
                      Your dedicated SignalDeck CI dashboard is live. Everything runs on your own server — your projects, your data, no shared infrastructure.
                    </p>

                    {{-- CTA button --}}
                    <table cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:36px;">
                      <tr>
                        <td style="background-color:#39D5FF;border-radius:8px;">
                          <a href="{{ $dashboardUrl }}/admin" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#08111E;text-decoration:none;letter-spacing:-0.2px;">
                            Open your dashboard →
                          </a>
                        </td>
                      </tr>
                    </table>

                    {{-- Credentials block --}}
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:32px;">
                      <tr>
                        <td style="padding:20px 24px;">
                          <p style="margin:0 0 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:1.2px;">Your login details</p>
                          <table cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                              <td style="padding:5px 0;font-size:13px;color:#64748b;padding-right:20px;white-space:nowrap;">URL</td>
                              <td style="padding:5px 0;font-size:13px;color:#0f172a;font-family:'Courier New',monospace;">{{ $dashboardUrl }}</td>
                            </tr>
                            <tr>
                              <td style="padding:5px 0;font-size:13px;color:#64748b;padding-right:20px;white-space:nowrap;">Email</td>
                              <td style="padding:5px 0;font-size:13px;color:#0f172a;font-family:'Courier New',monospace;">{{ $customerEmail }}</td>
                            </tr>
                            @if($adminPassword)
                            <tr>
                              <td style="padding:5px 0;font-size:13px;color:#64748b;padding-right:20px;white-space:nowrap;">Password</td>
                              <td style="padding:5px 0;font-size:13px;color:#0f172a;font-family:'Courier New',monospace;">{{ $adminPassword }}</td>
                            </tr>
                            @endif
                          </table>
                          @if($adminPassword)
                          <p style="margin:14px 0 0;font-size:12px;color:#94a3b8;line-height:1.5;">Change this in your profile settings once you're in.</p>
                          @endif
                        </td>
                      </tr>
                    </table>

                    {{-- App callout --}}
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f0f9ff;border-radius:8px;border-left:3px solid #39D5FF;">
                      <tr>
                        <td style="padding:16px 20px;">
                          <p style="margin:0 0 5px;font-size:13px;font-weight:600;color:#0f172a;">Get the app</p>
                          <p style="margin:0;font-size:13px;color:#64748b;line-height:1.6;">
                            Available on the App Store for macOS and iPad. Live run status in your menu bar or at a glance on your desk. Enter <span style="color:#0f172a;font-family:'Courier New',monospace;">{{ $dashboardUrl }}</span> in the setup screen.
                          </p>
                        </td>
                      </tr>
                    </table>

                    @if($billingUrl)
                    <p style="margin:28px 0 0;font-size:13px;color:#64748b;line-height:1.6;">
                      Need to change your plan or cancel? Use your
                      <a href="{{ $billingUrl }}" style="color:#0891b2;text-decoration:none;">billing portal</a>.
                      Keep this email — it has your unique link.
                    </p>
                    @endif

                    <p style="margin:28px 0 0;font-size:14px;color:#334155;line-height:1.7;">
                      Hope it goes well — we're here if you need anything.<br>
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
