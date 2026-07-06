<?php

declare(strict_types=1);

use Capell\FoundationTheme\Testing\AssertsPublicThemeOutputSafety;

uses(AssertsPublicThemeOutputSafety::class);

it('keeps public Blade free of authoring, package metadata, and database access', function (): void {
    $this->assertClassicThemeOutputIsSafe(__DIR__ . '/../../resources/views', 'capell-app/theme-quiet-type');
});

it('keeps the @php block count within the frozen baseline and static calls whitelisted', function (): void {
    // Baseline raised from 15 to 37 in Wave 4a: seven new signature sections
    // (essay-index--marginalia, author-profiles--bibliography,
    // serialized-chapters + its compact variant, issue-contents + its
    // numbered variant, quote-context + its medallion-left variant,
    // contextual-glossary + its compact variant, scroll-position-menu + its
    // left-rail variant) each carry the same defaulting/prep @php blocks as
    // every existing quiet-type section.
    $this->assertPhpBlockPolicy(__DIR__ . '/../../resources/views', 37);
});
