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
use Illuminate\Support\Facades\Log;
use Throwable;

final class AgentDeliveryRegistry
{
    /**
     * Keys that must never appear in public contributor metadata output.
     *
     * @var list<string>
     */
    private const array BLOCKED_METADATA_KEYS = [
        'model_id',
        'internal_model_id',
        'model_class',
        'model_type',
        'field_path',
        'admin_url',
        'admin_path',
        'edit_url',
        'filament_url',
        'signed_url',
        'secret_prompt',
        'prompt',
        'system_prompt',
        'api_key',
        'api_secret',
        'token',
        'password',
        'internal_id',
        'permission',
        'permissions',
    ];

    /** @var list<string> */
    private const array BLOCKED_VALUE_FRAGMENTS = [
        '/admin',
        '/filament',
        'signed-editor-url',
        'signed_url',
        'secret prompt',
        'system prompt',
        'api key',
        'api_key',
        'api secret',
        'api_secret',
        'bearer ',
        'sk-',
    ];

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
            try {
                $metadata = array_replace_recursive($metadata, $contributor->metadata($page, $site, $language));
            } catch (Throwable $throwable) {
                $this->reportContributorFailure($contributor, 'metadata', $throwable);
            }
        }

        foreach ($this->metadataContributors as $contributor) {
            try {
                $metadata = array_replace_recursive($metadata, $contributor->metadata($page, $site, $language));
            } catch (Throwable $throwable) {
                $this->reportContributorFailure($contributor, 'metadata', $throwable);
            }
        }

        return $this->sanitiseMetadata($metadata);
    }

    /**
     * @param  Pageable<Model>  $page
     * @return list<AgentDeliveryChunkData>
     */
    public function chunks(Pageable $page, Site $site, Language $language): array
    {
        $chunks = [];

        foreach ($this->contributors as $contributor) {
            try {
                foreach ($contributor->chunks($page, $site, $language) as $chunk) {
                    $chunks[] = $chunk;
                }
            } catch (Throwable $throwable) {
                $this->reportContributorFailure($contributor, 'chunks', $throwable);
            }
        }

        foreach ($this->chunkContributors as $contributor) {
            try {
                foreach ($contributor->chunks($page, $site, $language) as $chunk) {
                    $chunks[] = $chunk;
                }
            } catch (Throwable $throwable) {
                $this->reportContributorFailure($contributor, 'chunks', $throwable);
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
            try {
                foreach ($contributor->references($page, $site, $language) as $reference) {
                    if ($this->isPublicUrl($reference['url'] ?? null)) {
                        $references[] = array_filter(
                            $reference,
                            static fn (string $value): bool => $value !== '',
                        );
                    }
                }
            } catch (Throwable $throwable) {
                $this->reportContributorFailure($contributor, 'references', $throwable);
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
            try {
                foreach ($contributor->relatedUrls($page, $site, $language) as $url) {
                    if ($this->isPublicUrl($url)) {
                        $urls[] = $url;
                    }
                }
            } catch (Throwable $throwable) {
                $this->reportContributorFailure($contributor, 'related_urls', $throwable);
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * Recursively strip blocked keys from contributor metadata to prevent
     * accidental leakage of internal identifiers, admin URLs, prompts, or secrets.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function sanitiseMetadata(array $metadata): array
    {
        $blocked = array_flip(self::BLOCKED_METADATA_KEYS);
        $sanitised = [];

        foreach ($metadata as $key => $value) {
            if (isset($blocked[$key])) {
                continue;
            }

            $value = is_array($value)
                ? $this->sanitiseMetadata($value)
                : $this->sanitiseMetadataValue($value);

            if ($value !== null) {
                $sanitised[$key] = $value;
            }
        }

        return $sanitised;
    }

    private function sanitiseMetadataValue(mixed $value): mixed
    {
        if (! is_scalar($value) && $value !== null) {
            return null;
        }

        if (! is_string($value)) {
            return $value;
        }

        $normalized = strtolower($value);

        foreach (self::BLOCKED_VALUE_FRAGMENTS as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return null;
            }
        }

        return $value;
    }

    private function isPublicUrl(mixed $url): bool
    {
        return is_string($url)
            && (str_starts_with($url, 'https://') || str_starts_with($url, 'http://'));
    }

    private function reportContributorFailure(object $contributor, string $surface, Throwable $throwable): void
    {
        Log::warning('capell-agent-delivery: skipped failing contributor.', [
            'contributor' => get_debug_type($contributor),
            'surface' => $surface,
            'exception' => $throwable::class,
        ]);
    }
}
