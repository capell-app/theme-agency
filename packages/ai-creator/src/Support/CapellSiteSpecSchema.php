<?php

declare(strict_types=1);

namespace Capell\AiCreator\Support;

/**
 * JSON-schema description of CapellSiteSpecData, handed to the external agent
 * by the `get_site_spec_schema` discovery tool so it can assemble a valid spec
 * without guessing field names. Hand-authored to mirror the DTOs in
 * Capell\AiCreator\Data\SiteSpec — keep in sync when those change.
 */
final class CapellSiteSpecSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(): array
    {
        return [
            'type' => 'object',
            'required' => ['site', 'theme', 'pages'],
            'properties' => [
                'site' => [
                    'type' => 'object',
                    'required' => ['name'],
                    'properties' => [
                        'name' => ['type' => 'string', 'minLength' => 1],
                        'businessName' => ['type' => ['string', 'null']],
                        'organisationType' => ['type' => ['string', 'null']],
                        'description' => ['type' => ['string', 'null']],
                    ],
                ],
                'theme' => [
                    'type' => 'object',
                    'required' => ['key'],
                    'properties' => [
                        'key' => ['type' => 'string', 'minLength' => 1],
                        'colors' => [
                            'type' => 'object',
                            'properties' => [
                                'primary' => ['type' => ['string', 'null']],
                                'secondary' => ['type' => ['string', 'null']],
                                'accent' => ['type' => ['string', 'null']],
                            ],
                        ],
                        'fontFamily' => ['type' => ['string', 'null']],
                        'linkColor' => ['type' => ['string', 'null']],
                        'linkColorActive' => ['type' => ['string', 'null']],
                        'container' => ['type' => ['string', 'null']],
                        'customCss' => ['type' => ['string', 'null']],
                    ],
                ],
                'language' => [
                    'type' => 'object',
                    'properties' => [
                        'code' => ['type' => 'string', 'default' => 'en'],
                        'name' => ['type' => 'string', 'default' => 'English'],
                        'locale' => ['type' => 'string', 'default' => 'en_GB'],
                        'flag' => ['type' => 'string', 'default' => 'gb'],
                        'default' => ['type' => 'boolean', 'default' => true],
                    ],
                ],
                'pages' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['name', 'slug', 'title', 'pageType'],
                        'properties' => [
                            'name' => ['type' => 'string', 'minLength' => 1],
                            'slug' => ['type' => 'string', 'minLength' => 1],
                            'title' => ['type' => 'string', 'minLength' => 1],
                            'pageType' => ['type' => 'string', 'description' => 'A page blueprint key (see list_page_types).'],
                            'url' => ['type' => ['string', 'null'], 'description' => 'Public path; defaults to "/{slug}".'],
                            'description' => ['type' => ['string', 'null']],
                            'order' => ['type' => 'integer', 'default' => 0],
                            'contentStructure' => ['type' => 'string', 'enum' => ['html', 'blocks'], 'default' => 'html'],
                            'sections' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['type', 'content'],
                                    'properties' => [
                                        'type' => ['type' => 'string', 'description' => 'A section blueprint key (see list_section_types).'],
                                        'content' => ['type' => 'string', 'description' => 'HTML body the agent writes.'],
                                        'title' => ['type' => ['string', 'null']],
                                        'summary' => ['type' => ['string', 'null']],
                                        'order' => ['type' => 'integer', 'default' => 0],
                                        'meta' => ['type' => 'object'],
                                    ],
                                ],
                            ],
                            'visibility' => ['type' => 'object'],
                            'meta' => ['type' => 'object'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
