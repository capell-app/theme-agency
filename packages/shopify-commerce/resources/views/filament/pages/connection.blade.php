<x-filament-panels::page>
    <div class="space-y-6">
        @php($connection = $this->getManageableConnection())

        @if (session('status'))
            <div
                role="status"
                class="bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-300 dark:ring-success-400/20 rounded-lg p-4 text-sm ring-1"
            >
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any() && ! $errors->has('shop'))
            <div
                role="alert"
                class="bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-300 dark:ring-danger-400/20 rounded-lg p-4 text-sm ring-1"
            >
                {{ $errors->first() }}
            </div>
        @endif

        @if (count($this->siteOptions()) > 1)
            <label class="block space-y-2">
                <span class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.site_label') }}
                </span>
                <select
                    wire:model.live="selectedSiteId"
                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-white/10 dark:bg-white/5"
                >
                    @foreach ($this->siteOptions() as $siteId => $siteName)
                        <option value="{{ $siteId }}">{{ $siteName }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        @if (! $connection)
            <form wire:submit="connect" class="space-y-4">
                <label class="block space-y-2">
                    <span
                        class="text-sm font-medium text-gray-950 dark:text-white"
                    >
                        {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.shop_label') }}
                    </span>
                    <input
                        type="text"
                        wire:model="shop"
                        placeholder="{{ __('capell-shopify-commerce::capell-shopify-commerce.connection.shop_placeholder') }}"
                        aria-invalid="{{ $errors->has('shop') ? 'true' : 'false' }}"
                        @if ($errors->has('shop')) aria-describedby="shop-error" @endif
                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-white/10 dark:bg-white/5"
                    />
                </label>

                @error('shop')
                    <p
                        id="shop-error"
                        class="text-danger-600 dark:text-danger-400 text-sm"
                    >
                        {{ $message }}
                    </p>
                @enderror

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="connect"
                    class="fi-btn fi-btn-size-md fi-color-primary fi-btn-color-primary"
                >
                    {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.connect') }}
                </button>
            </form>
        @else
            <div
                class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                >
                    <div class="space-y-2">
                        <h3
                            class="fi-section-header-heading text-base font-semibold"
                        >
                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.connected_store') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $connection->shop_domain }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.granted_scopes') }}:
                            {{ implode(', ', $connection->scopes ?? []) }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.last_sync') }}:
                            {{ $connection->last_synced_at?->toDayDateTimeString() ?? __('capell-shopify-commerce::capell-shopify-commerce.connection.never_synced') }}
                        </p>
                        @if ($connection->status === ShopifyConnectionStatus::Error)
                            <p
                                role="alert"
                                class="text-danger-600 dark:text-danger-400 text-sm"
                            >
                                {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.sync_error') }}
                                @if (filled($connection->last_sync_error))
                                    {{ Str::limit(strip_tags((string) $connection->last_sync_error), 140) }}
                                @endif
                            </p>
                        @endif

                        @if ($this->isSyncBusy($connection))
                            <p
                                role="status"
                                class="text-primary-600 dark:text-primary-400 text-sm"
                            >
                                {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.sync_running') }}
                            </p>
                        @endif
                    </div>

                    <div class="flex gap-3">
                        <button
                            wire:click="syncNow"
                            wire:loading.attr="disabled"
                            wire:target="syncNow"
                            @disabled($this->isSyncBusy($connection))
                            class="fi-btn fi-btn-size-sm fi-color-primary fi-btn-color-primary"
                        >
                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.sync_now') }}
                        </button>
                        <button
                            wire:click="disconnect"
                            wire:loading.attr="disabled"
                            wire:target="disconnect"
                            wire:confirm="{{ __('capell-shopify-commerce::capell-shopify-commerce.connection.disconnect_confirm') }}"
                            class="fi-btn fi-btn-size-sm fi-color-danger fi-btn-color-danger"
                        >
                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.disconnect') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <form
                    wire:submit="search"
                    class="flex flex-col gap-3 md:flex-row"
                >
                    <label class="flex-1 space-y-2">
                        <span
                            class="text-sm font-medium text-gray-950 dark:text-white"
                        >
                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.search_label') }}
                        </span>
                        <input
                            type="text"
                            wire:model="searchTerm"
                            placeholder="{{ __('capell-shopify-commerce::capell-shopify-commerce.connection.search_placeholder') }}"
                            class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-white/10 dark:bg-white/5"
                        />
                    </label>

                    <div class="flex items-end">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="search"
                            class="fi-btn fi-btn-size-md fi-color-primary fi-btn-color-primary"
                        >
                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.search') }}
                        </button>
                    </div>
                </form>

                <p
                    role="status"
                    aria-live="polite"
                    class="text-sm text-gray-500 dark:text-gray-400"
                >
                    {{ trans_choice('capell-shopify-commerce::capell-shopify-commerce.connection.result_count', $searchResults->count(), ['count' => $searchResults->count()]) }}
                </p>

                <div>
                    @if ($searchResults->isNotEmpty())
                        <div
                            class="overflow-x-auto rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10"
                        >
                            <table class="w-full text-left text-sm">
                                <thead
                                    class="bg-gray-50 text-gray-600 dark:bg-white/5 dark:text-gray-300"
                                >
                                    <tr>
                                        <th
                                            scope="col"
                                            class="px-4 py-3 font-medium"
                                        >
                                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.product') }}
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-4 py-3 font-medium"
                                        >
                                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.status') }}
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-4 py-3 font-medium"
                                        >
                                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.variants') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900"
                                >
                                    @foreach ($searchResults as $product)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div
                                                    class="font-medium text-gray-950 dark:text-white"
                                                >
                                                    {{ $product->title }}
                                                </div>
                                                <div
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ $product->handle }}
                                                </div>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-gray-600 dark:text-gray-300"
                                            >
                                                {{ $product->status }}
                                            </td>
                                            <td
                                                class="px-4 py-3 text-gray-600 dark:text-gray-300"
                                            >
                                                {{ $product->variants_count ?? $product->variants()->count() }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif ($searchTerm !== '')
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.no_results') }}
                        </p>
                    @elseif (! $this->hasCachedProducts())
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('capell-shopify-commerce::capell-shopify-commerce.connection.empty_catalog') }}
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
