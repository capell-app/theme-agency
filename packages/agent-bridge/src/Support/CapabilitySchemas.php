<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Support;

final class CapabilitySchemas
{
    /** @return array<string, mixed> */
    public static function emptyObject(): array
    {
        return [
            'type' => 'object',
            'properties' => [],
            'additionalProperties' => false,
        ];
    }

    /** @return array<string, mixed> */
    public static function createDraftPageInput(): array
    {
        return [
            'type' => 'object',
            'required' => ['name', 'site_id', 'blueprint_id', 'layout_id'],
            'properties' => [
                'name' => ['type' => 'string', 'maxLength' => 255],
                'site_id' => ['type' => 'integer'],
                'blueprint_id' => ['type' => 'integer'],
                'layout_id' => ['type' => 'integer'],
                'parent_id' => ['type' => ['integer', 'null']],
                'meta' => ['type' => ['object', 'null']],
                'admin' => ['type' => ['object', 'null']],
                'visible_from' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                'visible_until' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function updateDraftPageInput(): array
    {
        return [
            'type' => 'object',
            'required' => ['page_id'],
            'properties' => [
                'page_id' => ['type' => 'integer'],
                'name' => ['type' => 'string', 'maxLength' => 255],
                'meta' => ['type' => ['object', 'null']],
                'admin' => ['type' => ['object', 'null']],
                'visible_from' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                'visible_until' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function pageIdInput(): array
    {
        return [
            'type' => 'object',
            'required' => ['page_id'],
            'properties' => [
                'page_id' => ['type' => 'integer'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function capabilityResultOutput(): array
    {
        return [
            'type' => 'object',
            'required' => ['ok', 'message', 'data', 'warnings'],
            'properties' => [
                'ok' => ['type' => 'boolean'],
                'message' => ['type' => 'string'],
                'data' => ['type' => 'object'],
                'warnings' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
        ];
    }
}
