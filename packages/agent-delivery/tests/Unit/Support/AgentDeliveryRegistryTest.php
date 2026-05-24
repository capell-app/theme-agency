<?php

declare(strict_types=1);

use Capell\AgentDelivery\Contracts\AgentDeliveryContributor;
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
