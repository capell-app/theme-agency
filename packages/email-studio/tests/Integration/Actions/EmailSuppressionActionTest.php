<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\EmailStudio\Actions\CheckEmailSuppressionAction;
use Capell\EmailStudio\Actions\SuppressEmailAddressAction;
use Capell\EmailStudio\Enums\SuppressionReason;
use Capell\EmailStudio\Models\EmailSuppression;

it('checks active suppressions by normalized email hash and site scope', function (): void {
    Site::factory()->create(['id' => 12]);

    SuppressEmailAddressAction::run(
        email: 'Blocked@Example.com',
        reason: SuppressionReason::Manual,
        siteId: 12,
        siteScopeKey: 'site:12',
        source: 'admin',
    );

    expect(CheckEmailSuppressionAction::run('blocked@example.com', 'site:12'))->toBeTrue()
        ->and(CheckEmailSuppressionAction::run('blocked@example.com', 'site:99'))->toBeFalse();

    $suppression = EmailSuppression::query()->sole();

    expect($suppression->normalized_email)->toBe('blocked@example.com')
        ->and($suppression->email_hash)->toBe(hash('sha256', 'blocked@example.com'))
        ->and($suppression->site_id)->toBe(12)
        ->and($suppression->site_scope_key)->toBe('site:12');

    SuppressEmailAddressAction::run(
        email: 'global@example.com',
        reason: SuppressionReason::Complaint,
        siteId: null,
        siteScopeKey: 'global',
        source: 'provider',
    );

    $releasedSuppression = SuppressEmailAddressAction::run(
        email: 'released@example.com',
        reason: SuppressionReason::Manual,
        siteId: null,
        siteScopeKey: 'global',
        source: 'admin',
    );

    $releasedSuppression->update(['released_at' => now()->toImmutable()]);

    expect(CheckEmailSuppressionAction::run('global@example.com', 'site:12'))->toBeTrue()
        ->and(CheckEmailSuppressionAction::run('released@example.com', 'site:12'))->toBeFalse();
});
