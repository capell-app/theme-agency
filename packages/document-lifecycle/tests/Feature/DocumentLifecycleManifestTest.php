<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\DocumentLifecycle\Filament\Resources\Documents\DocumentResource;
use Capell\DocumentLifecycle\Manifest\DocumentResourceContribution;

it('declares the document admin resource as a manifest contribution', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $contribution = capell_test_collect($manifest['contributes'] ?? [])
        ->firstWhere('type', 'admin-resource');

    expect($contribution)
        ->toBeArray()
        ->and($contribution['class'] ?? null)->toBe(DocumentResourceContribution::class)
        ->and($contribution['resourceClass'] ?? null)->toBe(DocumentResource::class)
        ->and($contribution['group'] ?? null)->toBe('Document')
        ->and(DocumentResourceContribution::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(class_implements(DocumentResourceContribution::class))->toContain(RegistersExtensionAdminResource::class);
});
