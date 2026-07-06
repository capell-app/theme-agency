@if (! $embedded)
    <x-filament-widgets::widget>
        <x-filament::section
            :heading="__('capell-seo-suite::generic.seo_audit')"
            icon="heroicon-o-magnifying-glass"
            :collapsible="true"
        >
            @include ('capell-seo-suite::filament.widgets.partials.seo-audit-edit-content')
        </x-filament::section>
    </x-filament-widgets::widget>
@else
    @include ('capell-seo-suite::filament.widgets.partials.seo-audit-edit-content')
@endif
