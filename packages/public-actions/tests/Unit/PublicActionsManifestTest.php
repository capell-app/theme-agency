<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\PublicActions\Console\Commands\PrunePublicActionSubmissionsCommand;
use Capell\PublicActions\Filament\Resources\Destinations\PublicActionDestinationResource;
use Capell\PublicActions\Filament\Resources\DispatchAttempts\PublicActionDispatchAttemptResource;
use Capell\PublicActions\Filament\Resources\IntegrationTokens\PublicActionIntegrationTokenResource;
use Capell\PublicActions\Filament\Resources\PublicActions\PublicActionResource;
use Capell\PublicActions\Filament\Resources\Submissions\PublicActionSubmissionResource;
use Capell\PublicActions\Health\PublicActionsHealthCheck;
use Capell\PublicActions\Manifest\PublicActionsAdminResourcesContribution;
use Capell\PublicActions\Manifest\PublicActionsConsoleCommandsContribution;
use Capell\PublicActions\Manifest\PublicActionsHealthContribution;
use Capell\PublicActions\Manifest\PublicActionsModelsContribution;
use Capell\PublicActions\Manifest\PublicActionsRoutesContribution;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionIntegrationToken;
use Capell\PublicActions\Models\PublicActionSubmission;

it('does not advertise cache dependency blocking without an implementation', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect(data_get($manifest, 'capabilities'))->not->toContain('cache-blocking')
        ->and(data_get($manifest, 'capabilities'))->toContain('captcha-spam-protection')
        ->and(data_get($manifest, 'performance.cacheSafety.cacheable'))->toBeFalse()
        ->and(data_get($manifest, 'performance.cacheSafety.invalidationSources'))->toBe([]);
});

it('declares implemented public action package contributions', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $contributes = data_get($manifest, 'contributes');

    throw_unless(is_array($contributes), RuntimeException::class, 'Expected public actions manifest contributions.');

    $contributions = collect($contributes);

    $adminResources = $contributions->firstWhere('class', PublicActionsAdminResourcesContribution::class);
    $models = $contributions->firstWhere('class', PublicActionsModelsContribution::class);
    $routes = $contributions->firstWhere('class', PublicActionsRoutesContribution::class);
    $consoleCommand = $contributions->firstWhere('class', PublicActionsConsoleCommandsContribution::class);
    $healthCheck = $contributions->firstWhere('class', PublicActionsHealthContribution::class);
    throw_unless(is_array($adminResources), RuntimeException::class, 'Expected public actions admin resource contribution.');
    throw_unless(is_array($models), RuntimeException::class, 'Expected public actions model contribution.');
    throw_unless(is_array($routes), RuntimeException::class, 'Expected public actions route contribution.');
    throw_unless(is_array($consoleCommand), RuntimeException::class, 'Expected public actions console command contribution.');
    throw_unless(is_array($healthCheck), RuntimeException::class, 'Expected public actions health check contribution.');

    expect(data_get($manifest, 'database.requiredTables'))->toBe([
        'public_actions',
        'public_action_destinations',
        'public_action_submissions',
        'public_action_dispatch_attempts',
        'public_action_integration_tokens',
    ])
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([])
        ->and($adminResources['resourceClasses'])->toBe([
            PublicActionResource::class,
            PublicActionDestinationResource::class,
            PublicActionSubmissionResource::class,
            PublicActionDispatchAttemptResource::class,
            PublicActionIntegrationTokenResource::class,
        ])
        ->and($models['modelClasses'])->toBe([
            PublicAction::class,
            PublicActionDestination::class,
            PublicActionSubmission::class,
            PublicActionDispatchAttempt::class,
            PublicActionIntegrationToken::class,
        ])
        ->and($routes['routes'])->toBe([
            'capell-public-actions.show',
            'capell-public-actions.submit',
            'capell-public-actions.zapier.me',
            'capell-public-actions.zapier.actions',
            'capell-public-actions.zapier.actions.submit',
            'capell-public-actions.zapier.submissions',
        ])
        ->and($consoleCommand['commands'])->toBe(['capell:public-actions:prune-submissions'])
        ->and($consoleCommand['commandClasses'])->toBe([PrunePublicActionSubmissionsCommand::class])
        ->and($healthCheck['checkClass'])->toBe(PublicActionsHealthCheck::class)
        ->and(class_implements(PublicActionsAdminResourcesContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PublicActionsModelsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(PublicActionsRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(PublicActionsConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(PublicActionsHealthContribution::class))->toContain(ChecksExtensionHealth::class);
});
