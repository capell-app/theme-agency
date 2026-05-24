<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Support;

use Capell\AgentDelivery\Contracts\AgentDeliveryContributor;
use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;

final class AgentDeliveryRegistry
{
    /** @var list<AgentDeliveryContributor> */
    private array $contributors = [];

    public function register(AgentDeliveryContributor $contributor): self
    {
        $this->contributors[] = $contributor;

        return $this;
    }

    /**
     * @param  Pageable<Model>  $page
     * @return array<string, mixed>
     */
    public function metadata(Pageable $page, Site $site, Language $language): array
    {
        $metadata = [];

        foreach ($this->contributors as $contributor) {
            $metadata = array_replace_recursive($metadata, $contributor->metadata($page, $site, $language));
        }

        return $metadata;
    }

    /**
     * @param  Pageable<Model>  $page
     * @return list<AgentDeliveryChunkData>
     */
    public function chunks(Pageable $page, Site $site, Language $language): array
    {
        $chunks = [];

        foreach ($this->contributors as $contributor) {
            foreach ($contributor->chunks($page, $site, $language) as $chunk) {
                $chunks[] = $chunk;
            }
        }

        usort(
            $chunks,
            static fn (AgentDeliveryChunkData $left, AgentDeliveryChunkData $right): int => $left->order <=> $right->order,
        );

        return $chunks;
    }
}
