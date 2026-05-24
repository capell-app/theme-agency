<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\InstallShopifyCommercePermissionsAction;
use Capell\ShopifyCommerce\Support\Permissions\ShopifyCommercePermission;
use Spatie\Permission\Models\Permission;

it('installs the manage shopify commerce permission', function (): void {
    InstallShopifyCommercePermissionsAction::run();

    expect(Permission::query()
        ->where('name', ShopifyCommercePermission::MANAGE)
        ->where('guard_name', 'web')
        ->exists())->toBeTrue();
});
