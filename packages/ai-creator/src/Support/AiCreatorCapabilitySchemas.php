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
}
