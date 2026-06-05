<x-filament-widgets::widget class="capell-packages-installed">
    <x-filament::section
        heading="{{ __('capell-diagnostics::package.packages_installed_heading') }}"
    >
        @if ($this->data->packages->count() === 0)
            <p class="text-sm text-gray-400 dark:text-gray-500">
                {{ __('capell-diagnostics::package.packages_installed_empty_prefix') }}
                <span class="font-mono">vendor/composer/installed.json</span>
                .
            </p>
        @else
            @if ($this->data->healthCheckDeclaredCount > 0)
                <div
                    @class([
                        'mb-4 rounded border px-3 py-2 text-sm',
                        'border-success-200 bg-success-50 text-success-800 dark:border-success-900/50 dark:bg-success-950/30 dark:text-success-300' => $this->data->healthCheckStubCount === 0 && $this->data->healthCheckBrokenCount === 0,
                        'border-warning-200 bg-warning-50 text-warning-800 dark:border-warning-900/50 dark:bg-warning-950/30 dark:text-warning-300' => $this->data->healthCheckStubCount > 0 && $this->data->healthCheckBrokenCount === 0,
                        'border-danger-200 bg-danger-50 text-danger-800 dark:border-danger-900/50 dark:bg-danger-950/30 dark:text-danger-300' => $this->data->healthCheckBrokenCount > 0,
                    ])
                >
                    <div class="font-medium">
                        {{
                            __('capell-diagnostics::package.packages_installed_suite_health_summary', [
                                'implemented' => $this->data->healthCheckImplementedCount,
                                'declared' => $this->data->healthCheckDeclaredCount,
                            ])
                        }}
                    </div>

                    <div class="text-xs opacity-80">
                        @if ($this->data->healthCheckStubCount > 0 || $this->data->healthCheckBrokenCount > 0)
                            {{
                                __('capell-diagnostics::package.packages_installed_suite_health_findings', [
                                    'stub' => $this->data->healthCheckStubCount,
                                    'broken' => $this->data->healthCheckBrokenCount,
                                ])
                            }}
                        @else
                            {{ __('capell-diagnostics::package.packages_installed_suite_health_clean') }}
                        @endif
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-200 dark:border-gray-700"
                        >
                            <th
                                class="pr-4 pb-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-diagnostics::package.packages_installed_package') }}
                            </th>
                            <th
                                class="pr-4 pb-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-diagnostics::package.packages_installed_version') }}
                            </th>
                            <th
                                class="pr-4 pb-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-diagnostics::package.packages_installed_bundle') }}
                            </th>
                            <th
                                class="pr-4 pb-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-diagnostics::package.packages_installed_config_published') }}
                            </th>
                            <th
                                class="pr-4 pb-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-diagnostics::package.packages_installed_health') }}
                            </th>
                            <th
                                class="pr-4 pb-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-diagnostics::package.packages_installed_commands') }}
                            </th>
                            <th
                                class="pb-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-diagnostics::package.packages_installed_docs') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        @foreach ($this->data->packages as $package)
                            <tr class="py-1">
                                <td class="py-2 pr-4">
                                    <div
                                        class="font-medium text-gray-800 dark:text-gray-200"
                                    >
                                        {{ $package->displayName ?? $package->name }}
                                    </div>
                                    <div
                                        class="font-mono text-xs text-gray-400 dark:text-gray-500"
                                    >
                                        {{ $package->composerName }}
                                    </div>
                                </td>
                                <td class="py-2 pr-4">
                                    <span
                                        class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                    >
                                        {{ $package->version }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4">
                                    @if ($package->bundle !== null)
                                        <span
                                            class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                        >
                                            {{ $package->bundle }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">
                                            —
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    @if ($package->configPublished)
                                        <span
                                            class="bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400 rounded px-1.5 py-0.5 text-xs"
                                        >
                                            {{ __('capell-diagnostics::package.packages_installed_published') }}
                                        </span>
                                    @else
                                        <span
                                            class="bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400 rounded px-1.5 py-0.5 text-xs"
                                        >
                                            {{ __('capell-diagnostics::package.packages_installed_not_published') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    @if ($package->healthCheckDeclaredCount > 0)
                                        <div class="space-y-1">
                                            <span
                                                @class([
                                                    'rounded px-1.5 py-0.5 text-xs',
                                                    'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' => $package->healthCheckStubCount === 0 && $package->healthCheckBrokenCount === 0,
                                                    'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' => $package->healthCheckStubCount > 0 && $package->healthCheckBrokenCount === 0,
                                                    'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' => $package->healthCheckBrokenCount > 0,
                                                ])
                                            >
                                                {{
                                                    __('capell-diagnostics::package.packages_installed_health_implemented', [
                                                        'implemented' => $package->healthCheckImplementedCount,
                                                        'declared' => $package->healthCheckDeclaredCount,
                                                    ])
                                                }}
                                            </span>

                                            @if ($package->healthCheckStubCount > 0 || $package->healthCheckBrokenCount > 0)
                                                <div
                                                    class="text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        __('capell-diagnostics::package.packages_installed_health_stub_broken', [
                                                            'stub' => $package->healthCheckStubCount,
                                                            'broken' => $package->healthCheckBrokenCount,
                                                        ])
                                                    }}
                                                </div>
                                            @endif
                                        </div>
                                    @elseif ($package->doctorCommand !== null)
                                        <span
                                            class="bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400 rounded px-1.5 py-0.5 text-xs"
                                        >
                                            {{ $package->doctorCommand }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">
                                            —
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    @if ($package->installCommand !== null || $package->doctorCommand !== null)
                                        <div
                                            class="space-y-1 font-mono text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            @if ($package->installCommand !== null)
                                                <div>
                                                    {{ $package->installCommand }}
                                                </div>
                                            @endif

                                            @if ($package->doctorCommand !== null)
                                                <div>
                                                    {{ $package->doctorCommand }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">
                                            —
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2">
                                    @if ($package->docsUrl !== null)
                                        <a
                                            href="{{ $package->docsUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-xs underline"
                                        >
                                            {{ __('capell-diagnostics::package.packages_installed_readme') }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">
                                            —
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
