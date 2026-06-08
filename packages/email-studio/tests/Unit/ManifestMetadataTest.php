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
        ->toContain('transactional-email engine')
        ->toContain('safe {{ variable }} rendering')
        ->toContain('queued, auditable per-recipient send pipeline')
        ->not->toContain('replies')
        ->not->toContain('unsubscribe')
        ->and($composerDescription)
        ->toContain("Email Studio is Capell's transactional-email engine")
        ->toContain('queued, auditable per-recipient send pipeline')
        ->not->toContain('replies')
        ->not->toContain('unsubscribe')
        ->and($manifestDescription)
        ->toContain('Provider event webhooks write delivery events back into the audit trail')
        ->toContain('MailTracker-backed admin surface exposes sent-email records, opens, clicks, stored HTML, and retention controls')
        ->and($capabilities)
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

    $providerEventsCheck = collect(data_get($manifest, 'healthChecks', []))
        ->firstWhere('key', 'email-studio.provider-events');

    expect($providerEventsCheck)->toBeArray()
        ->and($providerEventsCheck['label'] ?? null)
        ->toBe('Provider webhook and inbound reply normalization foundations are present')
        ->and((string) ($providerEventsCheck['label'] ?? ''))
        ->not->toContain('local records');
});
