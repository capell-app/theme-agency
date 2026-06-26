<?php

declare(strict_types=1);

use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Tests\Fixtures\FakeCapabilityAction;

/**
 * Build a CapabilityData with sane defaults. Leaves requiredPackage null so the
 * registry's package-availability filter does not hide it in the test env.
 */
function makeCapability(
    string $key,
    CapabilityRiskEnum $risk = CapabilityRiskEnum::Read,
    bool $public = false,
): CapabilityData {
    return new CapabilityData(
        key: $key,
        name: $key,
        description: $key,
        scope: $key,
        server: CapabilityServerEnum::Site,
        risk: $risk,
        actionClass: FakeCapabilityAction::class,
        requiresConfirmation: false,
        public: $public,
    );
}

it('defaults public to false and serialises it in the payload', function (): void {
    $capability = makeCapability('discovery.list_themes');

    expect($capability->public)->toBeFalse();
    expect($capability->toPayload())->toHaveKey('public');
});
