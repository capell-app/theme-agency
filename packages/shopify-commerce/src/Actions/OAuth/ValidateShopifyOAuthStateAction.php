<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Capell\ShopifyCommerce\Models\ShopifyOAuthState;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

final class ValidateShopifyOAuthStateAction
{
    use AsAction;

    public function handle(mixed $state, string $shopDomain, Authenticatable $user): ?ShopifyOAuthState
    {
        if (! is_string($state) || $state === '') {
            return null;
        }

        $userId = $user->getAuthIdentifier();

        if ($userId === null) {
            return null;
        }

        return DB::transaction(function () use ($state, $shopDomain, $userId): ?ShopifyOAuthState {
            $stateModel = ShopifyOAuthState::query()
                ->where('nonce', $state)
                ->where('shop_domain', $shopDomain)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (! $stateModel instanceof ShopifyOAuthState) {
                return null;
            }

            $isValid = $stateModel->expires_at !== null && $stateModel->expires_at->isFuture();
            $stateModel->delete();

            return $isValid ? $stateModel : null;
        });
    }
}
