@php
    $recordSwitcherAssets = 'Filament\\Support\\Facades\\FilamentAsset';

    $recordSwitcherStyleHref = $recordSwitcherAssets::getStyleHref(
        'record-switcher',
        package: 'capell-record-switcher',
    );
    $recordSwitcherScriptSrc = $recordSwitcherAssets::getAlpineComponentSrc(
        'record-switcher',
        'capell-record-switcher',
    );
@endphp

<div
    x-ignore
    x-load-css="[@js($recordSwitcherStyleHref)]"
    x-load
    x-load-src="{{ $recordSwitcherScriptSrc }}"
    x-data="selectChangerComponent({
                getResultsUsing: async (search) => await $wire.getOptions(search),
                hasDynamicSearchResults: true,
                label: @js($label),
                loadingMessage:
                    '{{ __('filament-forms::components.select.loading_message') }}',
                noSearchResultsMessage:
                    '{{ __('filament-forms::components.select.no_search_results_message') }}',
                optionsLimit: @js($limit_results),
                placeholder: '{{ __('filament-forms::components.select.placeholder') }}',
                searchPrompt:
                    '{{ __('filament-forms::components.select.search_prompt') }}',
                searchingMessage:
                    '{{ __('filament-forms::components.select.searching_message') }}',
                state: @js($value),
                updateSelected: (value) => (window.location.href = value),
            })"
    wire:ignore
    x-on:keydown.esc="select.dropdown.isActive && $event.stopPropagation()"
    class="filament-record-switcher"
>
    @if (! empty($icon))
        <x-dynamic-component
            :component="$icon"
            :x-tooltip.raw="$icon_name ?? ''"
            class="inline-block h-6 w-6 stroke-current text-gray-500 dark:text-gray-300"
        />
    @endif

    <select
        x-ref="input"
        class="pointer-events-none appearance-none border-none bg-transparent !bg-none p-0 text-2xl font-bold text-gray-950 sm:text-3xl dark:text-white"
    >
        <option value="{{ $value }}">{{ $label }}</option>
    </select>
</div>
