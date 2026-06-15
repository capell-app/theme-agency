<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Actions;

use Capell\AgentDelivery\Data\AgentDeliveryChunkBudgetData;
use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static AgentDeliveryChunkBudgetData run(array<int, AgentDeliveryChunkData> $chunks)
 */
final class AnalyzeAgentDeliveryChunkBudgetAction
{
    use AsObject;

    /**
     * @param  list<AgentDeliveryChunkData>  $chunks
     */
    public function handle(array $chunks): AgentDeliveryChunkBudgetData
    {
        $targetWords = $this->positiveConfigInt('chunk_target_words', 160);
        $maxRecommendedChunks = $this->positiveConfigInt('chunk_max_recommended_chunks', 40);
        $maxChunkWords = 0;
        $overTargetChunks = 0;

        foreach ($chunks as $chunk) {
            $wordCount = $this->wordCount($chunk->body);
            $maxChunkWords = max($maxChunkWords, $wordCount);

            if ($wordCount > $targetWords) {
                $overTargetChunks++;
            }
        }

        $warnings = [];

        if (count($chunks) > $maxRecommendedChunks) {
            $warnings[] = 'chunk_count_exceeds_recommended_max';
        }

        if ($overTargetChunks > 0) {
            $warnings[] = 'chunk_body_exceeds_target_words';
        }

        return new AgentDeliveryChunkBudgetData(
            chunkCount: count($chunks),
            targetWords: $targetWords,
            maxRecommendedChunks: $maxRecommendedChunks,
            maxChunkWords: $maxChunkWords,
            overTargetChunks: $overTargetChunks,
            isWithinBudget: $warnings === [],
            warnings: $warnings,
        );
    }

    private function positiveConfigInt(string $key, int $fallback): int
    {
        $value = config('capell-agent-delivery.public_pages.' . $key, $fallback);

        if (is_int($value)) {
            return $value > 0 ? $value : $fallback;
        }

        return is_numeric($value) && (int) $value > 0 ? (int) $value : $fallback;
    }

    private function wordCount(string $body): int
    {
        $words = preg_split('/\s+/u', trim($body)) ?: [];

        return count(array_filter($words, static fn (string $word): bool => $word !== ''));
    }
}
