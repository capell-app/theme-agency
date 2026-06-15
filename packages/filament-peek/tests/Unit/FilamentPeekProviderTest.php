<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Extenders\AdminPanelExtender;
use Capell\Admin\Contracts\Extenders\PagePreviewActionExtender;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Facades\CapellCore;
use Capell\FilamentPeek\Filament\Actions\PeekPagePreviewAction;
use Capell\FilamentPeek\Filament\Extenders\FilamentPeekPanelExtender;
use Capell\FilamentPeek\Filament\Extenders\PagePeekPreviewActionExtender;
use Capell\FilamentPeek\Manifest\FilamentPeekRoutesContribution;
use Capell\FilamentPeek\Providers\FilamentPeekServiceProvider;
use Filament\Panel;
use Pboivin\FilamentPeek\FilamentPeekPlugin;

it('registers the panel and page preview extenders when installed', function (): void {
    $panelExtenders = collect(app()->tagged(AdminPanelExtender::TAG))
        ->map(fn (object $extender): string => $extender::class);

    $previewExtenders = collect(app()->tagged(PagePreviewActionExtender::TAG))
        ->map(fn (object $extender): string => $extender::class);

    expect($panelExtenders)->toContain(FilamentPeekPanelExtender::class)
        ->and($previewExtenders)->toContain(PagePeekPreviewActionExtender::class);
});

it('registers the peek plugin through the panel extender', function (): void {
    $panel = Panel::make();

    (new FilamentPeekPanelExtender)->extend($panel);

    expect($panel->hasPlugin(FilamentPeekPlugin::make()->getId()))->toBeTrue();
});

it('passes Capell device presets to the upstream preview modal', function (): void {
    expect(config('filament-peek.devicePresets.mobile.width'))->toBe('390px')
        ->and(config('filament-peek.devicePresets.tablet.canRotatePreset'))->toBeTrue()
        ->and(config('filament-peek.initialDevicePreset'))->toBe('fullscreen');
});

it('does not boot runtime integrations when the package is not installed', function (): void {
    CapellCore::forcePackageInstalled(FilamentPeekServiceProvider::$packageName, false);

    $provider = new FilamentPeekServiceProvider(app());
    $reflection = new ReflectionMethod($provider, 'shouldRegisterRuntime');

    expect($reflection->invoke($provider))->toBeFalse();

    CapellCore::forcePackageInstalled(FilamentPeekServiceProvider::$packageName);
});

it('contributes the peek action to the page preview group', function (): void {
    $extender = new PagePeekPreviewActionExtender;

    expect($extender->actions()[0])->toBeInstanceOf(PeekPagePreviewAction::class);
});

it('declares the signed preview route contribution in the manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    $routeContribution = collect($manifest['contributes'] ?? [])
        ->firstWhere('class', FilamentPeekRoutesContribution::class);

    expect($routeContribution)->toBeArray()
        ->and($routeContribution['type'])->toBe('route')
        ->and($routeContribution['routes'])->toBe(['capell-filament-peek.preview'])
        ->and($routeContribution['middleware'])->toBe(['web', 'signed'])
        ->and($routeContribution['tokenized'])->toBeTrue()
        ->and($manifest['security']['publicSurface']['tokenizedRoutes'])->toBe(['capell-filament-peek.preview'])
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([])
        ->and(class_implements(FilamentPeekRoutesContribution::class))->toContain(RegistersExtensionRoute::class);
});
