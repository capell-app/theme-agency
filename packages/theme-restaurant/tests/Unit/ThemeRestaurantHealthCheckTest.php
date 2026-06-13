<?php

declare(strict_types=1);

use Capell\ThemeStudio\Restaurant\Health\ThemeRestaurantHealthCheck;

it('reports the Restaurant theme package as healthy', function (): void {
    expect(ThemeRestaurantHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(ThemeRestaurantHealthCheck::passed())->toBeTrue();
});
