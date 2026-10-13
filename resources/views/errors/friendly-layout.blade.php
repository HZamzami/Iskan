<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <style>
        :root {
            --bg: #f4f5f7;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --accent: #d97706;
            --accent-text: #ffffff;
            --badge-bg: #fef3c7;
            --badge-text: #92400e;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0b0f14;
                --card: #161b22;
                --text: #e5e7eb;
                --muted: #9ca3af;
                --border: #2b313a;
                --accent: #f59e0b;
                --accent-text: #111827;
                --badge-bg: #3b2a0a;
                --badge-text: #fcd34d;
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: var(--bg);
            color: var(--text);
            font-family: "Segoe UI", Tahoma, "Noto Sans Arabic", system-ui, sans-serif;
            line-height: 1.7;
        }

        .card {
            width: 100%;
            max-width: 520px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px 28px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
        }

        .code {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 999px;
            background: var(--badge-bg);
            color: var(--badge-text);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .5px;
        }

        h1 { font-size: 22px; margin: 16px 0 8px; }

        p { color: var(--muted); margin: 0 0 12px; }

        ul {
            text-align: right;
            color: var(--muted);
            margin: 0 0 24px;
            padding-inline-start: 20px;
        }

        li { margin-bottom: 4px; }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid var(--border);
            color: var(--text);
            background: transparent;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--accent-text);
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="code">@yield('code')</span>
        <h1>@yield('heading')</h1>
        @yield('content')
        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-primary">العودة للوحة التحكم</a>
            <button type="button" class="btn" onclick="history.back()">رجوع للصفحة السابقة</button>
        </div>
    </main>
</body>
</html>
