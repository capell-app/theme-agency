<!DOCTYPE html>
<html lang="en">
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
                color-scheme: light;
                font-family:
                    ui-sans-serif,
                    system-ui,
                    -apple-system,
                    BlinkMacSystemFont,
                    'Segoe UI',
                    sans-serif;
            }

            body {
                align-items: center;
                background: #f8fafc;
                color: #0f172a;
                display: flex;
                margin: 0;
                min-height: 100vh;
                padding: 2rem;
            }

            main {
                margin: 0 auto;
                max-width: 34rem;
            }

            h1 {
                font-size: 1.25rem;
                font-weight: 650;
                margin: 0 0 0.5rem;
            }

            p {
                color: #475569;
                font-size: 0.95rem;
                line-height: 1.6;
                margin: 0;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>{{ $title }}</h1>
            <p>{{ $body }}</p>
        </main>
    </body>
</html>
