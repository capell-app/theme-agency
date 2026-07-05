@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.credits.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.credits.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="creator-credits"
    class="sbs-section sbs-section-field"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-gold-rush::sections.credits.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="sbs-lede">{{ $summary }}</p>

        <div class="sbs-credit-grid">
            @foreach ($items as $item)
                <article class="sbs-credit-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
