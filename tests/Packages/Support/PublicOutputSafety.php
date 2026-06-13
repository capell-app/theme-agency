<?php

declare(strict_types=1);

use Illuminate\Testing\TestResponse;

/**
 * @return list<string>
 */
function capellPublicOutputForbiddenTokens(): array
{
    return [
        '/admin/',
        '/authoring/regions/',
        '/filament/',
        '/filament-peek/preview',
        '/livewire/',
        'CapellFrontendAuthoring',
        'capell-authoring',
        'capell-editor',
        'capell-frontend-authoring',
        'data-can-edit',
        'data-capell-authoring',
        'data-capell-editor',
        'data-editable',
        'data-field-path',
        'data-livewire',
        'data-model-id',
        'data-page-id',
        'edit_url',
        'editable_regions',
        'editor-only',
        'frontend-authoring',
        'recordKey',
        'signed editor',
        'signed-editor',
        'signed_editor',
        'window.CapellFrontendAuthoring',
        'window.Livewire',
        'wire:effects',
        'wire:id',
        'wire:snapshot',
    ];
}

/**
 * @return array<string, string>
 */
function capellPublicOutputForbiddenPatterns(): array
{
    return [
        'admin_url' => '~(?<![A-Za-z0-9_-])/(?:admin)(?:[/?#)"\'\s]|$)~i',
        'authoring_region_url' => '#/authoring/regions/[A-Za-z0-9_-]+#',
        'field_path' => '/(?:\bdata-field-path\b|["\'](?:fieldPath|field[_-]?path)["\'])\s*(?:=|:)\s*["\'][^"\']+["\']/i',
        'filament_url' => '~(?<![A-Za-z0-9_-])/(?:filament|filament-peek)(?:[/?#)"\'\s]|$)~i',
        'livewire_directive' => '/\bwire:[A-Za-z][\w.-]*\s*=/',
        'livewire_internal' => '/\bLivewire\.(?:dispatch|find|first|on|start)\s*\(/',
        'model_id' => '/(?:\bdata-model-id\b|["\'](?:modelId|model[_-]?id)["\'])\s*(?:=|:)\s*["\']?\d+/i',
        'page_id' => '/(?:\bdata-page-id\b|["\'](?:pageId|page[_-]?id)["\'])\s*(?:=|:)\s*["\']?\d+/i',
        'permission_marker' => '/["\']permissions?["\']\s*:/i',
        'signature_parameter' => '/\bsignature\s*[:=]\s*["\']?[^"\'\s,}]+/i',
        'signed_query' => '/[?&](?:expires|signature|token)=[^\s&<>"\']+/i',
    ];
}

function assertCapellPublicOutputIsSafe(TestResponse|string $response, string $context = 'public output'): void
{
    $content = $response instanceof TestResponse ? (string) $response->getContent() : $response;
    $failures = [];

    foreach (capellPublicOutputForbiddenTokens() as $token) {
        if (str_contains($content, $token)) {
            $failures[] = sprintf('%s contains [%s]', $context, $token);
        }
    }

    foreach (capellPublicOutputForbiddenPatterns() as $label => $pattern) {
        if (preg_match($pattern, $content) === 1) {
            $failures[] = sprintf('%s matches [%s]', $context, $label);
        }
    }

    expect($failures)->toBe([]);
}
