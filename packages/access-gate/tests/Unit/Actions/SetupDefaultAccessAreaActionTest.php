<?php

declare(strict_types=1);

use Capell\AccessGate\Actions\SetupDefaultAccessAreaAction;

it('defaults the claim landing url to the welcome page', function (): void {
    $area = resolve(SetupDefaultAccessAreaAction::class)->handle();

    expect($area->claim_landing_url)->toBe('/welcome');
});

it('respects a configured claim landing url', function (): void {
    config()->set('access-gate.install.default_area.claim_landing_url', '/members');

    $area = resolve(SetupDefaultAccessAreaAction::class)->handle();

    expect($area->claim_landing_url)->toBe('/members');
});

it('stores a null claim landing url when configured empty', function (): void {
    config()->set('access-gate.install.default_area.claim_landing_url', '');

    $area = resolve(SetupDefaultAccessAreaAction::class)->handle();

    expect($area->claim_landing_url)->toBeNull();
});
