<?php

declare(strict_types=1);

use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\Core\Actions\CreateThemeAction;
use Capell\Core\Models\Blueprint;
use Capell\FoundationTheme\Actions\InstallFoundationThemeLayoutDefaultsAction;

function discovery_capability(string $key): CapabilityData
{
    return resolve(CapellAgentBridgeCapabilityRegistry::class)->get($key);
}

/**
 * Narrow a mixed capability-result value to an array so collection helpers can
 * infer their element types under static analysis.
 *
 * @return array<int|string, mixed>
 */
function discovery_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

/**
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
function discovery_execute(string $key, array $payload = []): array
{
    $capability = discovery_capability($key);
    $action = resolve($capability->actionClass);
    $result = $action->execute(new CapabilityInvocationData($capability, $payload));

    expect($result->ok)->toBeTrue();

    return $result->data;
}

it('registers every discovery capability as read-only and preview-safe', function (): void {
    $keys = [
        'capell.ai-creator.discovery.list_themes',
        'capell.ai-creator.discovery.list_page_types',
        'capell.ai-creator.discovery.list_section_types',
        'capell.ai-creator.discovery.list_layouts',
        'capell.ai-creator.discovery.get_site_spec_schema',
        'capell.ai-creator.discovery.validate_spec',
        'capell.ai-creator.interview.get',
    ];

    foreach ($keys as $key) {
        $capability = discovery_capability($key);

        expect($capability->scope)->toBe('capell.ai-creator.read')
            ->and($capability->risk->value)->toBe('read')
            ->and($capability->requiresConfirmation)->toBeFalse()
            ->and($capability->supportsPreview)->toBeTrue();
    }
});

it('lists installed themes and layouts', function (): void {
    InstallFoundationThemeLayoutDefaultsAction::run();
    CreateThemeAction::run('aurora', 'Aurora');

    $themes = discovery_execute('capell.ai-creator.discovery.list_themes')['themes'];
    $layouts = discovery_execute('capell.ai-creator.discovery.list_layouts')['layouts'];

    expect(collect($themes)->pluck('key'))->toContain('aurora')
        ->and(collect($layouts)->pluck('key'))->toContain('home', 'default');
});

it('lists page and section blueprints by type', function (): void {
    Blueprint::query()->create(['key' => 'landing', 'name' => 'Landing', 'type' => 'page', 'meta' => ['content_structure' => 'html']]);
    Blueprint::query()->create(['key' => 'hero', 'name' => 'Hero', 'type' => 'section', 'meta' => []]);

    $pageTypes = discovery_execute('capell.ai-creator.discovery.list_page_types')['page_types'];
    $sectionTypes = discovery_execute('capell.ai-creator.discovery.list_section_types')['section_types'];

    expect(collect($pageTypes)->pluck('key'))->toContain('landing')
        ->and(collect($pageTypes)->pluck('key'))->not->toContain('hero')
        ->and(collect($sectionTypes)->pluck('key'))->toContain('hero')
        ->and(collect($sectionTypes)->pluck('key'))->not->toContain('landing');
});

it('returns the site spec schema', function (): void {
    $schema = discovery_execute('capell.ai-creator.discovery.get_site_spec_schema')['schema'];

    expect($schema)->toHaveKey('properties.site')
        ->toHaveKey('properties.theme')
        ->toHaveKey('properties.pages')
        ->and($schema['required'])->toContain('site', 'theme', 'pages');
});

it('returns the interview script with required core, expansion, and rules', function (): void {
    $interview = discovery_execute('capell.ai-creator.interview.get')['interview'];

    expect($interview['version'])->toBe(1)
        ->and($interview['flow'])->toBe(['site_name', 'site_purpose', 'theme', 'pages', 'generate', 'delivery_target'])
        ->and(collect($interview['required'])->pluck('key'))->toContain('site_name', 'site_purpose', 'delivery_target')
        ->and(collect($interview['expansion'])->pluck('key'))->toContain('theme', 'pages')
        ->and($interview['rules'])->not->toBe([]);

    // delivery_target is asked at the close and carries the local/cloud follow-ups.
    $delivery = collect($interview['required'])->firstWhere('key', 'delivery_target');
    expect($delivery['ask_at'])->toBe('close')
        ->and($delivery['options'])->toBe(['preview', 'local', 'cloud'])
        ->and(collect($delivery['follow_ups'])->pluck('when'))->toContain('local', 'cloud');
});

it('validates a good spec and rejects a malformed one', function (): void {
    $good = discovery_execute('capell.ai-creator.discovery.validate_spec', [
        'spec' => [
            'site' => ['name' => 'Bluefin'],
            'theme' => ['key' => 'aurora'],
            'pages' => [
                ['name' => 'Home', 'slug' => 'home', 'title' => 'Welcome', 'pageType' => 'default'],
            ],
        ],
    ]);

    expect($good['valid'])->toBeTrue()
        ->and($good['errors'])->toBe([])
        ->and($good['normalized'])->toHaveKey('site');

    $bad = discovery_execute('capell.ai-creator.discovery.validate_spec', [
        'spec' => ['theme' => ['key' => 'aurora']],
    ]);

    expect($bad['valid'])->toBeFalse()
        ->and($bad['normalized'])->toBeNull()
        ->and($bad['errors'])->not->toBe([]);
});
