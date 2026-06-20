<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\Newsletter\Actions\BuildNewsletterSendHandoffPayloadAction;
use Capell\Newsletter\Actions\ResolveUtmAttributionAction;
use Capell\Newsletter\Actions\ScheduleNewsletterSendAction;
use Capell\Newsletter\Actions\SubscribeFromPublicRequestAction;
use Capell\Newsletter\Actions\UpdatePreferenceCenterAction;
use Capell\Newsletter\Console\Commands\RequeueDueProviderSyncAttemptsCommand;
use Capell\Newsletter\Filament\Resources\NewsletterSends\NewsletterSendResource;
use Capell\Newsletter\Filament\Resources\Segments\SegmentResource;
use Capell\Newsletter\Filament\Resources\Subscribers\SubscriberResource;
use Capell\Newsletter\Health\NewsletterHealthCheck;
use Capell\Newsletter\Manifest\NewsletterAdminResourcesContribution;
use Capell\Newsletter\Manifest\NewsletterConsoleCommandsContribution;
use Capell\Newsletter\Manifest\NewsletterFrontendRoutesContribution;
use Capell\Newsletter\Manifest\NewsletterHealthContribution;
use Capell\Newsletter\Manifest\NewsletterOverviewFilamentWidgetContribution;
use Capell\Newsletter\Manifest\NewsletterOverviewStatsContribution;
use Capell\Newsletter\Manifest\NewsletterSettingsContribution;
use Capell\Newsletter\Manifest\NewsletterSyncRetryScheduleContribution;
use Capell\Newsletter\Settings\NewsletterSettings;

function newsletterManifest(): array
{
    return capell_json_file_array(__DIR__ . '/../../capell.json');
}

it('declares implemented newsletter package contributions', function (): void {
    $manifest = newsletterManifest();
    $manifestContributions = $manifest['contributes'] ?? [];
    throw_unless(is_array($manifestContributions), RuntimeException::class, 'Newsletter contributions must be arrays.');
    $contributions = collect($manifestContributions);

    expect($manifest['contributionTraceability']['deferredContributions'])->toBe([])
        ->and($contributions->pluck('class')->all())->toContain(
            NewsletterAdminResourcesContribution::class,
            NewsletterOverviewFilamentWidgetContribution::class,
            NewsletterOverviewStatsContribution::class,
            NewsletterFrontendRoutesContribution::class,
            NewsletterSyncRetryScheduleContribution::class,
            NewsletterConsoleCommandsContribution::class,
            NewsletterSettingsContribution::class,
            NewsletterHealthContribution::class,
        );

    $adminResources = $contributions->firstWhere('class', NewsletterAdminResourcesContribution::class);
    $routes = $contributions->firstWhere('class', NewsletterFrontendRoutesContribution::class);
    $scheduledJob = $contributions->firstWhere('class', NewsletterSyncRetryScheduleContribution::class);
    $consoleCommand = $contributions->firstWhere('class', NewsletterConsoleCommandsContribution::class);
    $settings = $contributions->firstWhere('class', NewsletterSettingsContribution::class);
    $healthCheck = $contributions->firstWhere('class', NewsletterHealthContribution::class);
    throw_unless(is_array($adminResources), RuntimeException::class, 'Expected newsletter admin resource contribution.');
    throw_unless(is_array($routes), RuntimeException::class, 'Expected newsletter route contribution.');
    throw_unless(is_array($scheduledJob), RuntimeException::class, 'Expected newsletter scheduled job contribution.');
    throw_unless(is_array($consoleCommand), RuntimeException::class, 'Expected newsletter console command contribution.');
    throw_unless(is_array($settings), RuntimeException::class, 'Expected newsletter settings contribution.');
    throw_unless(is_array($healthCheck), RuntimeException::class, 'Expected newsletter health check contribution.');

    expect($adminResources['resourceClasses'])->toContain(
        SubscriberResource::class,
        SegmentResource::class,
        NewsletterSendResource::class,
    )
        ->and($routes['routes'])->toContain(
            'capell-newsletter.subscribe',
            'capell-newsletter.unsubscribe',
            'capell-newsletter.unsubscribe.one-click',
            'capell-newsletter.preferences.show',
            'capell-newsletter.preferences.update',
            'capell-newsletter.provider-webhook',
        )
        ->and($scheduledJob['command'])->toBe('newsletter:sync-retry-due')
        ->and($manifest['commands']['syncRetry'])->toBe('newsletter:sync-retry-due')
        ->and($manifest['settings'])->toContain(NewsletterSettings::class)
        ->and($consoleCommand['commands'])->toBe(['newsletter:sync-retry-due'])
        ->and($consoleCommand['commandClasses'])->toBe([RequeueDueProviderSyncAttemptsCommand::class])
        ->and($settings['settingsClass'])->toBe(NewsletterSettings::class)
        ->and($settings['settingsGroup'])->toBe('newsletter')
        ->and($healthCheck['checkClass'])->toBe(NewsletterHealthCheck::class)
        ->and(class_implements(NewsletterConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(NewsletterSettingsContribution::class))->toContain(RegistersExtensionSetting::class)
        ->and(class_implements(NewsletterHealthContribution::class))->toContain(ChecksExtensionHealth::class);
});

it('declares newsletter segmentation, preference center, campaign send, and attribution actions', function (): void {
    $manifest = newsletterManifest();

    expect($manifest['actions'])->toHaveKey('evaluateNewsletterSegment')
        ->and($manifest['actions'])->toHaveKey('buildListUnsubscribeHeaders')
        ->and($manifest['actions'])->toHaveKey('buildNewsletterSendHandoffPayload', BuildNewsletterSendHandoffPayloadAction::class)
        ->and($manifest['actions'])->toHaveKey('scheduleNewsletterSend', ScheduleNewsletterSendAction::class)
        ->and($manifest['actions'])->toHaveKey('subscribeFromPublicRequest', SubscribeFromPublicRequestAction::class)
        ->and($manifest['actions'])->toHaveKey('resolveUtmAttribution', ResolveUtmAttributionAction::class)
        ->and($manifest['actions'])->toHaveKey('updatePreferenceCenter', UpdatePreferenceCenterAction::class)
        ->and($manifest['capabilities'])->toContain(
            'newsletter-segments',
            'newsletter-preference-center',
            'newsletter-campaign-sends',
            'newsletter-send-handoff',
            'newsletter-utm-attribution',
            'newsletter-unsubscribe-routes',
            'newsletter-automation-hooks',
            'newsletter-global-suppression',
            'newsletter-sync-exhaustion',
        );
});

it('declares the built newsletter marketplace screenshot contract', function (): void {
    $manifest = newsletterManifest();
    $contract = capell_json_file_array(__DIR__ . '/../../docs/screenshots.json');
    $contractEntries = $contract['entries'] ?? [];
    $marketplaceScreenshots = data_get($manifest, 'marketplace.screenshots');
    throw_unless(is_array($contractEntries), RuntimeException::class, 'Newsletter screenshot contract entries must be arrays.');
    throw_unless(is_array($marketplaceScreenshots), RuntimeException::class, 'Newsletter marketplace screenshots must be arrays.');

    $contractPaths = [];

    foreach ($contractEntries as $entry) {
        throw_unless(is_array($entry), RuntimeException::class, 'Newsletter screenshot contract entries must be arrays.');
        $screenshotPath = $entry['screenshotPath'] ?? null;
        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Newsletter screenshot paths must be strings.');

        $contractPaths[] = str_replace('packages/newsletter/', '', $screenshotPath);
    }

    $manifestPaths = [];

    foreach ($marketplaceScreenshots as $screenshot) {
        throw_unless(is_array($screenshot), RuntimeException::class, 'Newsletter marketplace screenshots must be arrays.');
        $path = $screenshot['path'] ?? null;
        throw_unless(is_string($path), RuntimeException::class, 'Newsletter marketplace screenshot paths must be strings.');

        $manifestPaths[] = $path;
    }

    expect($manifestPaths)->toBe([
        'docs/assets/marketplace/extension-card.jpg',
        ...$contractPaths,
    ]);

    foreach ($manifestPaths as $path) {
        expect(is_file(__DIR__ . '/../../' . $path))->toBeTrue();
    }

    expect($contractPaths)->toHaveCount(13);
});
