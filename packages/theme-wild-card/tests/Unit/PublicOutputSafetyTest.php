<?php

declare(strict_types=1);

use Capell\FoundationTheme\Testing\AssertsPublicThemeOutputSafety;

uses(AssertsPublicThemeOutputSafety::class);

it('keeps public Blade free of authoring, package metadata, and database access', function (): void {
    $this->assertClassicThemeOutputIsSafe(__DIR__ . '/../../resources/views', 'capell-app/theme-wild-card');
});

it('keeps the @php block count within the frozen baseline and static calls whitelisted', function (): void {
    // Baseline raised 31 -> 35 for the time-capsule-browser signature widget
    // (base + --cabinet variant), each mirroring the sibling archive themes'
    // two-@php-block view shape (header defaulting + per-cycle foreach prep).
    $this->assertPhpBlockPolicy(__DIR__ . '/../../resources/views', 35);
});
