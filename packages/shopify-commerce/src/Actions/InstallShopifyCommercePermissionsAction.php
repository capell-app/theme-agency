<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions;

use Capell\ShopifyCommerce\Support\Permissions\ShopifyCommercePermission;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class InstallShopifyCommercePermissionsAction
{
    use AsObject;

    public function handle(?string $guardName = null): void
    {
        $permissionsTable = (string) config('permission.table_names.permissions', 'permissions');

        if (! Schema::hasTable($permissionsTable)) {
            return;
        }

        Permission::query()->firstOrCreate([
            'name' => ShopifyCommercePermission::MANAGE,
            'guard_name' => $guardName ?? config('auth.defaults.guard', 'web'),
        ]);

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
