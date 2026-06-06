<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Filament\Pages;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\ShopifyCommerce\Actions\Catalog\SearchShopifyProductsAction;
use Capell\ShopifyCommerce\Actions\Catalog\SyncShopifyProductsAction;
use Capell\ShopifyCommerce\Actions\OAuth\DisconnectShopifyStoreAction;
use Capell\ShopifyCommerce\Actions\OAuth\ValidateShopifyShopDomainAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Support\Permissions\ShopifyCommercePermission;
use Capell\ShopifyCommerce\Support\ShopifySiteContext;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Override;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

final class ShopifyConnectionPage extends Page
{
    public string $shop = '';

    public string $searchTerm = '';

    public ?int $selectedSiteId = null;

    /** @var EloquentCollection<int, ShopifyProduct> */
    public EloquentCollection $searchResults;

    private ?ShopifyConnection $manageableConnection = null;

    /** @var array<int, string>|null */
    private ?array $siteOptions = null;

    protected string $view = 'capell-shopify-commerce::filament.pages.connection';

    protected static ?string $slug = 'shopify-commerce';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-shopify-commerce::capell-shopify-commerce.navigation.label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-shopify-commerce::capell-shopify-commerce.navigation.group');
    }

    #[Override]
    public static function getNavigationSort(): int
    {
        return 80;
    }

    #[Override]
    public static function getNavigationIcon(): BackedEnum
    {
        return Heroicon::OutlinedShoppingBag;
    }

    #[Override]
    public static function canAccess(): bool
    {
        if (! CapellCore::isPackageInstalled('capell-app/shopify-commerce') || config('capell-shopify-commerce.enabled', true) !== true) {
            return false;
        }

        if (Gate::allows(ShopifyCommercePermission::MANAGE)) {
            return true;
        }

        return auth()->user()?->can(ShopifyCommercePermission::MANAGE) === true;
    }

    public function mount(): void
    {
        $this->searchResults = new EloquentCollection;
        $this->selectedSiteId = ShopifySiteContext::selectedSiteId(auth()->user());
        $this->loadCachedProductPreview();
    }

    #[Override]
    public function getTitle(): string
    {
        return __('capell-shopify-commerce::capell-shopify-commerce.connection.title');
    }

    public function connect(): ?RedirectResponse
    {
        throw_unless(self::canAccess(), HttpException::class, 403);

        $this->resetResolvedConnectionState();

        if (ValidateShopifyShopDomainAction::run($this->shop) !== true) {
            $this->addError('shop', __('capell-shopify-commerce::capell-shopify-commerce.connection.invalid_shop'));

            return null;
        }

        $siteId = ShopifySiteContext::selectedSiteId(auth()->user(), $this->selectedSiteId);

        if ($siteId === null) {
            $this->addError('selectedSiteId', __('capell-shopify-commerce::capell-shopify-commerce.connection.invalid_site'));

            return null;
        }

        $this->selectedSiteId = $siteId;

        return to_route('capell-shopify-commerce.oauth.install', [
            'shop' => $this->shop,
            'site_id' => $this->selectedSiteId,
        ]);
    }

    public function syncNow(): void
    {
        throw_unless(self::canAccess(), HttpException::class, 403);

        $this->resetResolvedConnectionState();

        $connection = $this->getManageableConnection();

        if (! $connection instanceof ShopifyConnection) {
            return;
        }

        if (in_array($connection->sync_status, ['queued', 'running', 'importing'], true)) {
            return;
        }

        $connection->forceFill([
            'sync_status' => 'queued',
            'last_sync_queued_at' => now(),
        ])->save();

        SyncShopifyProductsAction::dispatch((int) $connection->getKey());

        Notification::make()
            ->title(__('capell-shopify-commerce::capell-shopify-commerce.connection.sync_started'))
            ->success()
            ->send();
    }

    public function disconnect(): void
    {
        throw_unless(self::canAccess(), HttpException::class, 403);

        $this->resetResolvedConnectionState();

        $connection = $this->getManageableConnection();

        if (! $connection instanceof ShopifyConnection) {
            return;
        }

        DisconnectShopifyStoreAction::run($connection);
        $this->resetResolvedConnectionState();

        Notification::make()
            ->title(__('capell-shopify-commerce::capell-shopify-commerce.connection.disconnected'))
            ->success()
            ->send();
    }

    public function search(): void
    {
        throw_unless(self::canAccess(), HttpException::class, 403);

        $this->resetResolvedConnectionState();

        $connection = $this->getManageableConnection();

        if (! $connection instanceof ShopifyConnection) {
            return;
        }

        try {
            $this->searchResults = SearchShopifyProductsAction::run($this->searchTerm, 20, $connection);
        } catch (Throwable) {
            Notification::make()
                ->title(__('capell-shopify-commerce::capell-shopify-commerce.connection.search_failed'))
                ->danger()
                ->send();
        }
    }

    public function hasCachedProducts(): bool
    {
        $connection = $this->getManageableConnection();

        if (! $connection instanceof ShopifyConnection) {
            return false;
        }

        return ShopifyProduct::query()
            ->where('connection_id', $connection->getKey())
            ->exists();
    }

    public function getManageableConnection(): ?ShopifyConnection
    {
        if ($this->manageableConnection instanceof ShopifyConnection) {
            return $this->manageableConnection;
        }

        if (! Schema::hasTable('shopify_connections')) {
            return null;
        }

        $actor = auth()->user();

        if (! $actor instanceof Authenticatable) {
            return null;
        }

        if ($this->selectedSiteId !== null && ! ShopifySiteContext::actorCanUseSiteId($actor, $this->selectedSiteId)) {
            $this->selectedSiteId = ShopifySiteContext::selectedSiteId($actor);

            return null;
        }

        $this->selectedSiteId = ShopifySiteContext::selectedSiteId($actor, $this->selectedSiteId);

        $connection = ShopifyConnection::query()
            ->when($this->selectedSiteId !== null, fn (Builder $query): Builder => $query->where('site_id', $this->selectedSiteId))
            ->whereIn('status', [
                ShopifyConnectionStatus::Active->value,
                ShopifyConnectionStatus::Connecting->value,
                ShopifyConnectionStatus::Error->value,
            ])
            ->latest('id')
            ->first();

        $this->manageableConnection = $connection instanceof ShopifyConnection ? $connection : null;

        return $this->manageableConnection;
    }

    /**
     * @return array<int, string>
     */
    public function siteOptions(): array
    {
        if (is_array($this->siteOptions)) {
            return $this->siteOptions;
        }

        $this->siteOptions = ShopifySiteContext::options(auth()->user());

        return $this->siteOptions;
    }

    public function isSyncBusy(?ShopifyConnection $connection): bool
    {
        return $connection instanceof ShopifyConnection && in_array($connection->sync_status, ['queued', 'running', 'importing'], true);
    }

    public function getActiveConnection(): ?ShopifyConnection
    {
        $connection = $this->getManageableConnection();

        return $connection?->status === ShopifyConnectionStatus::Active ? $connection : null;
    }

    public function updatedSelectedSiteId(): void
    {
        $this->resetResolvedConnectionState();
        $this->loadCachedProductPreview();
    }

    private function loadCachedProductPreview(): void
    {
        $this->searchResults = new EloquentCollection;

        $connection = $this->getManageableConnection();

        if (! $connection instanceof ShopifyConnection) {
            return;
        }

        $this->searchResults = ShopifyProduct::query()
            ->withCount('variants')
            ->where('connection_id', $connection->getKey())
            ->latest('synced_at')
            ->latest('id')
            ->limit(20)
            ->get();
    }

    private function resetResolvedConnectionState(): void
    {
        $this->manageableConnection = null;
        $this->siteOptions = null;
    }
}
