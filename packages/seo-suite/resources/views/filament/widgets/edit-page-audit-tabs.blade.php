<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="selectTab('seo')"
                @class([
                    'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition',
                    'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-300 ring-1' => $activeTab === 'seo',
                    'text-gray-600 hover:bg-gray-50 hover:text-gray-950 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' => $activeTab !== 'seo',
                ])
            >
                <x-filament::icon
                    icon="heroicon-o-magnifying-glass"
                    class="h-4 w-4"
                />
                <span>{{ __('capell-seo-suite::generic.seo_audit') }}</span>
                @livewire('capell-seo-suite.edit-page-seo-audit-badge', ['recordKey' => $recordKey, 'lazy' => true], key('seo-audit-badge-' . ($recordKey ?? 'missing')))
            </button>

            <button
                type="button"
                wire:click="selectTab('pagespeed')"
                @class([
                    'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition',
                    'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-300 ring-1' => $activeTab === 'pagespeed',
                    'text-gray-600 hover:bg-gray-50 hover:text-gray-950 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' => $activeTab !== 'pagespeed',
                ])
            >
                <x-filament::icon
                    icon="heroicon-o-bolt"
                    class="h-4 w-4"
                />
                <span>
                    {{ __('capell-seo-suite::generic.pagespeed_audit') }}
                </span>
                @livewire('capell-seo-suite.edit-page-pagespeed-audit-badge', ['recordKey' => $recordKey, 'lazy' => true], key('pagespeed-audit-badge-' . ($recordKey ?? 'missing')))
            </button>
        </div>

        <div class="mt-4">
            @if ($activeTab === 'seo')
                @livewire('capell-seo-suite.edit-page-seo-audit', ['recordKey' => $recordKey, 'embedded' => true, 'lazy' => true], key('seo-audit-tab-' . ($recordKey ?? 'missing')))
            @else
                @livewire('capell-seo-suite.edit-page-pagespeed-audit', ['recordKey' => $recordKey, 'embedded' => true, 'lazy' => true], key('pagespeed-audit-tab-' . ($recordKey ?? 'missing')))
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
