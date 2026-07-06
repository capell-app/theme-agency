<?php

declare(strict_types=1);

use Capell\FoundationTheme\Testing\AssertsPublicThemeOutputSafety;

uses(AssertsPublicThemeOutputSafety::class);

it('keeps public Blade and translations free of authoring or package metadata, scripts, and database access in legacy sections', function (): void {
    $this->assertLayoutNativeThemeOutputIsSafe(__DIR__ . '/../../resources/views', 'capell-app/theme-liquid-glass', includeTranslations: true);
});

it('keeps the @php block count within the frozen baseline and static calls whitelisted', function (): void {
    // Wave 4c (§D "glassmorphism showcase") added 5 new widget views, raising
    // the count from 12 to 20 (see PhpBlockPolicyTest.php's fleet baseline,
    // updated in the same commit as this one, for the corresponding sum).
    $this->assertPhpBlockPolicy(__DIR__ . '/../../resources/views', 20, ['Frontend']);
});
