<?php

declare(strict_types=1);

use Capell\AiCreator\Manifest\AiCreatorAdminPageContribution;
use Capell\AiCreator\Manifest\AiCreatorAgentBridgeCapabilitiesContribution;
use Capell\AiCreator\Manifest\AiCreatorModelsContribution;
use Capell\AiCreator\Models\AiCreatorSession;
use Capell\AiCreator\Providers\AiCreatorServiceProvider;
use Illuminate\Support\Facades\File;

it('positions ai creator as a normal capell creation assistant with package recommendations', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = ai_creator_json_file_array($packagePath . '/capell.json');
    $composer = ai_creator_json_file_array($packagePath . '/composer.json');
    $readme = File::get($packagePath . '/README.md');
    $overview = File::get($packagePath . '/docs/overview.md');

    expect($manifest['name'])->toBe('capell-app/ai-creator')
        ->and($manifest['displayName'])->toBe('AI Creator')
        ->and($composer['autoload']['psr-4'])->toHaveKey('Capell\\AiCreator\\')
        ->and(data_get($manifest, 'providers.runtime'))->toContain(AiCreatorServiceProvider::class)
        ->and(data_get($manifest, 'database.requiredTables'))->toContain('capell_ai_creator_sessions')
        ->and(data_get($manifest, 'dependencies.requires'))->toContain(
            'capell-app/agent-bridge',
            'capell-app/ai-orchestrator',
        )
        ->and(data_get($manifest, 'dependencies.supports'))->toContain(
            'capell-app/layout-builder',
            'capell-app/content-sections',
            'capell-app/structured-content-library',
            'capell-app/media-ai',
            'capell-app/seo-suite',
            'capell-app/insights',
        )
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'admin-page',
            'class' => AiCreatorAdminPageContribution::class,
            'pageClass' => null,
            'labelKey' => 'capell-ai-creator::package.admin_title',
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'model',
            'class' => AiCreatorModelsContribution::class,
            'models' => [
                AiCreatorSession::class,
            ],
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'agent-capability',
            'class' => AiCreatorAgentBridgeCapabilitiesContribution::class,
            'providerClass' => 'Capell\\AiCreator\\AgentBridge\\AiCreatorAgentBridgeCapabilityProvider',
        ])
        ->and($readme)->toContain('AI Creator')
        ->and($readme)->toContain('Works better with')
        ->and($overview)->toContain('ordinary existing Capell site');
});

/**
 * @return array<array-key, mixed>
 */
function ai_creator_json_file_array(string $path): array
{
    $decodedJson = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

    throw_unless(is_array($decodedJson), RuntimeException::class, sprintf('Expected %s to decode to an array.', $path));

    return $decodedJson;
}
