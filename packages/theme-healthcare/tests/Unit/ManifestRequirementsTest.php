<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('theme healthcare capell.json manifest', function (): void {
    it('declares its demo command for package demo installs', function (): void {
        $manifest = healthcareThemeManifest();
        $commands = $manifest['commands'] ?? null;

        throw_unless(is_array($commands), RuntimeException::class, 'Theme Healthcare manifest commands must be an array.');

        expect($commands['demo'])->toBe('capell:theme-healthcare-demo')
            ->and($commands['demoParams'])->toBe(['url', 'languages', 'sites']);
    });

    it('uses buyer-facing marketplace copy and committed real preview assets', function (): void {
        $manifest = healthcareThemeManifest();
        $marketplace = $manifest['marketplace'] ?? null;

        throw_unless(is_array($marketplace), RuntimeException::class, 'Theme Healthcare marketplace manifest data must be an array.');

        $screenshots = $marketplace['screenshots'] ?? null;

        throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Healthcare marketplace screenshots must be an array.');

        $screenshotPaths = [];

        foreach ($screenshots as $screenshot) {
            throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Healthcare marketplace screenshots must define string paths.');

            $screenshotPaths[] = $screenshot['path'];
        }

        expect($marketplace['summary'])->toBe('A premium, appointment-led theme for private clinics and healthcare groups — service discovery, clinician profiles, care pathways, locations, and a booking-ready enquiry panel, all WCAG-minded and brand-tunable in Theme Studio.')
            ->and($marketplace['description'])->toBe('Theme Healthcare turns Capell into a conversion-focused clinical website. It ships nineteen care-oriented sections — an appointment hero, service finder, clinician carousel, care-pathway guidance, insurance/trust signals, locations, events, and a booking panel — that route patients toward the right enquiry with calm, clinical styling. The booking, events, and resource sections light up automatically when Capell Bookings/Form Builder, Events, and Blog are installed, with no theme reconfiguration. Built on Foundation Theme with accessible focus states and a skip link, it activates from the Themes screen and seeds a full demo via `capell:theme-healthcare-demo`.')
            ->and($screenshotPaths)->toBe([
                'docs/assets/marketplace/extension-card.jpg',
                'docs/screenshots/healthcare-homepage-desktop.png',
                'docs/screenshots/healthcare-homepage-mobile.png',
                'docs/screenshots/healthcare-services-listing.png',
                'docs/screenshots/healthcare-clinician-detail.png',
                'docs/screenshots/healthcare-contact-page.png',
            ]);

        foreach ($screenshotPaths as $screenshotPath) {
            expect(File::exists(__DIR__ . '/../../' . $screenshotPath))->toBeTrue();
        }
    });
});

/**
 * @return array<string, mixed>
 */
function healthcareThemeManifest(): array
{
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme Healthcare manifest must decode to an array.');

    return $manifest;
}
