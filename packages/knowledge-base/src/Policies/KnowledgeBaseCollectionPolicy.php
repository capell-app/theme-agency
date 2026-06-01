<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Policies;

use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Illuminate\Contracts\Auth\Authenticatable;

final class KnowledgeBaseCollectionPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, KnowledgeBaseCollection $collection): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, KnowledgeBaseCollection $collection): bool
    {
        return true;
    }

    public function delete(Authenticatable $user, KnowledgeBaseCollection $collection): bool
    {
        return false;
    }
}
