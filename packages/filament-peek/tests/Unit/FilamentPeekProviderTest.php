<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Extenders\AdminPanelExtender;
use Capell\Admin\Contracts\Extenders\ResourceHeaderActionExtender;
use Capell\Admin\Filament\Resources\Pages\Pages\EditPage;
use Capell\Admin\Filament\Resources\Pages\Pages\ListPages;
use Capell\Core\Facades\CapellCore;
use Capell\FilamentPeek\Filament\Actions\PeekPagePreviewAction;
use Capell\FilamentPeek\Filament\Extenders\FilamentPeekPanelExtender;
use Capell\FilamentPeek\Filament\Extenders\PagePeekPreviewHeaderActionExtender;
use Capell\FilamentPeek\Providers\FilamentPeekServiceProvider;

it('registers the panel and page header extenders when installed', function (): void {
    $panelExtenders = collect(app()->tagged(AdminPanelExtender::TAG))
        ->map(fn (object $extender): string => $extender::class);

    $headerExtenders = collect(app()->tagged(ResourceHeaderActionExtender::TAG))
        ->map(fn (object $extender): string => $extender::class);

    expect($panelExtenders)->toContain(FilamentPeekPanelExtender::class)
        ->and($headerExtenders)->toContain(PagePeekPreviewHeaderActionExtender::class);
});

it('does not boot runtime integrations when the package is not installed', function (): void {
    CapellCore::forcePackageInstalled(FilamentPeekServiceProvider::$packageName, false);

    $provider = new FilamentPeekServiceProvider(app());
    $reflection = new ReflectionMethod($provider, 'shouldRegisterRuntime');

    expect($reflection->invoke($provider))->toBeFalse();

    CapellCore::forcePackageInstalled(FilamentPeekServiceProvider::$packageName);
});

it('contributes the peek action only to page edit headers', function (): void {
    $extender = new PagePeekPreviewHeaderActionExtender;

    expect($extender->supports(EditPage::class))->toBeTrue()
        ->and($extender->supports(ListPages::class))->toBeFalse()
        ->and($extender->actions()[0])->toBeInstanceOf(PeekPagePreviewAction::class);
});
