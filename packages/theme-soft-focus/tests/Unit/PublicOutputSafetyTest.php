<?php

declare(strict_types=1);

use Capell\FoundationTheme\Testing\AssertsPublicThemeOutputSafety;

uses(AssertsPublicThemeOutputSafety::class);

it('keeps public Blade free of authoring, package metadata, and database access', function (): void {
    $this->assertClassicThemeOutputIsSafe(__DIR__ . '/../../resources/views', 'capell-app/theme-soft-focus');
});

it('keeps the @php block count within the frozen baseline and static calls whitelisted', function (): void {
    $this->assertPhpBlockPolicy(__DIR__ . '/../../resources/views', 25);
});
