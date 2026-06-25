<?php

declare(strict_types=1);

use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Illuminate\Support\Facades\Storage;

function exportCapability(): CapabilityData
{
    return resolve(CapellAgentBridgeCapabilityRegistry::class)->get('capell.ai-creator.export_site');
}

/**
 * @return array<string, mixed>
 */
function exportPayload(): array
{
    return [
        'spec' => [
            'site' => ['name' => 'Bluefin Coffee'],
            'theme' => ['key' => 'bluefin'],
            'pages' => [
                ['name' => 'Home', 'slug' => 'home', 'title' => 'Welcome', 'pageType' => 'default'],
            ],
        ],
        'project_name' => 'Bluefin Coffee',
    ];
}

it('registers export_site as a low-risk write capability', function (): void {
    $capability = exportCapability();

    expect($capability->scope)->toBe('capell.ai-creator.write')
        ->and($capability->risk->value)->toBe('low')
        ->and($capability->actionClass)->toContain('ExportSiteCapabilityAction');
});

it('previews an export without writing anything', function (): void {
    Storage::fake('local');

    $result = resolve(exportCapability()->actionClass)->preview(
        new CapabilityInvocationData(exportCapability(), exportPayload()),
    );

    expect($result->ok)->toBeTrue()
        ->and($result->data['would_export'])->toBeTrue();

    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('writes a portable spec artifact and returns the install command', function (): void {
    Storage::fake('local');

    $result = resolve(exportCapability()->actionClass)->execute(
        new CapabilityInvocationData(exportCapability(), exportPayload()),
    );

    expect($result->ok)->toBeTrue()
        ->and($result->data['path'])->toBe('ai-creator/exports/bluefin-coffee.capell-spec.json')
        ->and($result->data['install_command'])->toBe('php artisan capell:install --spec=bluefin-coffee.capell-spec.json');

    Storage::disk('local')->assertExists('ai-creator/exports/bluefin-coffee.capell-spec.json');

    $written = json_decode((string) Storage::disk('local')->get('ai-creator/exports/bluefin-coffee.capell-spec.json'), true);
    expect($written)->toHaveKey('site')
        ->and(data_get($written, 'site.name'))->toBe('Bluefin Coffee');
});
