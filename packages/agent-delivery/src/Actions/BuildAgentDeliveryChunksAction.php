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

        $heading = $delivery->title ?? $delivery->url;
        $idBase = Str::slug($heading);

        return [
            new AgentDeliveryChunkData(
                id: $idBase !== '' ? $idBase : 'page',
                heading: $heading,
                sourceUrl: $delivery->canonicalUrl,
                summary: $delivery->summary,
                body: $delivery->body,
                order: 1,
                references: $delivery->references,
                dependsOn: ['url:' . $delivery->canonicalUrl],
            ),
        ];
    }
}
