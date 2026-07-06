<?php

declare(strict_types=1);

use Capell\FoundationTheme\Testing\AssertsPublicThemeOutputSafety;

uses(AssertsPublicThemeOutputSafety::class);

it('keeps public Blade free of authoring, package metadata, and database access', function (): void {
    $this->assertClassicThemeOutputIsSafe(__DIR__ . '/../../resources/views', 'capell-app/theme-ink-press');
});

it('keeps the @php block count within the frozen baseline and static calls whitelisted', function (): void {
    // Baseline raised from 18 to 32 by Wave 4a: six new signature widgets
    // (breaking-news-ribbon, live-event-timeline, reading-progress-with-markers,
    // news-web-topology, author-credibility-inline, opinion-grid-with-bylines)
    // each ship a base view plus a sidecar variant view, all using @php only
    // for defaulting/prep (data_get/collect), never queries or facades — see
    // the static-call whitelist assertion below. May only decrease from here.
    $this->assertPhpBlockPolicy(__DIR__ . '/../../resources/views', 32);
});
