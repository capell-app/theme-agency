<?php

declare(strict_types=1);

namespace Capell\AiCreator\Tests\Fixtures;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

final class SiteScopedAgentBridgeUser implements Authenticatable
{
    /**
     * @param  list<int>  $assignedSiteIds
     */
    public function __construct(
        private readonly int $identifier,
        private readonly array $assignedSiteIds,
        private readonly bool $globalAdmin = false,
    ) {}

    public function getAuthIdentifier(): int
    {
        return $this->identifier;
    }

    /** @return Collection<int, int> */
    public function getAssignedSiteIds(): Collection
    {
        return collect($this->assignedSiteIds);
    }

    public function isGlobalAdmin(): bool
    {
        return $this->globalAdmin;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
