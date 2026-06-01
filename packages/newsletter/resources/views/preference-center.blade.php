<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        />
        <meta
            name="robots"
            content="noindex,nofollow"
        />
        <title>
            {{ __('capell-newsletter::generic.preference_center.title') }}
        </title>
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
                line-height: 1.5;
            }

            body {
                background: #f7f7f4;
                color: #1f2933;
                margin: 0;
            }

            main {
                margin: 0 auto;
                max-width: 42rem;
                padding: 4rem 1.25rem;
            }

            .panel {
                background: #fff;
                border: 1px solid #d9ddd5;
                border-radius: 8px;
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.75rem;
                line-height: 1.2;
                margin: 0 0 0.5rem;
            }

            p {
                margin: 0 0 1rem;
            }

            .muted {
                color: #667085;
            }

            .notice {
                background: #edf7ed;
                border: 1px solid #b7dfba;
                border-radius: 6px;
                margin: 0 0 1rem;
                padding: 0.75rem;
            }

            .options {
                display: grid;
                gap: 0.75rem;
                margin: 1.25rem 0;
            }

            label {
                align-items: start;
                border: 1px solid #d9ddd5;
                border-radius: 6px;
                display: flex;
                gap: 0.75rem;
                padding: 0.75rem;
            }

            input {
                margin-top: 0.3rem;
            }

            button {
                background: #1f2933;
                border: 0;
                border-radius: 6px;
                color: #fff;
                cursor: pointer;
                font: inherit;
                padding: 0.7rem 1rem;
            }
        </style>
    </head>
    <body>
        <main>
            <section class="panel">
                @if (session('newsletter_status'))
                    <p class="notice">{{ session('newsletter_status') }}</p>
                @endif

                <h1>
                    {{ __('capell-newsletter::generic.preference_center.title') }}
                </h1>
                <p class="muted">
                    {{ __('capell-newsletter::generic.preference_center.description', ['email' => $preferences->email]) }}
                </p>

                <form
                    method="post"
                    action="{{ route('capell-newsletter.preferences.update', ['token' => $token]) }}"
                >
                    @csrf

                    <div class="options">
                        @forelse ($preferences->segments as $segment)
                            <label>
                                <input
                                    type="checkbox"
                                    name="segments[]"
                                    value="{{ $segment->id }}"
                                    @checked($segment->selected)
                                />
                                <span>{{ $segment->name }}</span>
                            </label>
                        @empty
                            <p class="muted">
                                {{ __('capell-newsletter::generic.preference_center.no_segments') }}
                            </p>
                        @endforelse
                    </div>

                    <button type="submit">
                        {{ __('capell-newsletter::generic.preference_center.save') }}
                    </button>
                </form>
            </section>
        </main>
    </body>
</html>
