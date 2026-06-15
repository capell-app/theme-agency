<?php

declare(strict_types=1);

use Capell\AccessGate\Actions\PruneAccessGateRecordsAction;
use Capell\AccessGate\Enums\BrowserTokenStatus;
use Capell\AccessGate\Enums\ClaimTokenStatus;
use Capell\AccessGate\Enums\EventType;
use Capell\AccessGate\Enums\GrantStatus;
use Capell\AccessGate\Enums\RegistrationStatus;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Models\Event;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;

it('prunes stale access records without deleting active grants', function (): void {
    $area = Area::factory()->create();
    $grant = Grant::factory()->for($area, 'area')->create([
        'status' => GrantStatus::Active,
    ]);

    $oldExpiredBrowserToken = BrowserToken::factory()->for($area, 'area')->for($grant, 'grant')->create([
        'status' => BrowserTokenStatus::Expired,
        'expires_at' => now()->subDays(120),
        'updated_at' => now()->subDays(120),
    ]);
    $oldActiveExpiredBrowserToken = BrowserToken::factory()->for($area, 'area')->for($grant, 'grant')->create([
        'status' => BrowserTokenStatus::Active,
        'expires_at' => now()->subDays(120),
        'updated_at' => now(),
    ]);
    $recentRevokedBrowserToken = BrowserToken::factory()->for($area, 'area')->for($grant, 'grant')->create([
        'status' => BrowserTokenStatus::Revoked,
        'revoked_at' => now(),
        'updated_at' => now(),
    ]);

    $oldExpiredClaimToken = ClaimToken::factory()->for($area, 'area')->for($grant, 'grant')->create([
        'status' => ClaimTokenStatus::Expired,
        'expires_at' => now()->subDays(120),
        'updated_at' => now()->subDays(120),
    ]);
    $oldActiveExpiredClaimToken = ClaimToken::factory()->for($area, 'area')->for($grant, 'grant')->create([
        'status' => ClaimTokenStatus::Active,
        'expires_at' => now()->subDays(120),
        'updated_at' => now(),
    ]);
    $recentClaimedClaimToken = ClaimToken::factory()->for($area, 'area')->for($grant, 'grant')->create([
        'status' => ClaimTokenStatus::Claimed,
        'consumed_at' => now(),
        'updated_at' => now(),
    ]);

    $oldExpiredRegistration = Registration::factory()->for($area, 'area')->create([
        'status' => RegistrationStatus::Expired,
        'expired_at' => now()->subDays(120),
    ]);
    $recentExpiredRegistration = Registration::factory()->for($area, 'area')->create([
        'status' => RegistrationStatus::Expired,
        'expired_at' => now(),
    ]);
    $oldPendingRegistration = Registration::factory()->for($area, 'area')->create([
        'status' => RegistrationStatus::Pending,
        'requested_at' => now()->subDays(120),
    ]);

    $oldEvent = Event::factory()->for($area, 'area')->create([
        'type' => EventType::RegistrationCreated,
        'occurred_at' => now()->subDays(400),
    ]);
    $recentEvent = Event::factory()->for($area, 'area')->create([
        'type' => EventType::RegistrationCreated,
        'occurred_at' => now(),
    ]);

    $dryRun = PruneAccessGateRecordsAction::run(dryRun: true);

    expect($dryRun->toArray())->toMatchArray([
        'browser_tokens' => 2,
        'claim_tokens' => 2,
        'registrations' => 1,
        'events' => 1,
        'total' => 6,
    ])
        ->and(BrowserToken::query()->count())->toBe(3)
        ->and(ClaimToken::query()->count())->toBe(3)
        ->and(Registration::query()->count())->toBe(3)
        ->and(Event::query()->count())->toBe(2);

    $pruned = PruneAccessGateRecordsAction::run();

    expect($pruned->total())->toBe(6)
        ->and(BrowserToken::query()->whereKey($oldExpiredBrowserToken->getKey())->exists())->toBeFalse()
        ->and(BrowserToken::query()->whereKey($oldActiveExpiredBrowserToken->getKey())->exists())->toBeFalse()
        ->and(BrowserToken::query()->whereKey($recentRevokedBrowserToken->getKey())->exists())->toBeTrue()
        ->and(ClaimToken::query()->whereKey($oldExpiredClaimToken->getKey())->exists())->toBeFalse()
        ->and(ClaimToken::query()->whereKey($oldActiveExpiredClaimToken->getKey())->exists())->toBeFalse()
        ->and(ClaimToken::query()->whereKey($recentClaimedClaimToken->getKey())->exists())->toBeTrue()
        ->and(Registration::query()->whereKey($oldExpiredRegistration->getKey())->exists())->toBeFalse()
        ->and(Registration::query()->whereKey($recentExpiredRegistration->getKey())->exists())->toBeTrue()
        ->and(Registration::query()->whereKey($oldPendingRegistration->getKey())->exists())->toBeTrue()
        ->and(Event::query()->whereKey($oldEvent->getKey())->exists())->toBeFalse()
        ->and(Event::query()->whereKey($recentEvent->getKey())->exists())->toBeTrue()
        ->and(Grant::query()->whereKey($grant->getKey())->exists())->toBeTrue();
});
