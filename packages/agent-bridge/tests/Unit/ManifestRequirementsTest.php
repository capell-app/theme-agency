<?php

declare(strict_types=1);

use Capell\AgentBridge\Console\Commands\PruneAgentBridgeAuditEntriesCommand;
use Capell\AgentBridge\Extenders\AgentBridgeUserSchemaExtender;
use Capell\AgentBridge\Filament\Pages\CapellAgentBridgePromptBuilderPage;
use Capell\AgentBridge\Filament\Settings\AgentBridgeSettingsSchema;
use Capell\AgentBridge\Health\AgentBridgeHealthCheck;
use Capell\AgentBridge\Manifest\AgentBridgeAdminPageContribution;
use Capell\AgentBridge\Manifest\AgentBridgeAuditPruneScheduleContribution;
use Capell\AgentBridge\Manifest\AgentBridgeBuiltInCapabilitiesContribution;
use Capell\AgentBridge\Manifest\AgentBridgeConsoleCommandsContribution;
use Capell\AgentBridge\Manifest\AgentBridgeMigrationsContribution;
use Capell\AgentBridge\Manifest\AgentBridgeModelsContribution;
use Capell\AgentBridge\Manifest\AgentBridgeRoutesContribution;
use Capell\AgentBridge\Manifest\AgentBridgeSettingsContribution;
use Capell\AgentBridge\Manifest\AgentBridgeUserSchemaExtenderContribution;
use Capell\AgentBridge\Models\CapellAgentBridgeAuditEntry;
use Capell\AgentBridge\Models\CapellAgentBridgeConfirmation;
use Capell\AgentBridge\Models\CapellAgentBridgeSavedPrompt;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Capell\AgentBridge\Settings\AgentBridgeSettings;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\Core\Contracts\Extensions\RunsExtensionMigration;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Capell\Core\Support\Manifest\ManifestValidator;
use Illuminate\Support\Facades\File;

describe('agent-bridge capell.json manifest', function (): void {
    $packagePath = dirname(__DIR__, 2);

    $manifest = fn (): array => manifestJsonFileArray($packagePath . '/capell.json');

    $composer = fn (): array => manifestJsonFileArray($packagePath . '/composer.json');

    $screenshotsContract = fn (): array => manifestJsonFileArray($packagePath . '/docs/screenshots.json');

    it('uses buyer-facing app extension content', function () use ($manifest, $composer): void {
        $manifestData = $manifest();
        $composerData = $composer();

        $marketplace = manifestMarketplace($manifestData);
        $marketplaceSummary = manifestString($marketplace, 'summary');

        expect($manifestData['description'])->toContain('preview-then-confirm')
            ->and($manifestData['description'])->toContain('audited operations')
            ->and($marketplaceSummary)->toContain('scoped tokens')
            ->and($marketplaceSummary)->toContain('full audit trail')
            ->and($composerData['description'])->toContain('Scoped MCP access')
            ->and($composerData['keywords'])->toContain('model-context-protocol')
            ->and($composerData['keywords'])->toContain('scoped-access');
    });

    it('declares only buyer-facing generated screenshots for marketplace display', function () use ($manifest): void {
        $manifestData = $manifest();
        $screenshots = manifestScreenshots($manifestData);

        $screenshotPaths = collect($screenshots)
            ->pluck('path')
            ->values()
            ->all();

        expect($screenshotPaths)->toBe([
            'docs/screenshots/agent-bridge-prompt-builder-page.png',
            'docs/screenshots/agent-bridge-prompt-builder-page-dark.png',
        ]);
    });

    it('keeps duplicated runner captures out of required marketplace screenshot slots', function () use ($screenshotsContract): void {
        $contractData = $screenshotsContract();
        $entries = collect(manifestEntries($contractData))->keyBy('id');

        foreach ([
            'token-management-or-setup-surface',
            'capability-preview-and-confirmation-flow',
            'audit-entry-review',
            'agent-bridge-server-health-output',
        ] as $blockedEntryId) {
            $entry = $entries->get($blockedEntryId);

            throw_unless(is_array($entry), RuntimeException::class, sprintf('Missing Agent Bridge screenshot entry [%s].', $blockedEntryId));

            expect($entry['required'] ?? null)->toBeFalse()
                ->and($entry['notes'] ?? '')->toContain('Blocked from marketplace promotion')
                ->and($entry['notes'] ?? '')->toContain('duplicates the prompt builder');
        }
    });

    it('keeps marketplace screenshots readable and backed by files', function () use ($manifest, $packagePath): void {
        $manifestData = $manifest();

        foreach (manifestScreenshots($manifestData) as $screenshot) {
            $path = manifestString($screenshot, 'path');
            $alt = manifestString($screenshot, 'alt');
            $caption = manifestString($screenshot, 'caption');

            expect($path)->toStartWith('docs/screenshots/')
                ->and(File::exists($packagePath . '/' . $path))->toBeTrue()
                ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
                ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
        }
    });

    it('declares shipped extension contributions', function () use ($packagePath): void {
        $manifestData = capell_json_file_array($packagePath . '/capell.json');
        $composerData = capell_json_file_array($packagePath . '/composer.json');
        $contributions = data_get($manifestData, 'contributes');

        throw_unless(is_array($contributions), RuntimeException::class, 'Expected Agent Bridge manifest contributions.');

        (new ManifestValidator)->validate($manifestData, $composerData, 'capell-app/agent-bridge', $packagePath . '/capell.json');

        expect($manifestData)
            ->toHaveKey('manifest-version', 3)
            ->toHaveKey('name', 'capell-app/agent-bridge')
            ->toHaveKey('namespace', 'Capell\\AgentBridge')
            ->and(data_get($manifestData, 'database.requiredTables', []))->toBe([
                'capell_agent_bridge_tokens',
                'capell_agent_bridge_confirmations',
                'capell_agent_bridge_audit_entries',
                'capell_agent_bridge_saved_prompts',
            ])
            ->and(data_get($manifestData, 'commands.pruneAudit'))->toBe('capell:agent-bridge-prune-audit')
            ->and(data_get($manifestData, 'settings'))->toBe([AgentBridgeSettings::class])
            ->and(data_get($manifestData, 'healthChecks.0.class'))->toBe(AgentBridgeHealthCheck::class)
            ->and(data_get($manifestData, 'contributionTraceability.deferredContributions'))->toBe([]);

        expect($contributions)
            ->toContain([
                'type' => 'admin-page',
                'class' => AgentBridgeAdminPageContribution::class,
                'pageClass' => CapellAgentBridgePromptBuilderPage::class,
                'labelKey' => 'capell-agent-bridge::admin.prompt_builder_title',
            ])
            ->toContain([
                'type' => 'schema-extender',
                'class' => AgentBridgeUserSchemaExtenderContribution::class,
                'extenderClass' => AgentBridgeUserSchemaExtender::class,
                'tag' => 'capell-admin:user-schema-extenders',
            ])
            ->toContain([
                'type' => 'model',
                'class' => AgentBridgeModelsContribution::class,
                'modelClasses' => [
                    CapellAgentBridgeToken::class,
                    CapellAgentBridgeConfirmation::class,
                    CapellAgentBridgeAuditEntry::class,
                    CapellAgentBridgeSavedPrompt::class,
                ],
            ])
            ->toContain([
                'type' => 'route',
                'class' => AgentBridgeRoutesContribution::class,
                'routes' => [
                    'capell-agent-bridge.home',
                    'agent-bridge/capell/knowledge',
                    'agent-bridge/capell',
                ],
                'defaultEnabledRoutes' => ['agent-bridge/capell'],
            ])
            ->toContain([
                'type' => 'setting',
                'class' => AgentBridgeSettingsContribution::class,
                'settingsClass' => AgentBridgeSettings::class,
                'settingsGroup' => 'agent_bridge',
                'settingsSchema' => AgentBridgeSettingsSchema::class,
            ])
            ->toContain([
                'type' => 'migration',
                'class' => AgentBridgeMigrationsContribution::class,
                'tables' => [
                    'capell_agent_bridge_tokens',
                    'capell_agent_bridge_confirmations',
                    'capell_agent_bridge_audit_entries',
                    'capell_agent_bridge_saved_prompts',
                ],
            ])
            ->toContain([
                'type' => 'console-command',
                'class' => AgentBridgeConsoleCommandsContribution::class,
                'commands' => ['capell:agent-bridge-prune-audit'],
                'commandClasses' => [PruneAgentBridgeAuditEntriesCommand::class],
            ])
            ->toContain([
                'type' => 'scheduled-job',
                'class' => AgentBridgeAuditPruneScheduleContribution::class,
                'command' => 'capell:agent-bridge-prune-audit',
                'name' => 'capell-agent-bridge-prune-audit',
                'frequency' => 'daily',
                'retentionDaysConfig' => 'capell-agent-bridge.audit_retention_days',
            ])
            ->toContain([
                'type' => 'agent-capability',
                'class' => AgentBridgeBuiltInCapabilitiesContribution::class,
                'capabilities' => [
                    'capell.cache.clear',
                    'capell.pages.create_draft',
                    'capell.pages.update_draft',
                    'capell.pages.disable',
                    'capell.pages.inspect_readiness',
                ],
                'requiresPreviewConfirmation' => true,
            ])
            ->toContain([
                'type' => 'health-check',
                'class' => AgentBridgeHealthCheck::class,
            ]);

        expect(class_implements(AgentBridgeRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
            ->and(class_implements(AgentBridgeSettingsContribution::class))->toContain(RegistersExtensionSetting::class)
            ->and(class_implements(AgentBridgeMigrationsContribution::class))->toContain(RunsExtensionMigration::class)
            ->and(class_implements(AgentBridgeAdminPageContribution::class))->toContain(ExtensionContribution::class)
            ->and(class_implements(AgentBridgeBuiltInCapabilitiesContribution::class))->toContain(ExtensionContribution::class)
            ->and(class_implements(AgentBridgeConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
            ->and(class_implements(AgentBridgeAuditPruneScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
            ->and(class_implements(AgentBridgeModelsContribution::class))->toContain(ExtensionContribution::class)
            ->and(class_implements(AgentBridgeUserSchemaExtenderContribution::class))->toContain(ExtensionContribution::class)
            ->and(class_implements(AgentBridgeHealthCheck::class))->toContain(ChecksExtensionHealth::class);
    });

    it('documents audit retention and package-safe setup guidance', function () use ($packagePath): void {
        $readme = File::get($packagePath . '/README.md');
        $overview = File::get($packagePath . '/docs/overview.md');
        $capabilities = File::get($packagePath . '/docs/capabilities.md');

        foreach ([$readme, $overview] as $document) {
            expect($document)
                ->toContain('capell:agent-bridge-prune-audit')
                ->toContain('Migration impact: run host migrations through the package install flow before opening package surfaces')
                ->not->toContain('Deletion/retention behaviour: Docs gap');
        }

        expect($capabilities)
            ->toContain('capell-agent-bridge.audit_retention_days')
            ->toContain('capell-agent-bridge.routes.home')
            ->toContain('capell-agent-bridge.routes.knowledge')
            ->toContain('capell-agent-bridge.routes.site')
            ->not->toContain('capell-agent-bridge.home')
            ->not->toContain('capell-agent-bridge.knowledge');
    });
});

/**
 * @return array<string, mixed>
 */
function manifestJsonFileArray(string $path): array
{
    $decoded = json_decode(
        File::get($path),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($decoded), RuntimeException::class, sprintf('Expected [%s] to decode to an array.', $path));

    $normalized = [];

    foreach ($decoded as $key => $value) {
        if (is_string($key)) {
            $normalized[$key] = $value;
        }
    }

    return $normalized;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<array<string, mixed>>
 */
function manifestEntries(array $manifest): array
{
    $entries = $manifest['entries'] ?? null;

    throw_unless(is_array($entries), RuntimeException::class, 'Expected manifest entries array.');

    return array_values(array_map(
        static fn (array $entry): array => $entry,
        array_filter($entries, static fn (mixed $entry): bool => is_array($entry)),
    ));
}

/**
 * @param  array<string, mixed>  $manifest
 * @return array<string, mixed>
 */
function manifestMarketplace(array $manifest): array
{
    $marketplace = $manifest['marketplace'] ?? null;

    throw_unless(is_array($marketplace), RuntimeException::class, 'Expected marketplace manifest array.');

    $normalized = [];

    foreach ($marketplace as $key => $value) {
        if (is_string($key)) {
            $normalized[$key] = $value;
        }
    }

    return $normalized;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<array<string, mixed>>
 */
function manifestScreenshots(array $manifest): array
{
    $screenshots = manifestMarketplace($manifest)['screenshots'] ?? null;

    throw_unless(is_array($screenshots), RuntimeException::class, 'Expected marketplace screenshots array.');

    return array_values(array_map(
        static fn (array $screenshot): array => $screenshot,
        array_filter($screenshots, static fn (mixed $screenshot): bool => is_array($screenshot)),
    ));
}

/**
 * @param  array<string, mixed>  $values
 */
function manifestString(array $values, string $key): string
{
    $value = $values[$key] ?? null;

    return is_string($value) ? $value : '';
}
