@php
    // §0.3 payload cap: grids <= 50 items; the compact variant additionally
    // trims to 4 items for tighter Layout Builder columns.
    $heading = data_get($section, 'heading', __('capell-theme-ink-press::sections.opinion.heading'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-ink-press::sections.opinion.home_title'), 'meta' => __('capell-theme-ink-press::sections.opinion.home_meta'), 'byline' => __('capell-theme-ink-press::sections.opinion.home_byline')],
        ['title' => __('capell-theme-ink-press::sections.opinion.chair_title'), 'meta' => __('capell-theme-ink-press::sections.opinion.chair_meta'), 'byline' => __('capell-theme-ink-press::sections.opinion.chair_byline')],
    ]))->take(4);
@endphp

<section
    id="opinion-grid-with-bylines"
    class="dnews-section"
>
    <div class="dnews-section-inner">
        <div class="dnews-section-head dnews-section-head-flush">
            <p class="dnews-kicker">
                {{ __('capell-theme-ink-press::sections.opinion.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
        </div>

        <ul class="dnews-opinion-compact-list">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $byline = data_get($item, 'byline', data_get($item, 'care_note', __('capell-theme-ink-press::sections.opinion.care_note')));
                @endphp

                <li>
                    <p class="dnews-meta dnews-meta-accent">
                        {{ data_get($item, 'meta', __('capell-theme-ink-press::sections.opinion.default_meta')) }}
                    </p>
                    <h4>
                        @if ($itemUrl !== null)
                            <a
                                class="dnews-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        @endif
                    </h4>
                    <p class="dnews-meta dnews-opinion-byline">{{ $byline }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
