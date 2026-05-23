<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Http\Controllers\OAuth;

use Capell\ShopifyCommerce\Actions\OAuth\BuildShopifyAuthorizeUrlAction;
use Capell\ShopifyCommerce\Actions\OAuth\CreateShopifyOAuthStateAction;
use Capell\ShopifyCommerce\Actions\OAuth\ValidateShopifyShopDomainAction;
use Capell\ShopifyCommerce\Filament\Pages\ShopifyConnectionPage;
use Capell\ShopifyCommerce\Settings\ShopifyCommerceSettings;
use Capell\ShopifyCommerce\Support\ShopifySiteContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ShopifyInstallController
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(ShopifyConnectionPage::canAccess(), 403);

        $shop = $request->query('shop');
        abort_unless(ValidateShopifyShopDomainAction::run($shop), 422, __('capell-shopify-commerce::capell-shopify-commerce.connection.invalid_shop'));

        $clientId = config('capell-shopify-commerce.client_id');
        $clientSecret = config('capell-shopify-commerce.client_secret');

        if (! is_string($clientId) || trim($clientId) === '' || ! is_string($clientSecret) || trim($clientSecret) === '') {
            return to_route('filament.admin.pages.shopify-commerce')
                ->withErrors([__('capell-shopify-commerce::capell-shopify-commerce.connection.not_configured')]);
        }

        $user = $request->user();
        abort_unless($user !== null, 403);

        $siteId = ShopifySiteContext::selectedSiteId($user, $request->query('site_id'));

        if ($request->query('site_id') !== null) {
            abort_unless($siteId !== null, 403);
        }

        /** @var string $shop */
        $state = CreateShopifyOAuthStateAction::run($shop, $user, $siteId);

        return redirect()->away(BuildShopifyAuthorizeUrlAction::run(
            shopDomain: $shop,
            scopes: $this->defaultScopes(),
            state: $state,
            redirectUri: route('capell-shopify-commerce.oauth.callback'),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function defaultScopes(): array
    {
        if (app()->bound(ShopifyCommerceSettings::class)) {
            $settings = resolve(ShopifyCommerceSettings::class);

            return $settings->default_scopes;
        }

        $scopes = config('capell-shopify-commerce.default_scopes', ['read_products']);

        return is_array($scopes) ? array_values(array_filter($scopes, static fn (mixed $scope): bool => is_string($scope) && $scope !== '')) : ['read_products'];
    }
}
