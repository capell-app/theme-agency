<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Hero\Health\HeroHealthCheck;
use Illuminate\Support\Facades\View;

it('reports a compatible capell api version', function (): void {
    expect(HeroHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning doctor check results', function (): void {
    $results = HeroHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(2)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the hero widget component and view namespace are registered', function (): void {
    $check = new HeroHealthCheck;

    expect(HeroHealthCheck::passed())->toBeTrue()
        ->and($check->isWidgetComponentRegistered())->toBeTrue()
        ->and($check->widgetComponentCheck()->passed)->toBeTrue()
        ->and($check->viewNamespaceResolves())->toBeTrue()
        ->and($check->viewNamespaceCheck()->passed)->toBeTrue();
});

it('fails the view namespace check when the hero views cannot be resolved', function (): void {
    View::replaceNamespace('capell-hero', sys_get_temp_dir() . '/capell-hero-missing-' . uniqid());

    $check = new HeroHealthCheck;

    expect($check->viewNamespaceResolves())->toBeFalse()
        ->and($check->viewNamespaceCheck()->passed)->toBeFalse()
        ->and($check->viewNamespaceCheck()->remediation)->not->toBeNull()
        ->and(HeroHealthCheck::passed())->toBeFalse();
});

it('declares the admin dependency and surface required by hero schema extenders', function (): void {
    /** @var array{require: array<string, string>} $composer */
    $composer = json_decode((string) file_get_contents(__DIR__ . '/../../composer.json'), true, 512, JSON_THROW_ON_ERROR);

    /** @var array{surfaces: list<string>, dependencies: array{requires: list<string>}} $manifest */
    $manifest = json_decode((string) file_get_contents(__DIR__ . '/../../capell.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['require'])->toHaveKey('capell-app/admin')
        ->and($manifest['dependencies']['requires'])->toContain('capell-app/admin')
        ->and($manifest['surfaces'])->toContain('admin');
});

it('declares the hero feature capabilities exposed by the public renderer', function (): void {
    /** @var array{capabilities: list<string>} $manifest */
    $manifest = json_decode((string) file_get_contents(__DIR__ . '/../../capell.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($manifest['capabilities'])->toContain(
        'hero-widget',
        'hero-video-background',
        'hero-overlay-backgrounds',
        'hero-carousel',
        'hero-theme-inheritance',
    );
});

it('declares the built hero marketplace screenshots', function (): void {
    /** @var array{marketplace: array{screenshots: list<array{path: string}>}} $manifest */
    $manifest = json_decode((string) file_get_contents(__DIR__ . '/../../capell.json'), true, 512, JSON_THROW_ON_ERROR);

    $paths = collect($manifest['marketplace']['screenshots'])
        ->pluck('path')
        ->all();

    expect($paths)->toBe([
        'docs/assets/marketplace/extension-card.jpg',
        'docs/screenshots/hero-home-widget.png',
        'docs/screenshots/hero-slide-variant.png',
    ]);

    foreach ($paths as $path) {
        expect(is_file(__DIR__ . '/../../' . $path))->toBeTrue();
    }
});
