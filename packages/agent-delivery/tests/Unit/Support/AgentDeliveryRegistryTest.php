<?php

declare(strict_types=1);

use Capell\AgentDelivery\Contracts\AgentDeliveryChunkContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryMetadataContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryReferenceContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryRelatedUrlContributor;
use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Capell\AgentDelivery\Support\AgentDeliveryRegistry;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;

it('merges contributor metadata and returns chunks in stable order', function (): void {
    $registry = new AgentDeliveryRegistry;
    $page = new Page(['id' => 5]);
    $site = new Site(['id' => 7]);
    $language = new Language(['id' => 9, 'code' => 'en', 'locale' => 'en']);

    $registry->register(new class implements AgentDeliveryContributor
    {
        /**
         * @param  Pageable<Model>  $page
         * @return array<string, mixed>
         */
        public function metadata(Pageable $page, Site $site, Language $language): array
        {
            return ['provenance' => ['reviewed_by' => 'Editorial']];
        }

        /**
         * @param  Pageable<Model>  $page
         * @return list<AgentDeliveryChunkData>
         */
        public function chunks(Pageable $page, Site $site, Language $language): array
        {
            return [
                new AgentDeliveryChunkData('second', 'Second', 'https://example.com#second', null, 'Second body', 2),
                new AgentDeliveryChunkData('first', 'First', 'https://example.com#first', null, 'First body', 1),
            ];
        }
    });

    expect($registry->metadata($page, $site, $language))
        ->toBe(['provenance' => ['reviewed_by' => 'Editorial']])
        ->and(array_map(static fn (AgentDeliveryChunkData $chunk): string => $chunk->id, $registry->chunks($page, $site, $language)))
        ->toBe(['first', 'second']);
});

it('collects focused package-aware delivery contributors', function (): void {
    $registry = new AgentDeliveryRegistry;
    $page = new Page(['id' => 5]);
    $site = new Site(['id' => 7]);
    $language = new Language(['id' => 9, 'code' => 'en', 'locale' => 'en']);

    $registry
        ->registerMetadataContributor(new class implements AgentDeliveryMetadataContributor
        {
            /**
             * @param  Pageable<Model>  $page
             * @return array<string, mixed>
             */
            public function metadata(Pageable $page, Site $site, Language $language): array
            {
                return ['author' => ['name' => 'Editorial']];
            }
        })
        ->registerChunkContributor(new class implements AgentDeliveryChunkContributor
        {
            /**
             * @param  Pageable<Model>  $page
             * @return list<AgentDeliveryChunkData>
             */
            public function chunks(Pageable $page, Site $site, Language $language): array
            {
                return [
                    new AgentDeliveryChunkData('package-section', 'Package Section', 'https://example.com#section', null, 'Section body', 10),
                ];
            }
        })
        ->registerReferenceContributor(new class implements AgentDeliveryReferenceContributor
        {
            /**
             * @param  Pageable<Model>  $page
             * @return list<array<string, string>>
             */
            public function references(Pageable $page, Site $site, Language $language): array
            {
                return [
                    ['title' => 'Source', 'url' => 'https://source.example/reference'],
                    ['title' => 'Unsafe', 'url' => 'javascript:alert(1)'],
                ];
            }
        })
        ->registerRelatedUrlContributor(new class implements AgentDeliveryRelatedUrlContributor
        {
            /**
             * @param  Pageable<Model>  $page
             * @return list<string>
             */
            public function relatedUrls(Pageable $page, Site $site, Language $language): array
            {
                return [
                    'https://example.com/related',
                    'https://example.com/related',
                    '/relative-internal',
                ];
            }
        });

    expect($registry->metadata($page, $site, $language))->toBe(['author' => ['name' => 'Editorial']])
        ->and(array_map(static fn (AgentDeliveryChunkData $chunk): string => $chunk->id, $registry->chunks($page, $site, $language)))
        ->toBe(['package-section'])
        ->and($registry->references($page, $site, $language))
        ->toBe([
            ['title' => 'Source', 'url' => 'https://source.example/reference'],
        ])
        ->and($registry->relatedUrls($page, $site, $language))
        ->toBe(['https://example.com/related']);
});
