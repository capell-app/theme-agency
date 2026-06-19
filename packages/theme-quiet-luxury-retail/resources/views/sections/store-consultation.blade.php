@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.consultation.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-luxury-retail::sections.consultation.summary'));
    $actions = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.consultation.appointment_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.consultation.appointment_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.consultation.location_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.consultation.location_summary')],
    ]);
@endphp

<section class="luxury-section luxury-section-dark">
    <div class="luxury-section-inner luxury-split">
        <div>
            <p class="luxury-kicker">
                {{ __('capell-theme-quiet-luxury-retail::sections.consultation.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="luxury-lede">{{ $summary }}</p>
            <a
                class="luxury-button"
                href="{{ data_get($section, 'url', '/') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-quiet-luxury-retail::sections.consultation.button')) }}
            </a>
        </div>
        <div class="luxury-grid">
            @foreach ($actions as $action)
                <article class="luxury-card">
                    <h3>
                        {{ data_get($action, 'title', data_get($action, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($action, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
