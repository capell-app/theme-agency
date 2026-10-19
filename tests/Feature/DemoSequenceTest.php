<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\FoundationTheme\Actions\InstallFoundationThemeDemoAction;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\ThemeAgency\Actions\InstallAgencyThemeDemoAction;
use Capell\ThemeBusiness\Actions\InstallBusinessThemeDemoAction;
use Capell\UrlManager\Models\RedirectRule;
use Capell\UrlManager\Providers\UrlManagerServiceProvider;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;

it('installs agency after earlier theme demos and safely revisits its paths', function (): void {
    expect(DB::connection()->getDatabaseName())->toBe(':memory:')
        ->and(DB::transactionLevel())->toBe(1);
    // Exercise committed command behaviour so after-commit redirect listeners run.
    DB::rollBack();
    $transactions = new DatabaseTransactionsManager;
    app()->instance('db.transactions', $transactions);
    DB::connection()->setTransactionManager($transactions);
    CapellCore::forcePackageInstalled(UrlManagerServiceProvider::$packageName);
    app()->register(UrlManagerServiceProvider::class);
    resolve(Migrator::class)->run([dirname(__DIR__, 3) . '/url-manager/database/migrations']);

    $data = new ThemeDemoInstallData(['Demo'], ['en'], 'https://demo.test', force: true);

    expect(InstallFoundationThemeDemoAction::run($data))->toBe(0);
    $recovery = Page::query()->where('meta->theme_demo->surface', 'not-found')->sole();
    $canonicalPath = $recovery->pageUrls()->whereNull('type')->sole()->url;
    $redirects = RedirectRule::query()->orderBy('id')->get()->toArray();

    expect(InstallAgencyThemeDemoAction::run($data))->toBe(0);
    expect(InstallBusinessThemeDemoAction::run($data))->toBe(0);
    expect(InstallAgencyThemeDemoAction::run($data))->toBe(0);

    $this->assertSame($canonicalPath, $recovery->pageUrls()->whereNull('type')->sole()->url);
    expect(RedirectRule::query()->orderBy('id')->get()->toArray())->toBe($redirects);

    $site = Site::query()->where('name', 'Demo')->sole();
    expect(Page::query()->where('site_id', $site->id)
        ->where('meta->theme_demo->theme_key', 'agency')
        ->where('meta->theme_demo->surface', 'not-found')->count())->toBe(1);
});
