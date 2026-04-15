<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suite Health Breach</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#111827;">
@php
    $project     = $suite->project;
    $client      = $project->client ?? null;
    $brandColor  = $client?->primary_colour ?: config('brand.primary_color') ?: '#2563eb';
    $brandName   = config('brand.name') ?: config('app.name');
    $appLogoPath = config('brand.logo_path');
    $clientLogoPath = $client?->logo_url ?? null;
    $clientBg    = $client?->primary_colour ?: '#f3f4f6';
    $clientTextColor = $client?->primary_colour ? '#ffffff' : '#374151';
    $poweredBy   = config('brand.legal_name') ?: config('brand.name') ?: config('app.name');
    $dashboardUrl = config('app.url') . '/admin';
@endphp

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f3f4f6;padding:32px 16px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;width:100%;">

                {{-- App logo --}}
                <tr>
                    <td style="background:#f3f4f6;padding:20px 32px 12px;text-align:center;">
                        @if($appLogoPath)
                            <img src="{{ asset($appLogoPath) }}" alt="{{ $brandName }}" style="max-height:32px;width:auto;display:inline-block;">
                        @else
                            <span style="font-size:15px;font-weight:600;color:#6b7280;letter-spacing:-0.01em;">{{ $brandName }}</span>
                        @endif
                    </td>
                </tr>

                {{-- Client bar --}}
                <tr>
                    <td style="background:{{ $clientBg }};padding:14px 32px;border-radius:12px 12px 0 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                @if($clientLogoPath)
                                <td width="36" style="vertical-align:middle;padding-right:12px;">
                                    <img src="{{ $clientLogoPath }}" alt="{{ $client->name }}" style="max-height:32px;width:auto;display:block;">
                                </td>
                                @endif
                                <td style="vertical-align:middle;">
                                    @if($client)
                                    <span style="font-size:15px;font-weight:700;color:{{ $clientTextColor }};">{{ $client->name }}</span>
                                    <span style="font-size:15px;color:{{ $clientTextColor }};opacity:0.7;margin:0 6px;">/</span>
                                    @endif
                                    <span style="font-size:15px;color:{{ $clientTextColor }};opacity:0.85;">{{ $project->name }}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Status bar --}}
                <tr>
                    <td style="background:#fee2e2;padding:14px 32px;text-align:center;border-radius: 0 0 12px 12px">
                        <span style="font-size:15px;font-weight:700;color:#991b1b;">⚠️ Suite Health Breach</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><br /></td>
                </tr>

                {{-- Card body --}}
                <tr>
                    <td style="background:#ffffff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;">

                        {{-- Project / suite info --}}
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td style="padding:28px 32px 20px;border-bottom:1px solid #f3f4f6;">
                                    <p style="margin:0 0 2px;font-size:22px;font-weight:700;color:#111827;line-height:1.2;">{{ $suite->name }}</p>
                                    <p style="margin:0;font-size:14px;color:#6b7280;">{{ $project->name }}@if($client) · {{ $client->name }}@endif</p>
                                </td>
                            </tr>
                        </table>

                        {{-- Breach stats --}}
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding:24px 32px 0;">
                            <tr>
                                <td style="padding:0 8px 24px 0;">
                                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                        <tr>
                                            <td width="45%" style="background:#fee2e2;border-radius:8px;padding:16px 8px;text-align:center;">
                                                <div style="font-size:32px;font-weight:700;color:#dc2626;line-height:1;">{{ number_format($currentPassRate, 1) }}%</div>
                                                <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;margin-top:4px;">Current Pass Rate</div>
                                            </td>
                                            <td width="10%" style="padding:0 4px;text-align:center;font-size:20px;color:#9ca3af;">&lt;</td>
                                            <td width="45%" style="background:#f9fafb;border-radius:8px;padding:16px 8px;text-align:center;">
                                                <div style="font-size:32px;font-weight:700;color:#374151;line-height:1;">{{ number_format($threshold, 0) }}%</div>
                                                <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;margin-top:4px;">Threshold</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- Meta --}}
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td style="padding:0 32px 24px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f9fafb;border-radius:8px;padding:16px;">
                                        <tr>
                                            <td style="font-size:13px;color:#6b7280;line-height:2;">
                                                <span style="color:#374151;font-weight:600;">Suite</span>&nbsp;&nbsp;{{ $suite->name }}<br>
                                                <span style="color:#374151;font-weight:600;">Project</span>&nbsp;&nbsp;{{ $project->name }}<br>
                                                <span style="color:#374151;font-weight:600;">Breached at</span>&nbsp;&nbsp;{{ now()->format('d M Y, H:i') }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- CTA --}}
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td style="padding:0 32px 28px;">
                                    <a href="{{ $dashboardUrl }}" style="display:block;text-align:center;background:#004dd0;color:#ffffff;text-decoration:none;padding:14px 24px;border-radius:8px;font-weight:600;font-size:15px;">
                                        Open Dashboard
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:20px 0;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#9ca3af;">
                            You are receiving this as an admin of {{ $brandName }}.
                        </p>
                        <br>
                        <p style="margin:0;font-size:12px;color:#9ca3af;">
                            Powered by {{ $poweredBy }}
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
