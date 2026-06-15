<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\FrontendAuthoring\Manifest\FrontendAuthoringRoutesContribution;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

describe('frontend authoring capell.json manifest', function (): void {
    $authoringManifest = fn (): array => json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
    );

    $screenshotManifest = fn (): array => json_decode(
        File::get(__DIR__ . '/../../docs/screenshots.json'),
        associative: true,
    );

    it('declares requires using full composer package names', function () use ($authoringManifest): void {
        $manifest = $authoringManifest();

        foreach ($manifest['dependencies']['requires'] ?? [] as $requirement) {
            expect($requirement)->toContain('/');
        }
    });

    it('requires the Capell packages it depends on directly', function () use ($authoringManifest): void {
        $manifest = $authoringManifest();

        expect($manifest['dependencies']['requires'])->toContain('capell-app/core')
            ->and($manifest['dependencies']['requires'])->toContain('capell-app/admin')
            ->and($manifest['dependencies']['requires'])->toContain('capell-app/frontend');
    });

    it('requires the full frontend stack for screenshot browser runs', function () use ($screenshotManifest): void {
        $manifest = $screenshotManifest();

        expect($manifest['composerRequires'])->toContain('capell-app/core')
            ->and($manifest['composerRequires'])->toContain('capell-app/admin')
            ->and($manifest['composerRequires'])->toContain('capell-app/frontend')
            ->and($manifest['composerRequires'])->toContain('capell-app/foundation-theme')
            ->and($manifest['composerRequires'])->toContain('capell-app/frontend-authoring');
    });

    it('does not require Filament Peek for frontend authoring screenshots', function () use ($screenshotManifest): void {
        $manifest = $screenshotManifest();

        expect($manifest['composerRequires'])->not->toContain('capell-app/filament-peek')
            ->and($manifest['composerRequires'])->not->toContain('pboivin/filament-peek');
    });

    it('declares browser tests for public safety and admin editing', function () use ($screenshotManifest): void {
        $manifest = $screenshotManifest();
        $browserTestIds = capell_test_collect($manifest['browserTests'] ?? [])->pluck('id')->all();

        expect($browserTestIds)->toContain('anonymous-users-receive-no-authoring-surface')
            ->and($browserTestIds)->toContain('admin-can-open-single-field-editor');
    });

    it('declares the shipped frontend routes as explicit route contributions', function () use ($authoringManifest): void {
        $manifest = $authoringManifest();

        expect($manifest['contributes'])->toContain([
            'type' => 'route',
            'class' => FrontendAuthoringRoutesContribution::class,
            'routeNames' => [
                'capell-frontend.beacon',
                'capell-frontend.authoring.edit',
            ],
        ])
            ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([])
            ->and(class_implements(FrontendAuthoringRoutesContribution::class))->toContain(RegistersExtensionRoute::class);
    });

    it('keeps route security metadata aligned with package routes', function () use ($authoringManifest): void {
        $manifest = $authoringManifest();
        $publicSurface = data_get($manifest, 'security.publicSurface', []);

        throw_unless(is_array($publicSurface), RuntimeException::class, 'Expected frontend authoring public surface metadata array.');

        expect($publicSurface['routeNames'] ?? null)->toBe([
            'capell-frontend.authoring.edit',
            'capell-frontend.beacon',
        ])
            ->and($publicSurface['auth'] ?? null)->toBe('mixed')
            ->and($publicSurface['csrfExemptRoutes'] ?? null)->toBe(['capell-frontend.beacon'])
            ->and($publicSurface['signedRoutes'] ?? null)->toBe(['capell-frontend.authoring.edit'])
            ->and($publicSurface['throttledRoutes'] ?? null)->toBe(['capell-frontend.beacon']);

        $beaconRoute = Route::getRoutes()->getByName('capell-frontend.beacon');
        $editRoute = Route::getRoutes()->getByName('capell-frontend.authoring.edit');

        expect($beaconRoute)->toBeInstanceOf(LaravelRoute::class)
            ->and($beaconRoute?->methods())->toContain('POST')
            ->and($beaconRoute?->gatherMiddleware())->toContain('frontend.activity')
            ->and($beaconRoute?->gatherMiddleware())->toContain('throttle:60,1')
            ->and($editRoute)->toBeInstanceOf(LaravelRoute::class)
            ->and($editRoute?->methods())->toContain('GET')
            ->and($editRoute?->gatherMiddleware())->toContain('auth')
            ->and($editRoute?->gatherMiddleware())->toContain('signed');
    });
});
