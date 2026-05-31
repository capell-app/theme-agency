<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Data;

use Spatie\LaravelData\Data;

final class PublicKnowledgeBaseNavigationItemData extends Data
{
    /**
     * @param  list<array{title: string, slug: string, publicPath: string, summary: string|null}>  $articles
     */
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly array $articles,
    ) {}
}
