@php
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.winners.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.winners.summary'));
    $items = data_get($section, 'items', data_get($section, 'actions', [
        ['title' => __('capell-theme-wild-card::sections.winners.entry_title'), 'summary' => __('capell-theme-wild-card::sections.winners.entry_summary')],
    ]));
    $label = data_get($section, 'label');
    $url = data_get($section, 'url');
@endphp

<section
    id="winners-collections"
    class="exd-section exd-section-paper"
>
    <div class="exd-section-inner">
        <div class="exd-heading-row">
            <div>
                <p class="exd-kicker">
                    {{ __('capell-theme-wild-card::sections.winners.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="exd-lede">{{ $summary }}</p>
            </div>
            @if (filled($label) && filled($url))
                <a
                    class="exd-button exd-button-secondary"
                    href="{{ $url }}"
                >
                    {{ $label }}
                </a>
            @endif
        </div>

        <div class="exd-grid">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                @endphp

                <article class="exd-card">
                    <div class="exd-card-body">
                        <span class="exd-badge exd-badge-signal">
                            {{ __('capell-theme-wild-card::sections.winners.badge') }}
                        </span>
                        <h3>
                            @if (filled($itemUrl))
                                <a
                                    class="exd-title-link"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ $itemTitle }}
                                </a>
                            @else
                                {{ $itemTitle }}
                            @endif
                        </h3>
                        <p>{{ data_get($item, 'summary', '') }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
