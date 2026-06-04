<x-filament::button
    icon="heroicon-o-arrow-path"
    size="sm"
    wire:click="runAudit"
    wire:loading.attr="disabled"
    wire:target="runAudit"
>
    {{ __('capell-seo-suite::generic.pagespeed_run_audit') }}
</x-filament::button>
