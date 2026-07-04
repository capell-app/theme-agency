@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section class="dnews-section">
    <div class="dnews-section-inner">
        <div class="dnews-section-head">
            <p class="dnews-kicker">
                {{ __('capell-theme-dense-news-analysis::sections.listing.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-dense-news-analysis::sections.listing.heading')) }}
            </h2>
            @if (data_get($section, 'summary') !== null)
                <p class="dnews-lede">
                    {{ data_get($section, 'summary') }}
                </p>
            @endif
        </div>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="dnews-grid">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    @endphp

                    <article class="dnews-card">
                        <p class="dnews-meta dnews-meta-accent">
                            {{ data_get($item, 'category', data_get($item, 'meta', __('capell-theme-dense-news-analysis::sections.listing.kicker'))) }}
                        </p>
                        <h3>
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
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="dnews-lede">
                {{ __('capell-theme-dense-news-analysis::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
