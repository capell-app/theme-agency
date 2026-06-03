<?php

declare(strict_types=1);

namespace Capell\PublicActions\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;

final class PublicActionPolicyTestUser extends User
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    /**
     * @param  list<string>  $permissions
     * @param  list<int>  $assignedSiteIds
     */
    public function __construct(
        private readonly array $permissions = [],
        private readonly array $assignedSiteIds = [],
        private readonly bool $superAdmin = false,
    ) {
        parent::__construct();
    }

    public function checkPermissionTo(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function hasRole(string $role): bool
    {
        return $this->superAdmin && $role === config('capell.roles.super_admin', 'super_admin');
    }

    /**
     * @return Collection<int, int>
     */
    public function getAssignedSiteIds(): Collection
    {
        return collect($this->assignedSiteIds);
    }
}
