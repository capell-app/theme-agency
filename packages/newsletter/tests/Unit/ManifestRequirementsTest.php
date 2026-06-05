<?php

declare(strict_types=1);

use Capell\Newsletter\Actions\ResolveUtmAttributionAction;
use Capell\Newsletter\Actions\ScheduleNewsletterSendAction;
use Capell\Newsletter\Actions\UpdatePreferenceCenterAction;
use Capell\Newsletter\Filament\Resources\NewsletterSends\NewsletterSendResource;
use Capell\Newsletter\Filament\Resources\Segments\SegmentResource;
use Capell\Newsletter\Filament\Resources\Subscribers\SubscriberResource;
use Capell\Newsletter\Manifest\NewsletterAdminResourcesContribution;
use Capell\Newsletter\Manifest\NewsletterFrontendRoutesContribution;
use Capell\Newsletter\Manifest\NewsletterOverviewStatsContribution;
use Capell\Newsletter\Manifest\NewsletterOverviewWidgetContribution;
use Capell\Newsletter\Manifest\NewsletterSyncRetryScheduleContribution;

function newsletterManifest(): array
{
    return json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
}

it('declares implemented newsletter package contributions', function (): void {
    $manifest = newsletterManifest();
    $contributions = collect($manifest['contributes']);

    expect($manifest['contributionTraceability']['deferredContributions'])->toBe([])
        ->and($contributions->pluck('class')->all())->toContain(
            NewsletterAdminResourcesContribution::class,
            NewsletterOverviewWidgetContribution::class,
            NewsletterOverviewStatsContribution::class,
            NewsletterFrontendRoutesContribution::class,
            NewsletterSyncRetryScheduleContribution::class,
        );

    $adminResources = $contributions->firstWhere('class', NewsletterAdminResourcesContribution::class);
    $routes = $contributions->firstWhere('class', NewsletterFrontendRoutesContribution::class);
    $scheduledJob = $contributions->firstWhere('class', NewsletterSyncRetryScheduleContribution::class);

    expect($adminResources['resourceClasses'])->toContain(
        SubscriberResource::class,
        SegmentResource::class,
        NewsletterSendResource::class,
    )
        ->and($routes['routes'])->toContain(
            'capell-newsletter.unsubscribe',
            'capell-newsletter.preferences.show',
            'capell-newsletter.preferences.update',
            'capell-newsletter.provider-webhook',
        )
        ->and($scheduledJob['command'])->toBe('newsletter:sync-retry-due');
});

it('declares newsletter segmentation, preference center, campaign send, and attribution actions', function (): void {
    $manifest = newsletterManifest();

    expect($manifest['actions'])->toHaveKey('evaluateNewsletterSegment')
        ->and($manifest['actions'])->toHaveKey('scheduleNewsletterSend', ScheduleNewsletterSendAction::class)
        ->and($manifest['actions'])->toHaveKey('resolveUtmAttribution', ResolveUtmAttributionAction::class)
        ->and($manifest['actions'])->toHaveKey('updatePreferenceCenter', UpdatePreferenceCenterAction::class)
        ->and($manifest['capabilities'])->toContain(
            'newsletter-segments',
            'newsletter-preference-center',
            'newsletter-campaign-sends',
            'newsletter-utm-attribution',
            'newsletter-unsubscribe-routes',
            'newsletter-automation-hooks',
        );
});

it('declares the built newsletter marketplace screenshot contract', function (): void {
    $manifest = newsletterManifest();
    /** @var array{entries: list<array{id: string, screenshotPath: string}>} $contract */
    $contract = json_decode(
        (string) file_get_contents(__DIR__ . '/../../docs/screenshots.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $contractPaths = collect($contract['entries'])
        ->pluck('screenshotPath')
        ->map(fn (string $path): string => str_replace('packages/newsletter/', '', $path))
        ->all();

    $manifestPaths = collect($manifest['marketplace']['screenshots'])
        ->pluck('path')
        ->all();

    expect($manifestPaths)->toBe([
        'docs/assets/marketplace/extension-card.jpg',
        ...$contractPaths,
    ]);

    foreach ($manifestPaths as $path) {
        expect(is_file(__DIR__ . '/../../' . $path))->toBeTrue();
    }

    expect($contractPaths)->toHaveCount(13);
});
