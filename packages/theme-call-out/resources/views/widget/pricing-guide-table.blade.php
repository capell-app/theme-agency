@php
    /**
     * pricing-guide-table — uses the shared Foundation
     * `responsive-table-to-cards` primitive (Wave 2.7) for mobile collapse,
     * capped at <= 100 rows (§0.3).
     *
     * Two variants (theme-bar criterion 3), branched on payload `variant`:
     * "default" (job/price/notes columns) and "call-out-only" (job/price
     * only, no notes column — used when a page wants a terser guide).
     */
    $heading = $widget->getMeta('heading', __('capell-theme-call-out::sections.pricing.heading'));
    $summary = $widget->getMeta('summary', __('capell-theme-call-out::sections.pricing.summary'));
    $variant = $widget->getMeta('variant', 'default');
    $rows = collect($widget->getMeta('rows', []))->take(100);

    $headers = $variant === 'call-out-only'
        ? [__('capell-theme-call-out::sections.pricing.column_job'), __('capell-theme-call-out::sections.pricing.column_price')]
        : [__('capell-theme-call-out::sections.pricing.column_job'), __('capell-theme-call-out::sections.pricing.column_price'), __('capell-theme-call-out::sections.pricing.column_notes')];

    $tableRows = $rows->map(function (array $row) use ($variant): array {
        return $variant === 'call-out-only'
            ? [data_get($row, 'job', ''), data_get($row, 'price', '')]
            : [data_get($row, 'job', ''), data_get($row, 'price', ''), data_get($row, 'notes', '')];
    })->all();
@endphp

<section
    id="pricing-guide-table"
    class="rco-shell rco-section"
    data-widget="pricing-guide-table"
    data-variant="{{ $variant }}"
>
    <div class="rco-section-inner">
        <h2>{{ $heading }}</h2>
        <p class="rco-section-summary">{{ $summary }}</p>

        <x-capell-theme-foundation::display.responsive-table-to-cards
            :headers="$headers"
            :rows="$tableRows"
            class="rco-pricing-table"
        />
    </div>
</section>
