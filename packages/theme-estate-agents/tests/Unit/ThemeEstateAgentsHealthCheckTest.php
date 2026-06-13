<?php

declare(strict_types=1);

use Capell\ThemeStudio\EstateAgents\Health\ThemeEstateAgentsHealthCheck;

it('reports the Estate Agents theme package as healthy', function (): void {
    expect(ThemeEstateAgentsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(ThemeEstateAgentsHealthCheck::passed())->toBeTrue();
});
