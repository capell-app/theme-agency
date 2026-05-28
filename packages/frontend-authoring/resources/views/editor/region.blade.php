<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="fi">
    <head>
        <meta charset="utf-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>
            {{ $title ?? __('capell-frontend-authoring::authoring.edit_region') }}
            
        </title>
        @include('capell::editor.filament-shell-assets')
        @livewireStyles
        <style>
            html {
                background: #f8fafc;
            }

            body.capell-authoring-editor-shell {
                margin: 0;
                min-height: 100vh;
                background: #f8fafc;
                color: #0f172a;
                font-family:
                    ui-sans-serif,
                    system-ui,
                    -apple-system,
                    BlinkMacSystemFont,
                    'Segoe UI',
                    sans-serif;
            }

            .capell-authoring-editor-shell__inner {
                box-sizing: border-box;
                min-height: 100vh;
                padding: 24px;
            }

            .capell-authoring-editor-shell__header {
                margin: 0 auto 18px;
                max-width: 880px;
            }

            .capell-authoring-editor-shell__eyebrow {
                color: #64748b;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.06em;
                line-height: 1;
                margin-bottom: 7px;
                text-transform: uppercase;
            }

            .capell-authoring-editor-shell__title {
                color: #111827;
                font-size: 20px;
                font-weight: 800;
                line-height: 1.2;
                margin: 0;
            }

            .capell-authoring-editor-shell__description {
                color: #475569;
                font-size: 13px;
                line-height: 1.5;
                margin: 8px 0 0;
            }

            .capell-authoring-editor-shell__panel {
                background: #fff;
                border: 1px solid rgba(15, 23, 42, 0.08);
                border-radius: 8px;
                box-shadow: 0 16px 48px rgba(15, 23, 42, 0.08);
                margin: 0 auto;
                max-width: 880px;
                overflow: hidden;
            }

            @media (max-width: 640px) {
                .capell-authoring-editor-shell__inner {
                    padding: 16px;
                }
            }
        </style>
    </head>
    <body class="fi-body capell-authoring-editor-shell antialiased">
        <main class="capell-authoring-editor-shell__inner">
            <header class="capell-authoring-editor-shell__header">
                <div class="capell-authoring-editor-shell__eyebrow">
                    {{ __('capell-frontend-authoring::authoring.admin_editing') }}
                </div>
                <h1 class="capell-authoring-editor-shell__title">
                    {{ $title ?? __('capell-frontend-authoring::authoring.edit_region') }}
                </h1>
                @if (! empty($description))
                    <p class="capell-authoring-editor-shell__description">
                        {{ $description }}
                    </p>
                @endif
            </header>

            <section class="capell-authoring-editor-shell__panel">
                @livewire('capell-frontend-authoring.edit-region-field', ['payload' => $payload])
            </section>
        </main>

        <script>
            document.addEventListener('livewire:init', function () {
                window.parent.postMessage(
                    {
                        type: 'capell-authoring:editor-loaded',
                    },
                    window.location.origin,
                )

                Livewire.on('capell-authoring-saved', function () {
                    window.parent.postMessage(
                        {
                            type: 'capell-authoring:saved',
                            detail: arguments[0] || {},
                        },
                        window.location.origin,
                    )
                })
            })

            document.addEventListener(
                'input',
                function (event) {
                    if (!event.target.matches('input, textarea, select')) {
                        return
                    }

                    window.parent.postMessage(
                        {
                            type: 'capell-authoring:dirty',
                        },
                        window.location.origin,
                    )
                },
                { capture: true },
            )
        </script>
        @filamentScripts(withCore: true)
        @livewireScripts
    </body>
</html>
