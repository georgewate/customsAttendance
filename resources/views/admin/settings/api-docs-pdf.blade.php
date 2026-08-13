<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>API Connection Points</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 20px; margin-bottom: 2px; }
        .subtitle { color: #64748b; margin-bottom: 18px; }
        h2 { font-size: 14px; margin-top: 22px; margin-bottom: 2px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        .group-note { color: #64748b; font-size: 10px; margin-bottom: 8px; }
        .endpoint { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 10px; margin-bottom: 8px; }
        .method { display: inline-block; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 9px; color: #fff; }
        .method-GET { background-color: #0284c7; }
        .method-POST { background-color: #059669; }
        .method-PUT { background-color: #d97706; }
        .method-DELETE { background-color: #e11d48; }
        .path { font-family: Courier, monospace; font-size: 11px; margin-left: 6px; }
        .auth-badge { display: inline-block; margin-left: 6px; padding: 2px 6px; border-radius: 3px; font-size: 8px; background-color: #0c2d5e; color: #fff; }
        .description { margin: 5px 0 3px; }
        .meta { color: #475569; font-size: 9.5px; }
        .meta strong { color: #1e293b; }
        .getting-started { border: 1px solid #cbd5e1; border-radius: 4px; padding: 8px 10px; margin-bottom: 10px; }
        code { font-family: Courier, monospace; background-color: #f1f5f9; padding: 1px 3px; }
    </style>
</head>
<body>
    <h1>API Connection Points</h1>
    <div class="subtitle">Smart Attendance &mdash; generated {{ $generatedAt }}</div>

    <div class="getting-started">
        <strong>No token API</strong> &mdash; every connection point below is a session/cookie-authenticated web
        route. Employees interact with <code>/scan/{qr_token}</code> without an account; admins and managers must
        first sign in at <code>/login</code> and keep the session cookie for subsequent requests.
    </div>

    @foreach ($groups as $group)
        <h2>{{ $group['name'] }}</h2>
        <div class="group-note">{{ $group['note'] }}</div>

        @foreach ($group['endpoints'] as $endpoint)
            <div class="endpoint">
                <div>
                    <span class="method method-{{ $endpoint['method'] }}">{{ $endpoint['method'] }}</span>
                    <span class="path">{{ $endpoint['path'] }}</span>
                    @if (! empty($endpoint['auth']))
                        <span class="auth-badge">Session auth required</span>
                    @endif
                </div>
                <div class="description">{{ $endpoint['description'] }}</div>
                <div class="meta"><strong>Params:</strong> {{ $endpoint['params'] }}</div>
                <div class="meta"><strong>Response:</strong> {{ $endpoint['response'] }}</div>
            </div>
        @endforeach
    @endforeach
</body>
</html>
