<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleVersionData;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;

final class CreateKnowledgeBaseArticleVersionAction
{
    use AsObject;

    public function handle(CreateKnowledgeBaseArticleVersionData $data): KnowledgeBaseArticleVersion
    {
        $title = trim($data->title);
        $body = trim($data->body);
        $version = trim($data->version);

        if ($title === '') {
            throw ValidationException::withMessages([
                'title' => __('capell-knowledge-base::generic.validation.article_title_required'),
            ]);
        }

        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => __('capell-knowledge-base::generic.validation.article_body_required'),
            ]);
        }

        $version = $version === '' ? 'v1' : $version;

        if ($data->article->versions()->where('version', $version)->exists()) {
            throw ValidationException::withMessages([
                'version' => __('capell-knowledge-base::generic.validation.version_unique'),
            ]);
        }

        return KnowledgeBaseArticleVersion::query()->create([
            'article_id' => $data->article->getKey(),
            'version' => $version,
            'title' => $title,
            'summary' => $data->summary === null ? null : trim($data->summary),
            'body' => $body,
            'author_type' => $data->author?->getMorphClass(),
            'author_id' => $data->author?->getKey(),
            'published_at' => null,
        ]);
    }
}
