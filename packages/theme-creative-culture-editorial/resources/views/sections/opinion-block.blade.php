@php
    $heading = data_get($section, 'heading', __('capell-theme-creative-culture-editorial::sections.updates.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-creative-culture-editorial::sections.updates.summary'));
    $quotes = data_get($section, 'items', [
        ['title' => __('capell-theme-creative-culture-editorial::sections.updates.release_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.updates.release_summary')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.updates.beta_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.updates.beta_summary')],
    ]);
    $url = data_get($section, 'url', '/');
    $label = data_get($section, 'label', __('capell-theme-creative-culture-editorial::sections.updates.button'));
@endphp

<section
    id="opinion-block"
    class="cce-section cce-section-dark"
>
    <div class="cce-section-inner cce-split">
        <div>
            <p class="cce-kicker">
                {{ __('capell-theme-creative-culture-editorial::sections.updates.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="cce-lede">{{ $summary }}</p>
            @if (filled($url))
                <a
                    class="cce-button"
                    href="{{ $url }}"
                >
                    {{ $label }}
                </a>
            @endif
        </div>
        <div class="cce-grid">
            @foreach ($quotes as $quote)
                <article class="cce-card">
                    <h3>
                        {{ data_get($quote, 'title', data_get($quote, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($quote, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
