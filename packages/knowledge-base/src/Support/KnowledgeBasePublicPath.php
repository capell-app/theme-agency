<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Support;

use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Illuminate\Support\Str;
use RuntimeException;

final class KnowledgeBasePublicPath
{
    public static function forArticle(KnowledgeBaseArticle $article): string
    {
        $article->loadMissing('collection');
        $collection = $article->collection;

        throw_unless($collection instanceof KnowledgeBaseCollection, RuntimeException::class, 'Knowledge base articles require a collection to resolve their public path.');

        $prefix = trim((string) config('capell-knowledge-base.public_path_prefix', 'docs'), '/');
        $segments = [
            $prefix,
            $collection->slug,
            $article->slug,
        ];

        return '/' . collect($segments)
            ->map(static fn (string $segment): string => trim($segment, '/'))
            ->filter(static fn (string $segment): bool => $segment !== '')
            ->map(static fn (string $segment): string => Str::slug($segment))
            ->implode('/');
    }
}
