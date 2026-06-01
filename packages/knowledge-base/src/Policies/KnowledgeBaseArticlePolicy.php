<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Policies;

use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Illuminate\Contracts\Auth\Authenticatable;

final class KnowledgeBaseArticlePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, KnowledgeBaseArticle $article): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, KnowledgeBaseArticle $article): bool
    {
        return true;
    }

    public function delete(Authenticatable $user, KnowledgeBaseArticle $article): bool
    {
        return false;
    }
}
