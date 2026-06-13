<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\LiveChatKnowledgeDocumentData;
use Capell\LiveChat\Enums\KnowledgeSourceStatus;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class IndexLiveChatKnowledgeDocumentAction
{
    use AsAction;

    public function handle(LiveChatKnowledgeDocumentData $data): LiveChatKnowledgeDocument
    {
        $content = $this->normalizeContent($data->content);
        $attributes = [
            'installation_id' => $data->installationId,
            'source_type' => $data->sourceType->value,
            'source_key' => $data->sourceKey,
            'chunk_index' => $data->chunkIndex,
        ];

        return LiveChatKnowledgeDocument::query()->updateOrCreate($attributes, [
            'site_id' => $data->siteId,
            'source_id' => $data->sourceId,
            'title' => $data->title,
            'url' => $data->url,
            'content' => $content,
            'content_hash' => hash('sha256', $content),
            'status' => KnowledgeSourceStatus::Active,
            'last_synced_at' => CarbonImmutable::now(),
            'metadata' => $data->metadata,
        ]);
    }

    private function normalizeContent(string $content): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($content)));
    }
}
