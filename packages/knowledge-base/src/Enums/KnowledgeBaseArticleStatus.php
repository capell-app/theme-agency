<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Enums;

use Filament\Support\Contracts\HasLabel;

enum KnowledgeBaseArticleStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return (string) __('capell-knowledge-base::generic.article_status.' . $this->value);
    }

    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
    }
}
