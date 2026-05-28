<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>
            {{ __('capell-frontend-authoring::authoring.edit_region') }}
        </title>
        @filamentStyles
    </head>
    <body class="fi-body antialiased">
        @livewire('capell-frontend-authoring.edit-region-field', ['payload' => $payload])

        <script>
            document.addEventListener('livewire:init', function () {
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
    </body>
</html>
