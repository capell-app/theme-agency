<?php

declare(strict_types=1);

use Capell\FoundationTheme\Testing\AssertsPublicThemeOutputSafety;

uses(AssertsPublicThemeOutputSafety::class);

it('keeps public Blade free of authoring, package metadata, and database access', function (): void {
    $this->assertClassicThemeOutputIsSafe(__DIR__ . '/../../resources/views', 'capell-app/theme-far-field');
});

it('keeps the @php block count within the frozen baseline and static calls whitelisted', function (): void {
    // Wave 4a raised the baseline from 19 to 34: five new signature-widget
    // views (radio-audio's rewrite, city-guides' rewrite, the new
    // photo-essay and cultural-dispatch-timeline sections, and columnists'
    // rewrite) each added one or two @php blocks, all pure data_get()/
    // collect() defaulting/prep per the §1.2 policy -- no queries, facades,
    // or side-effectful conditionals. The ratchet still holds: this count
    // may only decrease from here.
    $this->assertPhpBlockPolicy(__DIR__ . '/../../resources/views', 34);
});
