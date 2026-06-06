<x-filament-panels::page>
    <div class="space-y-6">
        @if ($this::canManageConnections())
            <div class="grid gap-4 md:grid-cols-3">
                <label
                    class="grid gap-2 text-sm font-medium text-gray-950 dark:text-white"
                >
                    {{ __('capell-deployments::plugins.deployment_connection.repo_owner_label') }}
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="repoOwner"
                        class="fi-input focus:ring-primary-600 dark:focus:ring-primary-500 block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 outline-none placeholder:text-gray-400 focus:ring-2 sm:text-sm dark:bg-white/5 dark:text-white dark:ring-white/20"
                    />
                </label>
                <label
                    class="grid gap-2 text-sm font-medium text-gray-950 dark:text-white"
                >
                    {{ __('capell-deployments::plugins.deployment_connection.repo_name_label') }}
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="repoName"
                        class="fi-input focus:ring-primary-600 dark:focus:ring-primary-500 block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 outline-none placeholder:text-gray-400 focus:ring-2 sm:text-sm dark:bg-white/5 dark:text-white dark:ring-white/20"
                    />
                </label>
                <label
                    class="grid gap-2 text-sm font-medium text-gray-950 dark:text-white"
                >
                    {{ __('capell-deployments::plugins.deployment_connection.install_policy_label') }}
                    <select
                        wire:model.live="installPolicy"
                        class="fi-select-input focus:ring-primary-600 dark:focus:ring-primary-500 block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 outline-none focus:ring-2 sm:text-sm dark:bg-white/5 dark:text-white dark:ring-white/20"
                    >
                        @foreach ($this->getInstallPolicyOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="flex flex-wrap gap-4">
                @foreach ($this->getConnectProviders() as $connectProvider)
                    @if ($connectProvider['url'])
                        <a
                            href="{{ $connectProvider['url'] }}"
                            class="fi-btn fi-btn-size-md fi-color-primary fi-btn-color-primary"
                        >
                            {{ $connectProvider['label'] }}
                        </a>
                    @else
                        <button
                            type="button"
                            disabled
                            title="{{ $connectProvider['disabledReason'] }}"
                            class="fi-btn fi-btn-size-md fi-color-gray fi-btn-color-gray opacity-60"
                        >
                            {{ $connectProvider['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Active connections --}}
        @foreach ($this->getConnections() as $connection)
            <div
                class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3
                            class="fi-section-header-heading text-base font-semibold"
                        >
                            {{ $connection->provider->getLabel() }}:
                            {{ $connection->repoCoordinate() }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $connection->install_policy->getLabel() }}
                        </p>
                    </div>
                    @if ($this::canManageConnections())
                        <button
                            wire:click="disconnect({{ $connection->id }})"
                            wire:confirm="{{ __('capell-deployments::plugins.deployment_connection.disconnect_confirm') }}"
                            class="fi-btn fi-btn-size-sm fi-color-danger fi-btn-color-danger"
                        >
                            {{ __('capell-deployments::plugins.deployment_connection.disconnect') }}
                        </button>
                    @endif
                </div>

                @php($recentPublications = $this->getRecentPublications($connection))

                <div class="mt-6 border-t border-gray-950/10 pt-4 dark:border-white/10">
                    <h4 class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ __('capell-deployments::plugins.deployment_connection.recent_publishes') }}
                    </h4>

                    @if ($recentPublications->isEmpty())
                        <p class="mt-2 text-sm text-gray-500">
                            {{ __('capell-deployments::plugins.deployment_connection.no_recent_publishes') }}
                        </p>
                    @else
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="py-2 pr-4 font-medium">
                                            {{ __('capell-deployments::plugins.deployment_connection.package_column') }}
                                        </th>
                                        <th class="py-2 pr-4 font-medium">
                                            {{ __('capell-deployments::plugins.deployment_connection.status_column') }}
                                        </th>
                                        <th class="py-2 pr-4 font-medium">
                                            {{ __('capell-deployments::plugins.deployment_connection.reference_column') }}
                                        </th>
                                        <th class="py-2 font-medium">
                                            {{ __('capell-deployments::plugins.deployment_connection.published_column') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-950/10 dark:divide-white/10">
                                    @foreach ($recentPublications as $publication)
                                        <tr>
                                            <td class="py-3 pr-4 font-medium text-gray-950 dark:text-white">
                                                {{ $publication->composer_package }}
                                                @if ($publication->constraint)
                                                    <span class="text-gray-500">
                                                        {{ $publication->constraint }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">
                                                {{ $publication->statusLabel() }}
                                            </td>
                                            <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">
                                                @if ($publication->pull_request_url)
                                                    <a
                                                        href="{{ $publication->pull_request_url }}"
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        class="text-primary-600 hover:underline dark:text-primary-400"
                                                    >
                                                        {{ __('capell-deployments::plugins.deployment_connection.pull_request_reference', ['id' => $publication->pull_request_id ?? '']) }}
                                                    </a>
                                                @elseif ($publication->commit_sha)
                                                    <span class="font-mono text-xs">
                                                        {{ Str::limit($publication->commit_sha, 12, '') }}
                                                    </span>
                                                @elseif ($publication->branch_name)
                                                    <span class="font-mono text-xs">
                                                        {{ $publication->branch_name }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">
                                                        {{ __('capell-deployments::plugins.deployment_connection.no_reference') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 text-gray-600 dark:text-gray-300">
                                                {{ $publication->created_at?->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if (count($this->getConnections()) === 0)
            <p class="text-sm text-gray-500">
                {{ __('capell-deployments::plugins.deployment_connection.none_connected') }}
            </p>
        @endif
    </div>
</x-filament-panels::page>
