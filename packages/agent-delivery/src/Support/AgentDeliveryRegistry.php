<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Support;

use Capell\AgentDelivery\Contracts\AgentDeliveryChunkContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryMetadataContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryReferenceContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryRelatedUrlContributor;
use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;

final class AgentDeliveryRegistry
{
    /** @var list<AgentDeliveryContributor> */
    private array $contributors = [];

    /** @var list<AgentDeliveryMetadataContributor> */
    private array $metadataContributors = [];

    /** @var list<AgentDeliveryChunkContributor> */
    private array $chunkContributors = [];

    /** @var list<AgentDeliveryReferenceContributor> */
    private array $referenceContributors = [];

    /** @var list<AgentDeliveryRelatedUrlContributor> */
    private array $relatedUrlContributors = [];

    public function register(AgentDeliveryContributor $contributor): self
    {
        $this->contributors[] = $contributor;

        return $this;
    }

    public function registerMetadataContributor(AgentDeliveryMetadataContributor $contributor): self
    {
        $this->metadataContributors[] = $contributor;

        return $this;
    }

    public function registerChunkContributor(AgentDeliveryChunkContributor $contributor): self
    {
        $this->chunkContributors[] = $contributor;

        return $this;
    }

    public function registerReferenceContributor(AgentDeliveryReferenceContributor $contributor): self
    {
        $this->referenceContributors[] = $contributor;

        return $this;
    }

    public function registerRelatedUrlContributor(AgentDeliveryRelatedUrlContributor $contributor): self
    {
        $this->relatedUrlContributors[] = $contributor;

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

        foreach ($this->metadataContributors as $contributor) {
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

        foreach ($this->chunkContributors as $contributor) {
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

    /**
     * @param  Pageable<Model>  $page
     * @return list<array<string, string>>
     */
    public function references(Pageable $page, Site $site, Language $language): array
    {
        $references = [];

        foreach ($this->referenceContributors as $contributor) {
            foreach ($contributor->references($page, $site, $language) as $reference) {
                if ($this->isPublicUrl($reference['url'] ?? null)) {
                    $references[] = array_filter(
                        $reference,
                        static fn (string $value): bool => $value !== '',
                    );
                }
            }
        }

        return $references;
    }

    /**
     * @param  Pageable<Model>  $page
     * @return list<string>
     */
    public function relatedUrls(Pageable $page, Site $site, Language $language): array
    {
        $urls = [];

        foreach ($this->relatedUrlContributors as $contributor) {
            foreach ($contributor->relatedUrls($page, $site, $language) as $url) {
                if ($this->isPublicUrl($url)) {
                    $urls[] = $url;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    private function isPublicUrl(mixed $url): bool
    {
        return is_string($url)
            && (str_starts_with($url, 'https://') || str_starts_with($url, 'http://'));
    }
}
