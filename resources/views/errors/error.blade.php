<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $status }} &mdash; SignalDeck</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: #08111e;
            background-image: radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 0);
            background-size: 28px 28px;
            color: #9db3c5;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card {
            background-color: #0e1d30;
            border: 1px solid #315675;
            border-radius: 1.25rem;
            padding: 3rem 2.5rem;
            max-width: 460px;
            width: 100%;
            text-align: center;
        }

        /* ── Brand ────────────────────────────────────────────────────────── */
        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            margin-bottom: 2.5rem;
        }

        .brand-symbol {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #e2eaf1;
            letter-spacing: -0.02em;
        }

        /* ── Divider ──────────────────────────────────────────────────────── */
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #315675, transparent);
            margin-bottom: 2.5rem;
        }

        /* ── Status ───────────────────────────────────────────────────────── */
        .status-code {
            font-size: 5rem;
            font-weight: 700;
            color: #39d5ff;
            letter-spacing: -0.04em;
            line-height: 1;
            margin-bottom: 0.75rem;
        }

        .title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #e2eaf1;
            margin-bottom: 0.75rem;
        }

        .description {
            font-size: 0.875rem;
            line-height: 1.7;
            color: #9db3c5;
            margin-bottom: 2rem;
        }

        /* ── Ref block ────────────────────────────────────────────────────── */
        .ref-block {
            background-color: #091724;
            border: 1px solid #315675;
            border-radius: 0.625rem;
            padding: 1rem 1.25rem;
            margin-bottom: 2rem;
        }

        .ref-label {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9db3c5;
            margin-bottom: 0.375rem;
        }

        .ref-value {
            font-family: 'JetBrains Mono', 'Cascadia Code', 'Courier New', monospace;
            font-size: 1.125rem;
            font-weight: 600;
            color: #39d5ff;
            letter-spacing: 0.15em;
            user-select: all;
            cursor: text;
        }

        .ref-hint {
            font-size: 0.75rem;
            color: #315675;
            margin-top: 0.5rem;
        }

        /* ── Back link ────────────────────────────────────────────────────── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #9db3c5;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 1px solid #315675;
            border-radius: 0.5rem;
            transition: color 0.15s, border-color 0.15s;
        }

        .back-link:hover {
            color: #39d5ff;
            border-color: #39d5ff;
        }

        /* ── Footer ───────────────────────────────────────────────────────── */
        footer {
            margin-top: 2.5rem;
            font-size: 0.75rem;
            color: #315675;
        }
    </style>
</head>
<body>

    <div class="card">

        <div class="brand">
            {{-- SignalDeck brand symbol — inlined so it renders even during asset failures --}}
            <svg class="brand-symbol" viewBox="0 0 512 512" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="sd-panel" x1="96" y1="88" x2="416" y2="424" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-color="#16324A"/>
                        <stop offset="1" stop-color="#0B1728"/>
                    </linearGradient>
                    <linearGradient id="sd-bars" x1="172" y1="192" x2="332" y2="360" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-color="#39D5FF"/>
                        <stop offset="1" stop-color="#7FF7B8"/>
                    </linearGradient>
                    <linearGradient id="sd-check" x1="214" y1="218" x2="365" y2="332" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-color="#EAF6FF"/>
                        <stop offset="1" stop-color="#7BF6E8"/>
                    </linearGradient>
                    <filter id="sd-shadow" x="72" y="84" width="368" height="348" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feDropShadow dx="0" dy="18" stdDeviation="18" flood-color="#08111E" flood-opacity="0.28"/>
                    </filter>
                </defs>
                <g filter="url(#sd-shadow)">
                    <rect x="96" y="104" width="320" height="304" rx="72" fill="url(#sd-panel)"/>
                    <rect x="118" y="126" width="276" height="260" rx="54" stroke="#315675" stroke-width="4"/>
                </g>
                <circle cx="164" cy="167" r="11" fill="#39D5FF"/>
                <circle cx="196" cy="167" r="11" fill="#7BF6E8" opacity="0.72"/>
                <circle cx="228" cy="167" r="11" fill="#7FF7B8" opacity="0.5"/>
                <rect x="152" y="285" width="42" height="59" rx="21" fill="url(#sd-bars)"/>
                <rect x="214" y="236" width="42" height="108" rx="21" fill="url(#sd-bars)"/>
                <rect x="276" y="193" width="42" height="151" rx="21" fill="url(#sd-bars)"/>
                <path d="M254 287L287 320L361 241" stroke="url(#sd-check)" stroke-width="28" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="brand-name">SignalDeck</span>
        </div>

        <div class="divider"></div>

        <div class="status-code">{{ $status }}</div>

        <div class="title">
            @switch($status)
                @case(400) Bad Request @break
                @case(403) Access Denied @break
                @case(404) Page Not Found @break
                @case(405) Method Not Allowed @break
                @case(419) Session Expired @break
                @case(429) Too Many Requests @break
                @case(503) Service Unavailable @break
                @default  Something Went Wrong
            @endswitch
        </div>

        <p class="description">
            @switch($status)
                @case(403)
                    You do not have permission to access this page.
                    @break
                @case(404)
                    The page you are looking for could not be found. It may have been moved or deleted.
                    @break
                @case(419)
                    Your session has expired. Please refresh the page and try again.
                    @break
                @case(429)
                    Too many requests. Please wait a moment before trying again.
                    @break
                @case(503)
                    The service is temporarily unavailable. Please try again shortly.
                    @break
                @default
                    An unexpected error occurred. If the problem persists, contact your administrator and quote the reference number below.
            @endswitch
        </p>

        @if($ref)
        <div class="ref-block">
            <div class="ref-label">Error Reference</div>
            <div class="ref-value">{{ $ref }}</div>
            <div class="ref-hint">Quote this when contacting support.</div>
        </div>
        @endif

        <a href="javascript:history.back()" class="back-link">
            &larr; Go back
        </a>

    </div>

    <footer>&copy; {{ date('Y') }} SignalDeck. All rights reserved.</footer>

</body>
</html>
