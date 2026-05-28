<?php

declare(strict_types=1);

use Capell\AgentBridge\Actions\BuildAgentBridgePromptAction;
use Capell\AgentBridge\Data\AgentBridgePromptData;
use Capell\AgentBridge\Filament\Pages\CapellAgentBridgePromptBuilderPage;
use Capell\AgentBridge\Models\CapellAgentBridgeAuditEntry;
use Capell\AgentBridge\Models\CapellAgentBridgeConfirmation;
use Carbon\CarbonImmutable;
use Filament\Schemas\Schema;

it('builds prompt builder text from complete and fallback state', function (): void {
    $prompt = BuildAgentBridgePromptAction::run(AgentBridgePromptData::fromArray([
        'goal' => 'refresh stale landing page cache',
        'area' => 'cache',
        'operation' => 'clear',
        'safety' => 'preview_first',
        'target' => 'Site ID 12',
        'constraints' => 'Only clear public frontend cache.',
        'success_criteria' => 'Cache clear capability preview is listed.',
    ]));
    $fallbackPrompt = BuildAgentBridgePromptAction::run(AgentBridgePromptData::fromArray([
        'goal' => 'inspect page readiness',
    ]));

    expect($prompt)
        ->toContain('refresh stale landing page cache')
        ->toContain('Area: cache')
        ->toContain('Only clear public frontend cache.')
        ->and($fallbackPrompt)->toContain('Area: other')
        ->and($fallbackPrompt)->toContain('Target/context:')
        ->and($fallbackPrompt)->toContain('Not provided.')
        ->and($fallbackPrompt)->toContain('Use Capell package boundaries, policies, and preview-first workflow.');
});

it('declares prompt builder navigation metadata and option sets', function (): void {
    $page = new CapellAgentBridgePromptBuilderPage;

    expect(CapellAgentBridgePromptBuilderPage::shouldRegisterNavigation())->toBeFalse()
        ->and(CapellAgentBridgePromptBuilderPage::getNavigationLabel())->toBe('Capell Agent Bridge')
        ->and($page->getTitle())->toBe('Capell Agent Bridge Prompt Builder')
        ->and(CapellAgentBridgePromptBuilderPage::areaOptions())->toHaveKeys([
            'pages',
            'cache',
            'seo',
            'redirects',
            'navigation',
            'packages',
            'other',
        ])
        ->and(CapellAgentBridgePromptBuilderPage::operationOptions())->toHaveKeys([
            'inspect',
            'create',
            'update',
            'disable',
            'clear',
            'regenerate',
            'recommend',
        ])
        ->and(CapellAgentBridgePromptBuilderPage::safetyOptions())->toHaveKeys([
            'preview_first',
            'read_only',
            'prepare_confirmation',
        ]);
});

it('declares prompt builder form schema and header action', function (): void {
    $page = new CapellAgentBridgePromptBuilderPage;
    $schema = $page->form(Schema::make());
    $headerActions = invokeAgentBridgePromptBuilderMethod($page, 'getHeaderActions', []);

    expect($schema->getComponents())->toHaveCount(1)
        ->and($headerActions)->toHaveCount(1);
});

it('casts agent bridge audit and confirmation payloads and usability state', function (): void {
    $audit = new CapellAgentBridgeAuditEntry;
    $audit->payload = ['capability' => 'capell.fake'];
    $audit->result = ['ok' => true];

    $usableConfirmation = new CapellAgentBridgeConfirmation;
    $usableConfirmation->expires_at = CarbonImmutable::now()->addMinute();
    $usableConfirmation->used_at = null;

    $usedConfirmation = new CapellAgentBridgeConfirmation;
    $usedConfirmation->expires_at = CarbonImmutable::now()->addMinute();
    $usedConfirmation->used_at = CarbonImmutable::now();

    $expiredConfirmation = new CapellAgentBridgeConfirmation;
    $expiredConfirmation->expires_at = CarbonImmutable::now()->subMinute();
    $expiredConfirmation->used_at = null;

    expect($audit->payload)->toBe(['capability' => 'capell.fake'])
        ->and($audit->result)->toBe(['ok' => true])
        ->and($usableConfirmation->isUsable())->toBeTrue()
        ->and($usedConfirmation->isUsable())->toBeFalse()
        ->and($expiredConfirmation->isUsable())->toBeFalse()
        ->and($usableConfirmation->getCasts())->toMatchArray([
            'payload' => 'array',
            'preview' => 'array',
            'expires_at' => 'immutable_datetime',
            'used_at' => 'immutable_datetime',
        ]);
});

/**
 * @param  array<int, mixed>  $parameters
 */
function invokeAgentBridgePromptBuilderMethod(
    CapellAgentBridgePromptBuilderPage $page,
    string $methodName,
    array $parameters,
): mixed {
    $reflectionMethod = new ReflectionMethod($page, $methodName);

    return $reflectionMethod->invokeArgs($page, $parameters);
}
