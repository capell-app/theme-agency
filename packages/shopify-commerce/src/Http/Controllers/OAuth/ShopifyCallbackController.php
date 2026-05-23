<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Http\Controllers\OAuth;

use Capell\ShopifyCommerce\Actions\OAuth\ConnectShopifyStoreAction;
use Capell\ShopifyCommerce\Actions\OAuth\ExchangeShopifyAuthorizationCodeAction;
use Capell\ShopifyCommerce\Actions\OAuth\ValidateShopifyHmacAction;
use Capell\ShopifyCommerce\Actions\OAuth\ValidateShopifyOAuthStateAction;
use Capell\ShopifyCommerce\Actions\OAuth\ValidateShopifyShopDomainAction;
use Capell\ShopifyCommerce\Data\ShopifyCallbackQueryData;
use Capell\ShopifyCommerce\Exceptions\OAuthCallbackFailedException;
use Capell\ShopifyCommerce\Filament\Pages\ShopifyConnectionPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ShopifyCallbackController
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(ShopifyConnectionPage::canAccess(), 403);

        try {
            $query = ShopifyCallbackQueryData::from($request);
            $user = $request->user();

            abort_unless($user !== null, 403);

            throw_if(ValidateShopifyShopDomainAction::run($query->shop) !== true, OAuthCallbackFailedException::class, 'invalid_shop');

            throw_if(ValidateShopifyHmacAction::run($request->query(), config('capell-shopify-commerce.client_secret')) !== true, OAuthCallbackFailedException::class, 'invalid_hmac');

            $state = ValidateShopifyOAuthStateAction::run($query->state, $query->shop, $user);

            throw_if($state === null, OAuthCallbackFailedException::class, 'invalid_state');

            $tokenData = ExchangeShopifyAuthorizationCodeAction::run($query->shop, $query->code);

            ConnectShopifyStoreAction::run($query->shop, $tokenData, $user, is_numeric($state->site_id) ? (int) $state->site_id : null);

            return to_route('filament.admin.pages.shopify-commerce')
                ->with('status', __('capell-shopify-commerce::capell-shopify-commerce.connection.oauth_connected'));
        } catch (OAuthCallbackFailedException $exception) {
            Log::warning('Shopify OAuth failed', ['reason' => $exception->getMessage()]);

            $message = $exception->getMessage() === 'invalid_state'
                ? __('capell-shopify-commerce::capell-shopify-commerce.connection.oauth_invalid_state')
                : __('capell-shopify-commerce::capell-shopify-commerce.connection.oauth_failed');

            return to_route('filament.admin.pages.shopify-commerce')->withErrors([$message]);
        } catch (Throwable $exception) {
            Log::warning('Shopify OAuth failed', [
                'reason' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return to_route('filament.admin.pages.shopify-commerce')
                ->withErrors([__('capell-shopify-commerce::capell-shopify-commerce.connection.oauth_failed')]);
        }
    }
}
