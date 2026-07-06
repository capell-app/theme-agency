<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="capell-message"
>
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    />
    <meta
        name="robots"
        content="noindex, nofollow"
    />
    <title>{{ $title }}</title>
    <style>
        :root {
            color-scheme: light dark;
            --access-gate-bg: #f4f7f6;
            --access-gate-panel: #ffffff;
            --access-gate-text: #1f2933;
            --access-gate-muted: #5f6b78;
            --access-gate-border: #d8dfdc;
            --access-gate-accent: #165a4a;
            --access-gate-accent-text: #ffffff;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --access-gate-bg: #151817;
                --access-gate-panel: #202622;
                --access-gate-text: #f2f0ea;
                --access-gate-muted: #aeb8b1;
                --access-gate-border: #3a443f;
                --access-gate-accent: #9fd6c2;
                --access-gate-accent-text: #10201a;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background: var(--access-gate-bg);
            color: var(--access-gate-text);
            font-family:
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                'Segoe UI',
                sans-serif;
        }

        main {
            width: min(100%, 460px);
            padding: 32px;
            border: 1px solid var(--access-gate-border);
            border-radius: 8px;
            background: var(--access-gate-panel);
        }

        h1 {
            margin: 0 0 12px;
            font-size: 28px;
            line-height: 1.15;
            letter-spacing: 0;
        }

        p {
            margin: 0 0 24px;
            color: var(--access-gate-muted);
            line-height: 1.55;
        }

        a {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border-radius: 6px;
            background: var(--access-gate-accent);
            color: var(--access-gate-accent-text);
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>

        <a href="{{ $continueUrl }}"> {{ $continueLabel }} </a>
    </main>
</body>
</html>
