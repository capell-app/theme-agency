<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\Core\Models\Theme;
use Capell\Core\Models\Translation;
use Capell\Core\Support\Creator\LayoutCreator;
use Capell\Hero\Actions\InstallHeroLayoutDefaultsAction;
use Capell\Hero\Health\HeroHealthCheck;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Illuminate\Support\Facades\View;

it('reports a compatible capell api version', function (): void {
    expect(HeroHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning doctor check results', function (): void {
    $results = HeroHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(3)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the hero widget component and view namespace are registered', function (): void {
    InstallHeroLayoutDefaultsAction::run(force: true);

    $check = new HeroHealthCheck;

    expect(HeroHealthCheck::passed())->toBeTrue()
        ->and($check->isWidgetComponentRegistered())->toBeTrue()
        ->and($check->widgetComponentCheck()->passed)->toBeTrue()
        ->and($check->viewNamespaceResolves())->toBeTrue()
        ->and($check->viewNamespaceCheck()->passed)->toBeTrue()
        ->and($check->hasSeededHomeLayoutState())->toBeTrue()
        ->and($check->homeLayoutDefaultsCheck()->passed)->toBeTrue();
});

it('fails the home layout defaults check when the hero container is missing', function (): void {
    resolve(LayoutCreator::class)->setup();

    Layout::query()
        ->where('key', LayoutEnum::Home->value)
        ->firstOrFail()
        ->update(['containers' => ['main' => ['widgets' => [['widget_key' => 'page-content']]]]]);

    $check = new HeroHealthCheck;

    expect($check->hasSeededHomeLayoutState())->toBeFalse()
        ->and($check->homeLayoutDefaultsCheck()->passed)->toBeFalse()
        ->and($check->homeLayoutDefaultsCheck()->remediation)->toBe('Run capell:hero-setup --force to install the Hero-managed home layout defaults.');
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

    expect($manifest['capabilities'])->toBe([]);
});

it('declares cacheable public output with concrete invalidation sources', function (): void {
    /** @var array{performance: array{cacheSafety: array{cacheable: bool, variesBy: list<string>, sensitiveOutput: bool, invalidationSources: list<array{model: string}>}}} $manifest */
    $manifest = json_decode((string) file_get_contents(__DIR__ . '/../../capell.json'), true, 512, JSON_THROW_ON_ERROR);
    $cacheSafety = $manifest['performance']['cacheSafety'];

    expect($cacheSafety['cacheable'])->toBeTrue()
        ->and($cacheSafety['variesBy'])->toBe(['site', 'locale'])
        ->and($cacheSafety['sensitiveOutput'])->toBeFalse()
        ->and(collect($cacheSafety['invalidationSources'])->pluck('model')->all())->toContain(
            Media::class,
            Page::class,
            Theme::class,
            Translation::class,
            Widget::class,
            WidgetAsset::class,
        );
});

it('does not promote mock hero captures as marketplace screenshots', function (): void {
    /** @var array{marketplace: array{screenshots: list<array{path: string}>}} $manifest */
    $manifest = json_decode((string) file_get_contents(__DIR__ . '/../../capell.json'), true, 512, JSON_THROW_ON_ERROR);

    $paths = collect($manifest['marketplace']['screenshots'])
        ->pluck('path')
        ->all();

    expect($paths)->toBe([
        'docs/assets/marketplace/extension-card.jpg',
        'docs/screenshots/hero-home-widget.png',
    ]);

    foreach ($paths as $path) {
        throw_unless(is_string($path), RuntimeException::class, 'Expected hero screenshot path to be a string.');

        expect(is_file(__DIR__ . '/../../' . $path))->toBeTrue();
    }
});
