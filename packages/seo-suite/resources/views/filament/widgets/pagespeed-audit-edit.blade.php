@if (! $embedded)
    <x-filament-widgets::widget>
        <x-filament::section
            :heading="__('capell-seo-suite::generic.pagespeed_audit')"
            icon="heroicon-o-bolt"
            :collapsible="true"
        >
            <x-slot name="headerEnd">
                @include ('capell-seo-suite::filament.widgets.partials.pagespeed-run-audit-button')
            </x-slot>

            @include ('capell-seo-suite::filament.widgets.partials.pagespeed-audit-edit-content')
        </x-filament::section>
    </x-filament-widgets::widget>
@else
    <div class="mb-4 flex justify-end">
        @include ('capell-seo-suite::filament.widgets.partials.pagespeed-run-audit-button')
    </div>

    @include ('capell-seo-suite::filament.widgets.partials.pagespeed-audit-edit-content')
@endif
