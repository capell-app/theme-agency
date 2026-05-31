<?php

declare(strict_types=1);

use Capell\PublishingStudio\Actions\ExtendPreviewLinkAction;
use Capell\PublishingStudio\Actions\RevokePreviewLinkAction;
use Capell\PublishingStudio\Models\PreviewLink;
use Capell\PublishingStudio\Models\Workspace;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;

uses(CreatesAdminUser::class);

it('revoke action sets revoked_at and makes the link unusable', function (): void {
    $workspace = Workspace::factory()->create();
    $this->actingAsAdmin();
    $actor = auth()->user();

    expect($actor)->toBeInstanceOf(Authenticatable::class);

    $link = PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => PreviewLink::generateToken(),
        'issued_at' => CarbonImmutable::now(),
        'expires_at' => CarbonImmutable::now()->addHour(),
    ]);

    expect($link->isRevoked())->toBeFalse()
        ->and($link->isUsable())->toBeTrue();

    $revoked = (new RevokePreviewLinkAction)->handle($link, $actor);

    expect($revoked->revoked_at)->not->toBeNull()
        ->and($revoked->isRevoked())->toBeTrue()
        ->and($revoked->isUsable())->toBeFalse();

    $fresh = publishingStudioTestInstance(PreviewLink::query()->find($link->id), PreviewLink::class);
    expect($fresh->revoked_at)->not->toBeNull();
});

it('extend action adds minutes to the existing expires_at and leaves the token unchanged', function (): void {
    $workspace = Workspace::factory()->create();
    $this->actingAsAdmin();
    $actor = auth()->user();

    expect($actor)->toBeInstanceOf(Authenticatable::class);
    $originalToken = PreviewLink::generateToken();
    $originalExpiresAt = CarbonImmutable::now()->addHour();

    $link = PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => $originalToken,
        'issued_at' => CarbonImmutable::now(),
        'expires_at' => $originalExpiresAt,
    ]);

    $extended = (new ExtendPreviewLinkAction)->handle($link, 30, $actor);

    $expectedExpiresAt = $originalExpiresAt->addMinutes(30);

    expect($extended->token)->toBe($originalToken)
        ->and($extended->expires_at->timestamp)->toBe($expectedExpiresAt->timestamp);

    $fresh = publishingStudioTestInstance(PreviewLink::query()->find($link->id), PreviewLink::class);
    expect($fresh->expires_at->timestamp)->toBe($expectedExpiresAt->timestamp);
});

it('extend action bases the new expiry on the current expires_at, not on now', function (): void {
    $workspace = Workspace::factory()->create();
    $this->actingAsAdmin();
    $actor = auth()->user();

    expect($actor)->toBeInstanceOf(Authenticatable::class);

    $originalExpiresAt = CarbonImmutable::now()->addHours(2);

    $link = PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => PreviewLink::generateToken(),
        'issued_at' => CarbonImmutable::now(),
        'expires_at' => $originalExpiresAt,
    ]);

    (new ExtendPreviewLinkAction)->handle($link, 60, $actor);

    $fresh = publishingStudioTestInstance(PreviewLink::query()->find($link->id), PreviewLink::class);

    $expectedExpiresAt = $originalExpiresAt->addMinutes(60);
    expect($fresh->expires_at->timestamp)->toBe($expectedExpiresAt->timestamp);
});
