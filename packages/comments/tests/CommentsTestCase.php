<?php

declare(strict_types=1);

namespace Capell\Comments\Tests;

use Capell\Admin\Providers\AdminServiceProvider as CapellAdminServiceProvider;
use Capell\Admin\Providers\Filament\AdminPanelProvider;
use Capell\Comments\Providers\CommentsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Frontend\Providers\FrontendServiceProvider;
use Capell\Tests\AbstractTestCase;
use Illuminate\Support\Facades\Config;
use Livewire\LivewireServiceProvider;
use Override;

class CommentsTestCase extends AbstractTestCase
{
    protected function getPackageServiceName(): string
    {
        return 'capell-comments';
    }

    protected function createCommentsSite(string $name = 'Comments Site'): Site
    {
        $siteType = Blueprint::factory()->site()->create();
        $themeType = Blueprint::factory()->theme()->create();
        $theme = Theme::factory()->create(['blueprint_id' => $themeType->getKey()]);
        $language = Language::factory()->english()->create();

        return Site::query()->create([
            'name' => $name,
            'blueprint_id' => $siteType->getKey(),
            'theme_id' => $theme->getKey(),
            'language_id' => $language->getKey(),
            'default' => true,
            'status' => true,
        ]);
    }

    protected function createCommentsPage(?Site $site = null): Page
    {
        $site ??= $this->createCommentsSite();

        return Page::factory()
            ->site($site)
            ->create(['name' => 'Comments page']);
    }

    /**
     * @return class-string[]
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ...parent::getPackageProviders($app),
            CapellAdminServiceProvider::class,
            FrontendServiceProvider::class,
            CommentsServiceProvider::class,
            LivewireServiceProvider::class,
            AdminPanelProvider::class,
        ];
    }

    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        Config::set('app.key', 'base64:' . base64_encode(str_repeat('c', 32)));
        Config::set('capell-comments.enabled', true);

        CapellCore::forcePackageInstalled(CapellAdminServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(FrontendServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(CommentsServiceProvider::$packageName);
    }
}
