<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Filament\Widgets\EditPageAuditTabsWidget;
use Capell\SeoSuite\Filament\Widgets\EditPagePageSpeedAuditBadge;
use Capell\SeoSuite\Filament\Widgets\EditPageSeoAuditBadge;
use Capell\SeoSuite\Filament\Widgets\EditPageSeoAuditWidget;
use Capell\SeoSuite\Support\Admin\PageSeoAuditPageEditExtender;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Support\Collection as SupportCollection;
use Livewire\Livewire;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    test()->actingAsAdmin();
});

it('does not contribute edit page audit widgets above the form', function (): void {
    $extender = resolve(PageSeoAuditPageEditExtender::class);

    expect($extender->getHeaderWidgets())->toBe([])
        ->and($extender->getFormActions())->toBe([]);
});

it('registers stable aliases for lazy edit page audit components', function (): void {
    expect(Livewire::isDiscoverable('capell-seo-suite.edit-page-audit-tabs'))->toBeTrue()
        ->and(Livewire::isDiscoverable('capell-seo-suite.edit-page-seo-audit'))->toBeTrue()
        ->and(Livewire::isDiscoverable('capell-seo-suite.edit-page-pagespeed-audit'))->toBeTrue()
        ->and(Livewire::isDiscoverable('capell-seo-suite.edit-page-seo-audit-badge'))->toBeTrue()
        ->and(Livewire::isDiscoverable('capell-seo-suite.edit-page-pagespeed-audit-badge'))->toBeTrue();
});

it('switches edit page audit tabs without loading both tab bodies up front', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, siteDomainData: ['scheme' => 'https', 'domain' => 'example.com', 'path' => null])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, slug: 'home')
        ->create();

    Livewire::test(EditPageAuditTabsWidget::class, ['record' => $page])
        ->assertSet('activeTab', 'seo')
        ->assertSeeText(__('capell-seo-suite::generic.seo_audit'))
        ->assertSeeText(__('capell-seo-suite::generic.pagespeed_audit'))
        ->call('selectTab', 'pagespeed')
        ->assertSet('activeTab', 'pagespeed');
});

it('renders no checks when report context is unavailable', function (): void {
    Livewire::test(EditPageSeoAuditWidget::class)
        ->assertSeeText(__('capell-seo-suite::generic.no_checks'));
});

it('passes the meta description check when description is present', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, siteDomainData: ['scheme' => 'https', 'domain' => 'example.com', 'path' => null])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, ['description' => 'A useful description for this page that gives search engines enough context.'], slug: 'home')
        ->create();

    Livewire::test(EditPageSeoAuditWidget::class, ['record' => $page])
        ->assertSet('checks', fn (SupportCollection $checks): bool => $checks['meta_description']->pass === true);
});

it('lazy audit badges expose issue counts for the edit page tabs', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, siteDomainData: ['scheme' => 'https', 'domain' => 'example.com', 'path' => null])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, slug: 'home')
        ->create();

    Livewire::test(EditPageSeoAuditBadge::class, ['record' => $page])
        ->assertSet('issueCount', 8)
        ->assertSeeText('8');

    Livewire::test(EditPagePageSpeedAuditBadge::class, ['record' => $page])
        ->assertSet('issueCount', 2)
        ->assertSeeText('2');
});

it('renders lazy audit badges with a root element when context is unavailable', function (): void {
    Livewire::test(EditPageSeoAuditBadge::class)
        ->assertSet('issueCount', null)
        ->assertOk();

    Livewire::test(EditPagePageSpeedAuditBadge::class)
        ->assertSet('issueCount', null)
        ->assertOk();
});
