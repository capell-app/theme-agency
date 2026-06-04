<?php

declare(strict_types=1);
use Illuminate\Support\Facades\File;

describe('theme healthcare capell.json manifest', function (): void {
    it('declares its demo command for package demo installs', function (): void {
        $manifest = json_decode(
            File::get(__DIR__ . '/../../capell.json'),
            associative: true,
        );

        expect($manifest['commands']['demo'])->toBe('capell:theme-healthcare-demo')
            ->and($manifest['commands']['demoParams'])->toBe(['url', 'languages', 'sites']);
    });

    it('uses buyer-facing marketplace copy and committed real preview assets', function (): void {
        $manifest = json_decode(
            File::get(__DIR__ . '/../../capell.json'),
            associative: true,
        );

        expect($manifest['marketplace']['summary'])->toBe('A premium, appointment-led theme for private clinics and healthcare groups — service discovery, clinician profiles, care pathways, locations, and a booking-ready enquiry panel, all WCAG-minded and brand-tunable in Theme Studio.')
            ->and($manifest['marketplace']['description'])->toBe('Theme Healthcare turns Capell into a conversion-focused clinical website. It ships nineteen care-oriented sections — an appointment hero, service finder, clinician carousel, care-pathway guidance, insurance/trust signals, locations, events, and a booking panel — that route patients toward the right enquiry with calm, clinical styling. The booking, events, and resource sections light up automatically when Capell Bookings/Form Builder, Events, and Blog are installed, with no theme reconfiguration. Built on Foundation Theme with accessible focus states and a skip link, it activates from the Themes screen and seeds a full demo via `capell:theme-healthcare-demo`.')
            ->and(array_column($manifest['marketplace']['screenshots'], 'path'))->toBe([
                'docs/assets/marketplace/extension-card.jpg',
                'docs/assets/marketplace/hero-desktop.jpg',
                'docs/assets/marketplace/hero-mobile.jpg',
            ]);

        collect($manifest['marketplace']['screenshots'])
            ->each(fn (array $screenshot): mixed => expect(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue());
    });
});
