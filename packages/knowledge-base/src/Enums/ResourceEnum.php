<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Enums;

use Capell\KnowledgeBase\Filament\Resources\Articles\KnowledgeBaseArticleResource;
use Capell\KnowledgeBase\Filament\Resources\Collections\KnowledgeBaseCollectionResource;

enum ResourceEnum: string
{
    case Collections = KnowledgeBaseCollectionResource::class;
    case Articles = KnowledgeBaseArticleResource::class;
}
