@php
    $heading = data_get($section, 'heading', __('capell-theme-bold-sport-commerce::sections.offer.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-bold-sport-commerce::sections.offer.summary'));
    $actions = data_get($section, 'items', [
        ['title' => __('capell-theme-bold-sport-commerce::sections.offer.club_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.offer.club_summary')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.offer.member_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.offer.member_summary')],
    ]);
@endphp

<section class="sport-section sport-section-dark">
    <div class="sport-section-inner sport-split">
        <div>
            <p class="sport-kicker">
                {{ __('capell-theme-bold-sport-commerce::sections.offer.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="sport-lede">{{ $summary }}</p>
            <a
                class="sport-button"
                href="{{ data_get($section, 'url', '/') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-bold-sport-commerce::sections.offer.button')) }}
            </a>
        </div>
        <div class="sport-grid">
            @foreach ($actions as $action)
                <article class="sport-card">
                    <h3>
                        {{ data_get($action, 'title', data_get($action, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($action, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
