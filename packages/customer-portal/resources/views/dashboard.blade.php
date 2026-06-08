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
            content="noindex, nofollow"
        />
        <title>
            {{ __('capell-customer-portal::generic.frontend.title') }}
        </title>
        <style>
            :root {
                color-scheme: light dark;
                --portal-bg: #f5f7f8;
                --portal-panel: #ffffff;
                --portal-text: #17202a;
                --portal-muted: #637083;
                --portal-border: #d8e0e6;
                --portal-accent: #145c72;
                --portal-accent-text: #ffffff;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --portal-bg: #111519;
                    --portal-panel: #1a2229;
                    --portal-text: #f4f7f8;
                    --portal-muted: #a8b4be;
                    --portal-border: #34424d;
                    --portal-accent: #8bd3e6;
                    --portal-accent-text: #10242b;
                }
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: var(--portal-bg);
                color: var(--portal-text);
                font-family:
                    ui-sans-serif,
                    system-ui,
                    -apple-system,
                    BlinkMacSystemFont,
                    'Segoe UI',
                    sans-serif;
                line-height: 1.5;
            }

            main {
                width: min(100%, 1040px);
                margin: 0 auto;
                padding: 32px 20px 48px;
            }

            header,
            section {
                margin-bottom: 24px;
                border: 1px solid var(--portal-border);
                border-radius: 8px;
                background: var(--portal-panel);
                padding: 24px;
            }

            h1,
            h2,
            h3,
            p {
                margin-top: 0;
            }

            h1 {
                margin-bottom: 8px;
                font-size: 32px;
                line-height: 1.1;
            }

            h2 {
                margin-bottom: 16px;
                font-size: 20px;
            }

            .muted {
                color: var(--portal-muted);
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 12px;
            }

            .item {
                border: 1px solid var(--portal-border);
                border-radius: 6px;
                padding: 16px;
            }

            label {
                display: block;
                margin-bottom: 8px;
                font-weight: 650;
            }

            input,
            select,
            textarea {
                width: 100%;
                min-height: 42px;
                border: 1px solid var(--portal-border);
                border-radius: 6px;
                background: transparent;
                color: var(--portal-text);
                font: inherit;
                padding: 9px 11px;
            }

            textarea {
                min-height: 120px;
                resize: vertical;
            }

            button {
                min-height: 42px;
                border: 0;
                border-radius: 6px;
                background: var(--portal-accent);
                color: var(--portal-accent-text);
                cursor: pointer;
                font: inherit;
                font-weight: 700;
                padding: 0 16px;
            }

            .stack {
                display: grid;
                gap: 14px;
            }

            .status {
                border-color: color-mix(
                    in srgb,
                    var(--portal-accent) 35%,
                    var(--portal-border)
                );
            }
        </style>
    </head>
    <body>
        <main>
            <header>
                <h1>
                    {{ __('capell-customer-portal::generic.frontend.title') }}
                </h1>
                <p class="muted">
                    {{ __('capell-customer-portal::generic.frontend.signed_in_as', ['name' => $displayName ?? $email ?? __('capell-customer-portal::generic.frontend.customer')]) }}
                </p>
            </header>

            @if (session('customer_portal_status'))
                <section class="status">
                    {{ session('customer_portal_status') }}
                </section>
            @endif

            <section>
                <h2>
                    {{ __('capell-customer-portal::generic.frontend.profile') }}
                </h2>
                @if ($profileFields === [])
                    <p class="muted">
                        {{ __('capell-customer-portal::generic.frontend.no_profile_fields') }}
                    </p>
                @else
                    <dl class="grid">
                        @foreach ($profileFields as $field)
                            <div class="item">
                                <dt class="muted">{{ $field['label'] }}</dt>
                                <dd>{{ $field['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </section>

            <section>
                <h2>
                    {{ __('capell-customer-portal::generic.frontend.dashboard_items') }}
                </h2>
                @if ($dashboardItems === [])
                    <p class="muted">
                        {{ __('capell-customer-portal::generic.frontend.no_dashboard_items') }}
                    </p>
                @else
                    <div class="grid">
                        @foreach ($dashboardItems as $item)
                            <article class="item">
                                <h3>{{ $item['label'] }}</h3>
                                @if ($item['count'] !== null)
                                    <p class="muted">
                                        {{ __('capell-customer-portal::generic.frontend.item_count', ['count' => $item['count']]) }}
                                    </p>
                                @endif

                                @if ($item['description'] !== null)
                                    <p>{{ $item['description'] }}</p>
                                @endif

                                @if ($item['url'] !== null)
                                    <a href="{{ $item['url'] }}">
                                        {{ __('capell-customer-portal::generic.frontend.open_item') }}
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <h2>
                    {{ __('capell-customer-portal::generic.frontend.self_service_items') }}
                </h2>
                @if ($selfServiceItems === [])
                    <p class="muted">
                        {{ __('capell-customer-portal::generic.frontend.no_self_service_items') }}
                    </p>
                @else
                    <div class="stack">
                        @foreach ($selfServiceItems as $item)
                            <article class="item">
                                <p class="muted">{{ $item['type'] }}</p>
                                <h3>{{ $item['label'] }}</h3>
                                @if ($item['status'] !== null)
                                    <p class="muted">{{ $item['status'] }}</p>
                                @endif

                                @if ($item['occurred_at'] !== null)
                                    <p class="muted">
                                        {{ $item['occurred_at'] }}
                                    </p>
                                @endif

                                @if ($item['description'] !== null)
                                    <p>{{ $item['description'] }}</p>
                                @endif

                                @if ($item['url'] !== null)
                                    <a href="{{ $item['url'] }}">
                                        {{ __('capell-customer-portal::generic.frontend.open_item') }}
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <h2>
                    {{ __('capell-customer-portal::generic.frontend.preferences') }}
                </h2>
                <form
                    method="post"
                    action="{{ route('capell-customer-portal.preferences.update') }}"
                    class="stack"
                >
                    @csrf
                    @foreach ($preferenceOptions as $option)
                        <label>
                            <input
                                type="hidden"
                                name="preferences[{{ $option['key'] }}]"
                                value="0"
                            />
                            <input
                                type="checkbox"
                                name="preferences[{{ $option['key'] }}]"
                                value="1"
                                @checked((bool) ($preferences[$option['key']] ?? false))
                            />
                            {{ $option['label'] }}
                        </label>
                    @endforeach

                    <button type="submit">
                        {{ __('capell-customer-portal::generic.frontend.save_preferences') }}
                    </button>
                </form>
            </section>

            <section>
                <h2>
                    {{ __('capell-customer-portal::generic.frontend.support') }}
                </h2>
                <form
                    method="post"
                    action="{{ route('capell-customer-portal.support.store') }}"
                    class="stack"
                >
                    @csrf
                    <label>
                        {{ __('capell-customer-portal::generic.frontend.support_subject') }}
                        <input
                            type="text"
                            name="subject"
                            required
                            maxlength="160"
                        />
                    </label>
                    <label>
                        {{ __('capell-customer-portal::generic.frontend.support_priority') }}
                        <select name="priority">
                            @foreach ($priorityOptions as $value => $label)
                                <option value="{{ $value }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        {{ __('capell-customer-portal::generic.frontend.support_message') }}
                        <textarea
                            name="message"
                            required
                            maxlength="5000"
                        ></textarea>
                    </label>
                    <button type="submit">
                        {{ __('capell-customer-portal::generic.frontend.submit_support') }}
                    </button>
                </form>
            </section>

            <section>
                <h2>
                    {{ __('capell-customer-portal::generic.frontend.recent_support') }}
                </h2>
                @if ($supportRequests === [])
                    <p class="muted">
                        {{ __('capell-customer-portal::generic.frontend.no_support_requests') }}
                    </p>
                @else
                    <div class="stack">
                        @foreach ($supportRequests as $supportRequest)
                            <article class="item">
                                <h3>{{ $supportRequest['subject'] }}</h3>
                                <p class="muted">
                                    {{ $supportRequest['status'] }} &middot;
                                    {{ $supportRequest['priority'] }}
                                    @if ($supportRequest['submitted_at'] !== null)
                                        &middot;
                                        {{ $supportRequest['submitted_at'] }}
                                    @endif
                                </p>
                                <p>{{ $supportRequest['message'] }}</p>
                                @if ($supportRequest['replies'] !== [])
                                    <div class="stack">
                                        @foreach ($supportRequest['replies'] as $reply)
                                            <p class="muted">
                                                {{
                                                    __('capell-customer-portal::generic.frontend.support_reply_meta', [
                                                        'sender' => __('capell-customer-portal::generic.frontend.support_sender_' . $reply['sender_type']),
                                                        'date' => $reply['submitted_at'] ?? __('capell-customer-portal::generic.frontend.support_reply_recent'),
                                                    ])
                                                }}
                                            </p>
                                            <p>{{ $reply['message'] }}</p>
                                        @endforeach
                                    </div>
                                @endif

                                <form
                                    method="post"
                                    action="{{ route('capell-customer-portal.support.replies.store', ['supportRequest' => $supportRequest['id']]) }}"
                                >
                                    @csrf
                                    <label>
                                        {{ __('capell-customer-portal::generic.frontend.support_reply') }}
                                        <textarea
                                            name="message"
                                            required
                                            maxlength="5000"
                                        ></textarea>
                                    </label>
                                    <button type="submit">
                                        {{ __('capell-customer-portal::generic.frontend.submit_support_reply') }}
                                    </button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>
    </body>
</html>
