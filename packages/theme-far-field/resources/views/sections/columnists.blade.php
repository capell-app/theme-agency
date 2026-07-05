@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-far-field::sections.columnists.editor_title'), 'summary' => __('capell-theme-far-field::sections.columnists.editor_summary')],
        ['title' => __('capell-theme-far-field::sections.columnists.city_desk_title'), 'summary' => __('capell-theme-far-field::sections.columnists.city_desk_summary')],
        ['title' => __('capell-theme-far-field::sections.columnists.critic_title'), 'summary' => __('capell-theme-far-field::sections.columnists.critic_summary')],
    ]);
@endphp

<section
    class="gcm-section gcm-section-tinted"
    id="columnists"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.columnists.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-far-field::sections.columnists.heading')) }}
            </h2>
            <p class="gcm-lede">
                {{ data_get($section, 'summary', __('capell-theme-far-field::sections.columnists.summary')) }}
            </p>
        </div>
        <div class="gcm-grid gcm-grid-3">
            @foreach ($items as $item)
                @php
                    $columnTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                    $columnUrl = (string) data_get($item, 'url', data_get($item, 'href', ''));
                @endphp

                <article class="gcm-card">
                    <span
                        class="gcm-byline-mark"
                        aria-hidden="true"
                    >
                        {{ mb_substr(trim($columnTitle) !== '' ? trim($columnTitle) : 'A', 0, 1) }}
                    </span>
                    <h3>
                        @if ($columnUrl !== '')
                            <a
                                class="gcm-title-link"
                                href="{{ $columnUrl }}"
                            >
                                {{ $columnTitle }}
                            </a>
                        @else
                            {{ $columnTitle }}
                        @endif
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
