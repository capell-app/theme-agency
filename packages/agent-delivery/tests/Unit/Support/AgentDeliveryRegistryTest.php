<?php

declare(strict_types=1);

use Capell\AgentDelivery\Actions\BuildAgentDeliveryPageAction;
use Capell\AgentDelivery\Contracts\AgentDeliveryChunkContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryMetadataContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryReferenceContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryRelatedUrlContributor;
use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Capell\AgentDelivery\Support\AgentDeliveryRegistry;
use Capell\AgentDelivery\Tests\AgentDeliveryTestCase;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Data\PublicPageFieldsData;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;

uses(AgentDeliveryTestCase::class);

it('merges contributor metadata and returns chunks in stable order', function (): void {
    $registry = new AgentDeliveryRegistry;
    $page = (new Page)->forceFill(['id' => 5]);
    $page->setRelation('pageUrl', null);

    $site = (new Site)->forceFill(['id' => 7]);
    $language = (new Language)->forceFill(['id' => 9, 'code' => 'en', 'locale' => 'en']);

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
    $page = (new Page)->forceFill(['id' => 5]);
    $site = (new Site)->forceFill(['id' => 7]);
    $language = (new Language)->forceFill(['id' => 9, 'code' => 'en', 'locale' => 'en']);

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

it('sanitises contributor metadata to strip blocked internal keys', function (): void {
    $registry = new AgentDeliveryRegistry;
    $page = (new Page)->forceFill(['id' => 5]);
    $site = (new Site)->forceFill(['id' => 7]);
    $language = (new Language)->forceFill(['id' => 9, 'code' => 'en', 'locale' => 'en']);

    $registry->registerMetadataContributor(new class implements AgentDeliveryMetadataContributor
    {
        /**
         * @param  Pageable<Model>  $page
         * @return array<string, mixed>
         */
        public function metadata(Pageable $page, Site $site, Language $language): array
        {
            return [
                'author' => ['name' => 'Editorial'],
                'model_id' => 42,
                'internal_model_id' => 999,
                'field_path' => 'blocks.0.content',
                'admin_url' => 'https://admin.example.com/edit/42',
                'secret_prompt' => 'Rewrite with internal prompt',
                'api_key' => 'sk-secret-key',
                'provenance' => [
                    'source' => 'Editorial Team',
                    'internal_id' => 'int-ref-123',
                    'token' => 'bearer-abc',
                    'public_note' => 'Reviewed for publication.',
                    'unsafe_note' => 'Contains a secret prompt for editors.',
                    'admin_link' => 'https://example.com/admin/pages/42',
                ],
                'safe_key' => 'visible-value',
            ];
        }
    });

    $metadata = $registry->metadata($page, $site, $language);

    expect($metadata)
        ->toHaveKey('author')
        ->toHaveKey('safe_key', 'visible-value')
        ->toHaveKey('provenance')
        ->and($metadata['provenance'])->toBe([
            'source' => 'Editorial Team',
            'public_note' => 'Reviewed for publication.',
        ])
        ->and($metadata)->not->toHaveKey('model_id')
        ->and($metadata)->not->toHaveKey('internal_model_id')
        ->and($metadata)->not->toHaveKey('field_path')
        ->and($metadata)->not->toHaveKey('admin_url')
        ->and($metadata)->not->toHaveKey('secret_prompt')
        ->and($metadata)->not->toHaveKey('api_key');
});

it('does not expose raw array content in public page body or headings', function (): void {
    $registry = new AgentDeliveryRegistry;
    $page = (new Page)->forceFill(['id' => 5]);
    $page->setRelation('pageUrl', null);

    $site = (new Site)->forceFill(['id' => 7]);
    $language = (new Language)->forceFill(['id' => 9, 'code' => 'en', 'locale' => 'en']);

    $delivery = (new BuildAgentDeliveryPageAction($registry))->handle(
        page: $page,
        site: $site,
        language: $language,
        fields: new PublicPageFieldsData(
            url: '/structured',
            title: 'Structured Page',
            content: [
                'internal_model_id' => 123,
                'field_path' => 'blocks.0.secret_prompt',
                'public_copy' => 'Visible copy must be contributed as rendered text.',
            ],
            meta: [],
        ),
    );

    expect($delivery->body)->toBeNull()
        ->and($delivery->summary)->toBeNull()
        ->and($delivery->headings)->toBe(['Structured Page']);
});
