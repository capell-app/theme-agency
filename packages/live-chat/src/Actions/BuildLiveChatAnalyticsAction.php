<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\EscalationReason;
use Capell\LiveChat\Models\LiveChatAIRun;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatKnowledgeGap;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildLiveChatAnalyticsAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(int $siteId, ?int $installationId = null): array
    {
        $baseQuery = LiveChatConversation::query()->where('site_id', $siteId);

        if ($installationId !== null) {
            $baseQuery->where('installation_id', $installationId);
        }

        $conversationIds = [];

        foreach ((clone $baseQuery)->pluck('id') as $id) {
            if (is_int($id)) {
                $conversationIds[] = $id;
            }
        }
        $lowConfidenceThreshold = $this->configFloat('capell-live-chat.low_confidence_threshold', 0.55);
        $total = (clone $baseQuery)->count();
        $handoffs = (clone $baseQuery)->whereNotNull('handoff_requested_at')->count();
        $capturedContacts = (clone $baseQuery)->whereNotNull('contact_captured_at')->count();
        $aiRuns = $conversationIds === []
            ? LiveChatAIRun::query()->whereRaw('1 = 0')
            : LiveChatAIRun::query()->whereIn('conversation_id', $conversationIds);
        $aiRunCount = (clone $aiRuns)->count();
        $successfulAiRuns = (clone $aiRuns)->where('status', 'succeeded')->count();

        return [
            'total' => $total,
            'active' => (clone $baseQuery)->where('status', ConversationStatus::Active)->count(),
            'waiting_for_human' => (clone $baseQuery)->where('status', ConversationStatus::WaitingForHuman)->count(),
            'captured_contacts' => $capturedContacts,
            'handoffs' => $handoffs,
            'ai_answer_rate' => $aiRunCount > 0 ? round($successfulAiRuns / $aiRunCount, 4) : 0.0,
            'handoff_rate' => $total > 0 ? round($handoffs / $total, 4) : 0.0,
            'lead_capture_rate' => $total > 0 ? round($capturedContacts / $total, 4) : 0.0,
            'after_hours_conversion' => $this->afterHoursConversion($siteId, $installationId),
            'low_confidence_topics' => $this->lowConfidenceTopics($conversationIds, $lowConfidenceThreshold),
            'knowledge_gaps' => $this->knowledgeGaps($installationId),
        ];
    }

    private static function intAttribute(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return 0;
    }

    /**
     * @param  list<int>  $conversationIds
     * @return list<array{capability_key: string, count: int}>
     */
    private function lowConfidenceTopics(array $conversationIds, float $threshold): array
    {
        if ($conversationIds === []) {
            return [];
        }

        $topics = LiveChatAIRun::query()
            ->whereIn('conversation_id', $conversationIds)
            ->whereNotNull('confidence')
            ->where('confidence', '<', $threshold)
            ->selectRaw('capability_key, count(*) as aggregate_count')
            ->groupBy('capability_key')
            ->orderByDesc('aggregate_count')
            ->limit(5)
            ->get()
            ->map(static fn (LiveChatAIRun $run): array => [
                'capability_key' => $run->capability_key,
                'count' => self::intAttribute($run->getAttribute('aggregate_count')),
            ])
            ->values()
            ->all();

        return array_values($topics);
    }

    /**
     * @return list<array{question_hash: string, source_area: string|null, occurrences: int}>
     */
    private function knowledgeGaps(?int $installationId): array
    {
        $query = LiveChatKnowledgeGap::query();

        if ($installationId !== null) {
            $query->where('installation_id', $installationId);
        }

        $gaps = $query
            ->orderByDesc('occurrence_count')
            ->limit(5)
            ->get()
            ->map(static fn (LiveChatKnowledgeGap $gap): array => [
                'question_hash' => $gap->question_hash,
                'source_area' => $gap->source_area,
                'occurrences' => $gap->occurrence_count,
            ])
            ->values()
            ->all();

        return array_values($gaps);
    }

    /**
     * @return array{conversations: int, captured_contacts: int, rate: float}
     */
    private function afterHoursConversion(int $siteId, ?int $installationId): array
    {
        $query = LiveChatConversation::query()
            ->where('site_id', $siteId)
            ->where('escalation_reason', EscalationReason::AfterHours->value);

        if ($installationId !== null) {
            $query->where('installation_id', $installationId);
        }

        $conversations = (clone $query)->count();
        $capturedContacts = (clone $query)->whereNotNull('contact_captured_at')->count();

        return [
            'conversations' => $conversations,
            'captured_contacts' => $capturedContacts,
            'rate' => $conversations > 0 ? round($capturedContacts / $conversations, 4) : 0.0,
        ];
    }

    private function configFloat(string $key, float $fallback): float
    {
        $value = config($key);

        return is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))
            ? (float) $value
            : $fallback;
    }
}
