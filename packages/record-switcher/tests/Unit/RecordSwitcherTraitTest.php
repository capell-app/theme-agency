<?php

declare(strict_types=1);

use Capell\RecordSwitcher\Tests\Fixtures\RecordSwitcherSearchFixture;
use Capell\RecordSwitcher\Tests\Fixtures\RecordSwitcherSearchPage;
use Filament\Support\Facades\FilamentAsset;

it('loads the record switcher view and admin assets', function (): void {
    $viewName = implode('', ['capell-record-switcher::components.', 'record-switcher']);

    expect(view()->exists($viewName))->toBeTrue()
        ->and(FilamentAsset::getStyleHref('record-switcher', package: 'capell-record-switcher'))->not->toBeEmpty()
        ->and(FilamentAsset::getAlpineComponentSrc('record-switcher', 'capell-record-switcher'))->not->toBeEmpty();
});

it('builds case insensitive search constraints for configured columns', function (): void {
    $query = RecordSwitcherSearchFixture::query();

    RecordSwitcherSearchPage::applySearch($query, 'HeRo');

    expect($query->toSql())
        ->toContain('lower(')
        ->toContain('like ?')
        ->not->toContain('like binary')
        ->and($query->getBindings())->toContain('%hero%');
});
