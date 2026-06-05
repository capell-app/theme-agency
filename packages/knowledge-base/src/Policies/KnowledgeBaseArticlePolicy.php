<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Policies;

final class KnowledgeBaseArticlePolicy extends AbstractKnowledgeBaseResourcePolicy
{
    protected static function subject(): string
    {
        return 'KnowledgeBaseArticle';
    }
}
