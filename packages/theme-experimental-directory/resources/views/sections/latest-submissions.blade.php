@php
    $heading = data_get($section, 'heading', __('capell-theme-experimental-directory::sections.submissions.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-experimental-directory::sections.submissions.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-experimental-directory::sections.submissions.entry_title'), 'summary' => __('capell-theme-experimental-directory::sections.submissions.entry_summary'), 'meta' => __('capell-theme-experimental-directory::sections.submissions.entry_meta')],
    ]);
@endphp

<section
    id="latest-submissions"
    class="exd-section"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-experimental-directory::sections.submissions.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        <div class="exd-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                @endphp

                @if (filled($itemUrl))
                    <a
                        class="exd-card"
                        href="{{ $itemUrl }}"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="800"
                                height="600"
                                loading="lazy"
                                decoding="async"
                                class="exd-media"
                            />
                        @else
                            <div
                                class="exd-media exd-media-empty"
                                aria-hidden="true"
                            ></div>
                        @endif
                        <div class="exd-card-body">
                            <p class="exd-meta">
                                {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-experimental-directory::sections.submissions.default_meta'))) }}
                            </p>
                            <h3>{{ $itemTitle }}</h3>
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </div>
                    </a>
                @else
                    <article class="exd-card">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="800"
                                height="600"
                                loading="lazy"
                                decoding="async"
                                class="exd-media"
                            />
                        @else
                            <div
                                class="exd-media exd-media-empty"
                                aria-hidden="true"
                            ></div>
                        @endif
                        <div class="exd-card-body">
                            <p class="exd-meta">
                                {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-experimental-directory::sections.submissions.default_meta'))) }}
                            </p>
                            <h3>{{ $itemTitle }}</h3>
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>
