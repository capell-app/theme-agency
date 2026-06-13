<?php

declare(strict_types=1);

namespace Capell\LiveChat\Support;

use Capell\Core\Facades\CapellCore;
use Capell\KnowledgeBase\Actions\BuildAiReadableKnowledgeBaseOutputAction;
use Capell\KnowledgeBase\Data\AiReadableKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Providers\KnowledgeBaseServiceProvider;
use Capell\LiveChat\Actions\IndexLiveChatKnowledgeDocumentAction;
use Capell\LiveChat\Contracts\LiveChatKnowledgeProvider;
use Capell\LiveChat\Data\LiveChatKnowledgeDocumentData;
use Capell\LiveChat\Enums\KnowledgeSourceType;
use Capell\LiveChat\Enums\LiveChatSourcePolicy;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatInstallation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Support\Facades\Schema;

final class KnowledgeBaseLiveChatKnowledgeProvider implements LiveChatKnowledgeProvider
{
    /**
     * @return list<LiveChatKnowledgeDocument>
     */
    public function documents(LiveChatConversation $conversation, LiveChatMessage $message): array
    {
        if (! $this->enabled($conversation)) {
            return [];
        }

        if (! class_exists(BuildAiReadableKnowledgeBaseOutputAction::class) || ! Schema::hasTable('knowledge_base_articles')) {
            return [];
        }

        $documents = [];
        $articles = (new BuildAiReadableKnowledgeBaseOutputAction)->handle();

        foreach ($articles->take($this->limit()) as $article) {
            if (! $article instanceof AiReadableKnowledgeBaseArticleData || trim($article->content) === '') {
                continue;
            }

            $documents[] = (new IndexLiveChatKnowledgeDocumentAction)->handle(new LiveChatKnowledgeDocumentData(
                sourceType: KnowledgeSourceType::KnowledgeBase,
                sourceKey: 'knowledge-base:' . $article->publicPath,
                title: $article->title,
                content: trim(($article->summary ?? '') . ' ' . $article->content),
                url: $article->publicPath,
                siteId: $conversation->site_id,
                installationId: $conversation->installation_id,
                metadata: [
                    'provider' => 'knowledge-base',
                    'version' => $article->version,
                    'last_modified' => $article->lastModified?->toIso8601String(),
                ],
            ));
        }

        return $documents;
    }

    private function enabled(LiveChatConversation $conversation): bool
    {
        $installation = $conversation->installation;

        return $installation instanceof LiveChatInstallation
            && $installation->source_policy === LiveChatSourcePolicy::ManualAndKnowledgeBase
            && class_exists(KnowledgeBaseServiceProvider::class)
            && CapellCore::isPackageInstalled(KnowledgeBaseServiceProvider::$packageName);
    }

    private function limit(): int
    {
        return max(1, $this->configInt('capell-live-chat.knowledge.provider_document_limit', 50));
    }

    private function configInt(string $key, int $fallback): int
    {
        $value = config($key);

        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : $fallback;
    }
}
