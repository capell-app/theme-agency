<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;

final class PackagePolicyCoverageUser extends User
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    /**
     * @param  list<int>  $assignedSiteIds
     */
    public function __construct(
        private readonly bool $global = false,
        private readonly array $assignedSiteIds = [],
        private readonly bool $permissionResult = true,
    ) {
        parent::__construct();
    }

    public function isGlobalAdmin(): bool
    {
        return $this->global;
    }

    /**
     * @return Collection<int, int>
     */
    public function getAssignedSiteIds(): Collection
    {
        return collect($this->assignedSiteIds);
    }

    public function checkPermissionTo(mixed $permission, ?string $guardName = null): bool
    {
        unset($permission, $guardName);

        return $this->permissionResult;
    }
}
