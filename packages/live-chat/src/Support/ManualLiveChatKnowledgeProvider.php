<?php

declare(strict_types=1);

namespace Capell\LiveChat\Support;

use Capell\LiveChat\Contracts\LiveChatKnowledgeProvider;
use Capell\LiveChat\Enums\KnowledgeSourceStatus;
use Capell\LiveChat\Enums\KnowledgeSourceType;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Database\Eloquent\Builder;

final class ManualLiveChatKnowledgeProvider implements LiveChatKnowledgeProvider
{
    /**
     * @return list<LiveChatKnowledgeDocument>
     */
    public function documents(LiveChatConversation $conversation, LiveChatMessage $message): array
    {
        $installationId = $conversation->installation_id;
        $siteId = $conversation->site_id;

        return array_values(LiveChatKnowledgeDocument::query()
            ->where('status', KnowledgeSourceStatus::Active->value)
            ->where('source_type', '!=', KnowledgeSourceType::KnowledgeBase->value)
            ->where(function (Builder $query) use ($installationId, $siteId): void {
                if (is_int($installationId)) {
                    $query->where('installation_id', $installationId);
                }

                $query->orWhere(function (Builder $query) use ($siteId): void {
                    $query->whereNull('installation_id')
                        ->where('site_id', $siteId);
                });

                $query->orWhere(function (Builder $query): void {
                    $query->whereNull('installation_id')
                        ->whereNull('site_id');
                });
            })
            ->orderBy('title')
            ->limit($this->limit())
            ->get()
            ->values()
            ->all());
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
