<?php

declare(strict_types=1);

it('keeps package summaries aligned to shipped email studio capabilities', function (): void {
    $packageRoot = dirname(__DIR__, 2);

    $manifest = json_decode(
        (string) file_get_contents($packageRoot . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $composer = json_decode(
        (string) file_get_contents($packageRoot . '/composer.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest)->toBeArray()
        ->and($composer)->toBeArray();

    $marketplaceSummary = (string) data_get($manifest, 'marketplace.summary');
    $manifestDescription = (string) data_get($manifest, 'description');
    $composerDescription = (string) data_get($composer, 'description');
    $capabilities = data_get($manifest, 'capabilities', []);

    expect($marketplaceSummary)
        ->toContain('transactional-email engine and template authoring surface')
        ->toContain('safe {{ variable }} rendering')
        ->toContain('auth email replacements')
        ->toContain('preview/test-send')
        ->not->toContain('replies')
        ->not->toContain('unsubscribe')
        ->and($composerDescription)
        ->toContain("Email Studio is Capell's transactional-email engine")
        ->toContain('queued, auditable per-recipient send pipeline')
        ->not->toContain('replies')
        ->not->toContain('unsubscribe')
        ->and($manifestDescription)
        ->toContain('Packages register static Blade-backed template definitions')
        ->toContain('Auth email overrides replace Laravel verification and password reset messages by default')
        ->and($capabilities)
        ->toContain('auth-email-overrides')
        ->toContain('static-template-fallback')
        ->toContain('template-authoring-admin')
        ->toContain('template-theme-editor')
        ->toContain('provider-normalization-foundation')
        ->toContain('provider-event-ingestion')
        ->toContain('delivery-audit')
        ->toContain('mail-tracker-admin')
        ->toContain('mail-tracker-open-click-tracking')
        ->toContain('mail-tracker-retention')
        ->not->toContain('inbound-replies')
        ->not->toContain('one-click-unsubscribe');
});

it('describes provider event and reply health as normalization foundations only', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $healthChecks = data_get($manifest, 'healthChecks', []);
    $healthChecks = is_array($healthChecks) ? $healthChecks : [];

    $providerEventsCheck = collect($healthChecks)
        ->firstWhere('key', 'email-studio.provider-events');
    $templateAuthoringCheck = collect($healthChecks)
        ->firstWhere('key', 'email-studio.template-authoring');
    $providerEventsLabel = is_array($providerEventsCheck) ? $providerEventsCheck['label'] ?? null : null;
    $templateAuthoringLabel = is_array($templateAuthoringCheck) ? $templateAuthoringCheck['label'] ?? null : null;

    expect($providerEventsCheck)->toBeArray()
        ->and($providerEventsLabel)
        ->toBe('Provider webhook and inbound reply normalization foundations are present')
        ->and(is_string($providerEventsLabel) ? $providerEventsLabel : '')
        ->not->toContain('local records')
        ->and($templateAuthoringCheck)->toBeArray()
        ->and($templateAuthoringLabel)
        ->toBe('Template authoring resources, themes, and test-send actions are available');
});
