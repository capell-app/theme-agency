@if ($this->checks->isEmpty())
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('capell-seo-suite::generic.no_checks') }}
    </p>
@else
    <div
        @class([
            'grid grid-cols-1 gap-2',
            'sm:grid-cols-2' => $this->checks->count() > 1,
            'lg:grid-cols-3' => $this->checks->count() > 2,
            'xl:grid-cols-4' => $this->checks->count() > 3,
        ])
    >
        @foreach ($this->checks as $check)
            @php
                $statusLabel = $check->pass
                    ? __('capell-seo-suite::generic.seo_audit_status_passed')
                    : __('capell-seo-suite::generic.seo_audit_status_warning');
            @endphp

            <div
                class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700"
            >
                <div class="mt-0.5 shrink-0">
                    @if ($check->pass)
                        <x-filament::icon
                            icon="heroicon-o-check-circle"
                            class="text-success-500 dark:text-success-400 h-5 w-5"
                            aria-hidden="true"
                        />
                    @else
                        <x-filament::icon
                            icon="heroicon-o-x-circle"
                            class="text-warning-500 dark:text-warning-400 h-5 w-5"
                            aria-hidden="true"
                        />
                    @endif
                    <span class="sr-only">{{ $statusLabel }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p
                            class="text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ $check->label }}
                        </p>
                        @if ($check->tooltip)
                            <div
                                x-tooltip.raw="{{ $check->tooltip }}"
                                class="shrink-0 cursor-help"
                            >
                                <x-filament::icon
                                    icon="heroicon-o-question-mark-circle"
                                    class="h-4 w-4 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400"
                                />
                            </div>
                        @endif
                    </div>
                    @if ($check->detail)
                        <p
                            class="mt-0.5 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ $check->detail }}
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
