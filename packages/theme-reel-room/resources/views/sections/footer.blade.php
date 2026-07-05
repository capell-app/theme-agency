@php
    $brandName = data_get($section, 'brandName', data_get($section, 'brand', __('capell-theme-reel-room::sections.navigation.brand')));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.footer.tagline'));
    $columns = data_get($section, 'columns', data_get($section, 'items', [
        [
            'heading' => __('capell-theme-reel-room::sections.footer.archive'),
            'links' => [
                ['label' => __('capell-theme-reel-room::sections.footer.browse_by_year'), 'url' => '/#archive'],
                ['label' => __('capell-theme-reel-room::sections.footer.winners'), 'url' => '/#winner-list'],
                ['label' => __('capell-theme-reel-room::sections.footer.categories'), 'url' => '/#date-filter-rail'],
                ['label' => __('capell-theme-reel-room::sections.footer.earlier_cycles'), 'url' => '/theme-reel-room-directory'],
            ],
        ],
        [
            'heading' => __('capell-theme-reel-room::sections.footer.the_awards'),
            'links' => [
                ['label' => __('capell-theme-reel-room::sections.footer.jury_scoring'), 'url' => '/#jury-score-explainer'],
                ['label' => __('capell-theme-reel-room::sections.footer.how_to_submit'), 'url' => '/theme-reel-room-cta'],
                ['label' => __('capell-theme-reel-room::sections.footer.credits_policy'), 'url' => '/#media-credits'],
            ],
        ],
        [
            'heading' => __('capell-theme-reel-room::sections.footer.connect'),
            'links' => [
                ['label' => __('capell-theme-reel-room::sections.footer.submit_work'), 'url' => '/theme-reel-room-contact'],
                ['label' => 'archive@frameindex.example', 'url' => 'mailto:archive@frameindex.example'],
            ],
        ],
    ]));
@endphp

<footer class="mva-section mva-footer">
    <div class="mva-section-inner">
        <div class="mva-footer-grid">
            <div>
                <h3>{{ $brandName }}</h3>
                <p
                    style="
                        color: var(--mva-muted);
                        line-height: 1.6;
                        max-width: 20rem;
                    "
                >
                    {{ $summary }}
                </p>
            </div>
            @foreach ($columns as $column)
                <div>
                    <h3>
                        {{ data_get($column, 'heading', data_get($column, 'title', '')) }}
                    </h3>
                    @foreach (data_get($column, 'links', []) as $link)
                        @if (is_array($link))
                            <a
                                href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                            >
                                {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                            </a>
                        @else
                            <span>{{ $link }}</span>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
        <p class="mva-footer-bottom">{{ $brandName }} &copy; {{ date('Y') }}</p>
    </div>
</footer>
