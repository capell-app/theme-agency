@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-reading-room::sections.api_table.heading'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $parameters = is_array($widget->getMeta('parameters')) ? array_slice($widget->getMeta('parameters'), 0, 100) : [];
    $codeSample = (string) ($widget->getMeta('codeSample') ?? '');
    $variant = (string) ($widget->getMeta('variant') ?? 'table');
@endphp

{{--
    `api-reference-parameter-table` — type badges per parameter, plus a
    copyable code block. The copy button uses only the Clipboard API
    (~0-2KB inline script per §0.4 — no shared module needed for a single
    button's worth of behaviour); `required` parameters get a distinct
    `rr-type-badge-required` treatment. Payload capped at <= 100 rows
    per §0.3.

    Two variants:
    - `table` (default): a real `<table>` — the parameter-table primitive.
    - `cards`: the table-to-cards primitive pattern, one card per parameter,
      for narrow columns where a table would force horizontal scroll.
--}}
<section
    id="api-reference-parameter-table"
    class="rr-shell rr-section"
>
    <div class="rr-section-inner">
        <p class="rr-eyebrow">
            {{ __('capell-theme-reading-room::sections.api_table.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>

        @if ($summary !== '')
            <p class="rr-lede">{{ $summary }}</p>
        @endif

        @if ($variant === 'cards')
            <div
                class="rr-api-cards"
                style="margin-top: clamp(1.5rem, 3vw, 2.25rem)"
            >
                @foreach ($parameters as $parameter)
                    <div class="rr-api-card">
                        <span
                            class="rr-api-param-name"
                            >{{ data_get($parameter, 'name', '') }}</span
                        >
                        <span
                            class="rr-type-badge {{ data_get($parameter, 'required', false) ? 'rr-type-badge-required' : '' }}"
                        >
                            {{ data_get($parameter, 'type', '') }}
                        </span>
                        <p>{{ data_get($parameter, 'description', '') }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="rr-api-table"
                style="margin-top: clamp(1.5rem, 3vw, 2.25rem)"
            >
                <table>
                    <thead>
                        <tr>
                            <th scope="col">
                                {{ __('capell-theme-reading-room::sections.api_table.column_name') }}
                            </th>
                            <th scope="col">
                                {{ __('capell-theme-reading-room::sections.api_table.column_type') }}
                            </th>
                            <th scope="col">
                                {{ __('capell-theme-reading-room::sections.api_table.column_description') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($parameters as $parameter)
                            <tr>
                                <td class="rr-api-param-name">
                                    {{ data_get($parameter, 'name', '') }}
                                </td>
                                <td>
                                    <span
                                        class="rr-type-badge {{ data_get($parameter, 'required', false) ? 'rr-type-badge-required' : '' }}"
                                    >
                                        {{ data_get($parameter, 'type', '') }}
                                    </span>
                                </td>
                                <td>
                                    {{ data_get($parameter, 'description', '') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($codeSample !== '')
            <div
                class="rr-copy-block"
                style="margin-top: clamp(1.5rem, 3vw, 2.25rem)"
            >
                <pre
                    class="rr-code-block"
                ><code data-copy-source>{{ $codeSample }}</code></pre>
                <button
                    type="button"
                    class="rr-copy-button"
                    data-copy-trigger
                    aria-label="{{ __('capell-theme-reading-room::sections.api_table.copy_label') }}"
                >
                    {{ __('capell-theme-reading-room::sections.api_table.copy_button') }}
                </button>
            </div>

            {{--
                Inline Clipboard API hydration (~0.5KB), mirroring
                `theme-main-stage::widget.countdown-band`'s inline-script
                pattern for a single small per-widget behaviour that has no
                shared Foundation module equivalent (§0.4 budget: per-theme
                custom JS ~0-2KB). Delegated (not per-button) so multiple
                instances of this widget on one page only need one listener.
            --}}
            <script>
                document.addEventListener('click', function (event) {
                    var trigger = event.target.closest('[data-copy-trigger]')

                    if (!trigger) {
                        return
                    }

                    var block = trigger.closest('.rr-copy-block')
                    var source = block
                        ? block.querySelector('[data-copy-source]')
                        : null

                    if (!source || !navigator.clipboard) {
                        return
                    }

                    navigator.clipboard
                        .writeText(source.textContent || '')
                        .then(function () {
                            trigger.dataset.copyState = 'copied'

                            window.setTimeout(function () {
                                delete trigger.dataset.copyState
                            }, 1500)
                        })
                })
            </script>
        @endif
    </div>
</section>
