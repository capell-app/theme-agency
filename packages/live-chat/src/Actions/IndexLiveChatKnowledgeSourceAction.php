<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\LiveChatKnowledgeDocumentData;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class IndexLiveChatKnowledgeSourceAction
{
    use AsAction;

    public function handle(LiveChatKnowledgeSource $source, ?int $installationId = null): ?LiveChatKnowledgeDocument
    {
        if (! is_string($source->content) || trim($source->content) === '') {
            return null;
        }

        $document = (new IndexLiveChatKnowledgeDocumentAction)->handle(new LiveChatKnowledgeDocumentData(
            sourceType: $source->type,
            sourceKey: $source->source_key,
            title: $source->title,
            content: $source->content,
            url: $source->url,
            sourceId: $source->id,
            siteId: $source->site_id,
            installationId: $installationId,
            metadata: [
                'source_id' => $source->id,
            ],
        ));

        $source->forceFill([
            'content_hash' => $document->content_hash,
            'last_synced_at' => CarbonImmutable::now(),
        ])->save();

        return $document;
    }
}
