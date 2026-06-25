<?php

declare(strict_types=1);

namespace Capell\AiCreator\Support;

final class AiCreatorCapabilitySchemas
{
    /** @return array<string, mixed> */
    public static function startSessionInput(): array
    {
        return [
            'type' => 'object',
            'required' => ['intent'],
            'properties' => [
                'intent' => ['type' => 'string', 'minLength' => 1],
                'site_id' => ['type' => ['integer', 'null']],
                'workspace_id' => ['type' => ['integer', 'null']],
                'answers' => ['type' => 'object'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function sessionIdInput(): array
    {
        return [
            'type' => 'object',
            'required' => ['session_id'],
            'properties' => [
                'session_id' => ['type' => 'integer'],
            ],
        ];
    }

    /**
     * Input schema for the input-free discovery catalogue tools.
     *
     * @return array<string, mixed>
     */
    public static function noInput(): array
    {
        return [
            'type' => 'object',
            'properties' => [],
            'additionalProperties' => false,
        ];
    }

    /** @return array<string, mixed> */
    public static function validateSiteSpecInput(): array
    {
        return [
            'type' => 'object',
            'required' => ['spec'],
            'properties' => [
                'spec' => CapellSiteSpecSchema::toArray(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function buildPreviewInput(): array
    {
        return [
            'type' => 'object',
            'required' => ['session_id', 'spec'],
            'properties' => [
                'session_id' => ['type' => 'integer'],
                'spec' => CapellSiteSpecSchema::toArray(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function exportSiteInput(): array
    {
        return [
            'type' => 'object',
            'required' => ['spec'],
            'properties' => [
                'spec' => CapellSiteSpecSchema::toArray(),
                'project_name' => ['type' => ['string', 'null']],
            ],
        ];
    }
}
