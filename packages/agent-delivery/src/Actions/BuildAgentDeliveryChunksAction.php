<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Actions;

use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Capell\AgentDelivery\Data\AgentDeliveryPageData;
use Capell\AgentDelivery\Support\AgentDeliveryRegistry;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static list<AgentDeliveryChunkData> run(Pageable $page, Site $site, Language $language, AgentDeliveryPageData $delivery)
 */
final class BuildAgentDeliveryChunksAction
{
    use AsObject;

    public function __construct(private readonly AgentDeliveryRegistry $registry) {}

    /**
     * @param  Pageable<Model>  $page
     * @return list<AgentDeliveryChunkData>
     */
    public function handle(Pageable $page, Site $site, Language $language, AgentDeliveryPageData $delivery): array
    {
        $chunks = $this->registry->chunks($page, $site, $language);

        if ($chunks !== []) {
            return $chunks;
        }

        if ($delivery->body === null || $delivery->body === '') {
            return [];
        }

        return $this->defaultChunks($delivery);
    }

    /**
     * @return list<AgentDeliveryChunkData>
     */
    private function defaultChunks(AgentDeliveryPageData $delivery): array
    {
        $segments = $this->segments($delivery);
        $chunks = [];
        $order = 1;

        foreach ($segments as $segment) {
            foreach ($this->splitByTargetWords($segment['body']) as $partIndex => $body) {
                $heading = $partIndex === 0
                    ? $segment['heading']
                    : sprintf('%s %d', $segment['heading'], $partIndex + 1);
                $idBase = Str::slug($heading);

                $chunks[] = new AgentDeliveryChunkData(
                    id: $idBase !== '' ? $idBase : 'page-' . $order,
                    heading: $heading,
                    sourceUrl: $delivery->canonicalUrl . '#' . ($idBase !== '' ? $idBase : 'page-' . $order),
                    summary: Str::words($body, 40, ''),
                    body: $body,
                    order: $order,
                    references: $delivery->references,
                    dependsOn: ['url:' . $delivery->canonicalUrl],
                );

                $order++;
            }
        }

        return $chunks;
    }

    /**
     * @return list<array{heading: string, body: string}>
     */
    private function segments(AgentDeliveryPageData $delivery): array
    {
        $fallbackHeading = $delivery->title ?? $delivery->url;
        $body = $delivery->body ?? '';
        $candidateHeadings = array_values(array_filter(
            $delivery->headings,
            static fn (string $heading): bool => $heading !== $delivery->title,
        ));

        $positions = [];

        foreach ($candidateHeadings as $heading) {
            $position = mb_stripos($body, $heading);

            if ($position !== false) {
                $positions[] = ['heading' => $heading, 'position' => $position];
            }
        }

        usort(
            $positions,
            static fn (array $left, array $right): int => $left['position'] <=> $right['position'],
        );

        if ($positions === []) {
            return [[
                'heading' => $fallbackHeading,
                'body' => $body,
            ]];
        }

        $segments = [];

        foreach ($positions as $index => $position) {
            $start = $position['position'];
            $end = $positions[$index + 1]['position'] ?? mb_strlen($body);
            $segmentBody = trim(mb_substr($body, $start, $end - $start));

            if ($segmentBody !== '') {
                $segments[] = [
                    'heading' => $position['heading'],
                    'body' => $segmentBody,
                ];
            }
        }

        return $segments !== [] ? $segments : [[
            'heading' => $fallbackHeading,
            'body' => $body,
        ]];
    }

    /**
     * @return list<string>
     */
    private function splitByTargetWords(string $body): array
    {
        $words = preg_split('/\s+/u', trim($body)) ?: [];
        $words = array_values(array_filter($words, static fn (string $word): bool => $word !== ''));

        if ($words === []) {
            return [];
        }

        $targetWords = $this->positiveConfigInt('chunk_target_words', 160);
        $overlapWords = min($this->positiveConfigInt('chunk_overlap_words', 30), max(0, $targetWords - 1));

        if (count($words) <= $targetWords) {
            return [implode(' ', $words)];
        }

        $chunks = [];
        $start = 0;

        while ($start < count($words)) {
            $chunkWords = array_slice($words, $start, $targetWords);
            $chunks[] = implode(' ', $chunkWords);

            if (count($chunkWords) < $targetWords) {
                break;
            }

            $start += $targetWords - $overlapWords;
        }

        return $chunks;
    }

    private function positiveConfigInt(string $key, int $fallback): int
    {
        $value = config('capell-agent-delivery.public_pages.' . $key, $fallback);

        if (is_int($value)) {
            return $value > 0 ? $value : $fallback;
        }

        return is_numeric($value) && (int) $value > 0 ? (int) $value : $fallback;
    }
}
