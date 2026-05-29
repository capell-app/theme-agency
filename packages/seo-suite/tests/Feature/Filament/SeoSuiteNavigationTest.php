<?php

declare(strict_types=1);

use Capell\SeoSuite\Filament\Pages\AiDiscoveryPage;
use Capell\SeoSuite\Filament\Pages\BrokenLinksPage;
use Capell\SeoSuite\Filament\Pages\NotFoundUrlsPage;
use Capell\SeoSuite\Filament\Pages\SearchRankingsPage;
use Capell\SeoSuite\Filament\Pages\SeoAuditPage;
use Capell\SeoSuite\Filament\Pages\TranslationCoveragePage;

it('keeps seo monitoring links contained under seo audit', function (): void {
    $parentItem = (string) __('capell-seo-suite::generic.seo_audit');

    expect(SeoAuditPage::getNavigationSort())->toBe(10)
        ->and(SeoAuditPage::getNavigationParentItem())->toBeNull()
        ->and(BrokenLinksPage::getNavigationParentItem())->toBe($parentItem)
        ->and(BrokenLinksPage::getNavigationSort())->toBe(11)
        ->and(NotFoundUrlsPage::getNavigationParentItem())->toBe($parentItem)
        ->and(NotFoundUrlsPage::getNavigationSort())->toBe(12)
        ->and(AiDiscoveryPage::getNavigationParentItem())->toBe($parentItem)
        ->and(AiDiscoveryPage::getNavigationSort())->toBe(13)
        ->and(TranslationCoveragePage::getNavigationParentItem())->toBe($parentItem)
        ->and(TranslationCoveragePage::getNavigationSort())->toBe(14)
        ->and(SearchRankingsPage::getNavigationParentItem())->toBe($parentItem)
        ->and(SearchRankingsPage::getNavigationSort())->toBe(15);
});
