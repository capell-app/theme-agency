@php
    $brandName = data_get($section, 'brandName', data_get($section, 'brand', __('capell-theme-case-study-platform::sections.navigation.brand')));
    $summary = data_get($section, 'summary');
    $columns = data_get($section, 'columns', data_get($section, 'items', [
        [
            'heading' => __('capell-theme-case-study-platform::sections.footer.directory_heading'),
            'links' => [
                ['label' => __('capell-theme-case-study-platform::sections.footer.all_projects'), 'url' => '#project-feed'],
                ['label' => __('capell-theme-case-study-platform::sections.footer.disciplines'), 'url' => '#discipline-filters'],
                ['label' => __('capell-theme-case-study-platform::sections.footer.tools'), 'url' => '#credits-tools'],
            ],
        ],
        [
            'heading' => __('capell-theme-case-study-platform::sections.footer.creators_heading'),
            'links' => [
                ['label' => __('capell-theme-case-study-platform::sections.footer.submit'), 'url' => '#newsletter'],
                ['label' => __('capell-theme-case-study-platform::sections.footer.list_studio'), 'url' => '#newsletter'],
                ['label' => __('capell-theme-case-study-platform::sections.footer.post_role'), 'url' => '#newsletter'],
            ],
        ],
        [
            'heading' => __('capell-theme-case-study-platform::sections.footer.studio_heading'),
            'links' => [
                ['label' => __('capell-theme-case-study-platform::sections.footer.newsletter'), 'url' => '#newsletter'],
            ],
        ],
    ]));
@endphp

<footer class="csp-section csp-section-dark">
    <div class="csp-section-inner csp-footer-grid">
        <div class="csp-footer-brand">
            <span class="csp-footer-wordmark">{{ $brandName }}</span>
            @if (filled($summary))
                <p class="csp-footer-summary">{{ $summary }}</p>
            @endif
        </div>

        @foreach ($columns as $column)
            <div>
                <h3 class="csp-footer-heading">
                    {{ data_get($column, 'heading', data_get($column, 'title', '')) }}
                </h3>
                <ul class="csp-footer-links">
                    @foreach (data_get($column, 'links', []) as $link)
                        <li>
                            @if (is_array($link))
                                <a
                                    href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                                >
                                    {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                                </a>
                            @else
                                <span>{{ $link }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</footer>
