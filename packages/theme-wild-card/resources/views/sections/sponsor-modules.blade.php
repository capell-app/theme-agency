@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-wild-card::sections.sponsors.entry_title'), 'summary' => __('capell-theme-wild-card::sections.sponsors.entry_summary')],
    ]);
@endphp

<section
    id="sponsor-modules"
    class="exd-section exd-section-raised"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.sponsors.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-wild-card::sections.sponsors.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="exd-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="exd-grid exd-grid-tight">
            @foreach ($items as $item)
                <article class="exd-card">
                    <div class="exd-card-body">
                        <span class="exd-badge">
                            {{ __('capell-theme-wild-card::sections.sponsors.badge') }}
                        </span>
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>{{ data_get($item, 'summary', '') }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
