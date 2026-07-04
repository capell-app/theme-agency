@php
    $tabs = collect(data_get($section, 'tabs', data_get($section, 'items', [
        ['title' => __('capell-theme-minimal-curation-feed::sections.tabs.today')],
        ['title' => __('capell-theme-minimal-curation-feed::sections.tabs.this_week')],
        ['title' => __('capell-theme-minimal-curation-feed::sections.tabs.apps')],
        ['title' => __('capell-theme-minimal-curation-feed::sections.tabs.websites')],
    ])))
        ->filter(fn (mixed $tab): bool => filled(data_get($tab, 'title', data_get($tab, 'label'))))
        ->values();
    $descriptions = $tabs->filter(fn (mixed $tab): bool => filled(data_get($tab, 'summary')));
@endphp

<section
    id="category-tabs"
    class="mcf-section"
>
    <div class="mcf-section-inner">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-minimal-curation-feed::sections.tabs.kicker')) }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        <ul class="mcf-tabs">
            <li>
                <a
                    class="mcf-tab mcf-tab-active"
                    href="#curation-feed"
                    aria-current="true"
                >
                    {{ __('capell-theme-minimal-curation-feed::sections.tabs.latest') }}
                </a>
            </li>
            @foreach ($tabs as $tab)
                <li>
                    <a
                        class="mcf-tab"
                        href="{{ data_get($tab, 'url', data_get($tab, 'href', '#curation-feed')) }}"
                    >
                        {{ data_get($tab, 'title', data_get($tab, 'label', '')) }}
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($descriptions->isNotEmpty())
            <div class="mcf-tab-notes">
                @foreach ($descriptions as $tab)
                    <div class="mcf-tab-note">
                        <p class="mcf-meta">
                            {{ data_get($tab, 'title', data_get($tab, 'label', '')) }}
                        </p>
                        <p class="mcf-tiny">{{ data_get($tab, 'summary') }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
