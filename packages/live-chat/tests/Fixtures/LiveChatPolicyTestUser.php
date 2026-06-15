<?php

declare(strict_types=1);

namespace Capell\LiveChat\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;

final class LiveChatPolicyTestUser extends User
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
        private readonly bool $isGlobal = false,
    ) {
        parent::__construct();
    }

    public function isGlobalAdmin(): bool
    {
        return $this->isGlobal;
    }

    public function checkPermissionTo(mixed $permission, mixed $guardName = null): bool
    {
        unset($guardName);

        if (! is_scalar($permission)) {
            return false;
        }

        return in_array((string) $permission, $this->permissions, true);
    }

    /**
     * @return Collection<int, int>
     */
    public function getAssignedSiteIds(): Collection
    {
        return collect($this->assignedSiteIds);
    }
}
