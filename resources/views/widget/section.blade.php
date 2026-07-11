@php
    $section = is_object($widget) && method_exists($widget, 'getMeta')
        ? (array) $widget->getMeta()
        : [];
    $type = is_string($section['type'] ?? null) ? $section['type'] : '';
    $variant = is_string($section['variant'] ?? null) ? $section['variant'] : '';
    $variantViews = [
        'featured-portfolios:parallax' => 'featured-portfolios--parallax',
        'filter-taxonomies:grid' => 'filter-taxonomies--grid',
        'portfolio-grid:gallery-wall' => 'portfolio-grid--gallery-wall',
        'awarded-profiles:spotlight' => 'awarded-profiles--spotlight',
        'education-upsell:cta' => 'education-upsell--cta',
    ];
    $view = $variantViews[$type . ':' . $variant] ?? $type;
@endphp

@if ($view !== '' && view()->exists('capell-theme-agency::sections.' . $view))
    @include('capell-theme-agency::sections.' . $view, ['section' => $section])
@endif
