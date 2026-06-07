<?php

declare(strict_types=1);

namespace Capell\Newsletter\Tests;

use Capell\Admin\Providers\AdminServiceProvider as CapellAdminServiceProvider;
use Capell\Admin\Providers\Filament\AdminPanelProvider;
use Capell\Contacts\Providers\ContactsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\CustomerPortal\Providers\CustomerPortalServiceProvider;
use Capell\FormBuilder\Providers\FormBuilderServiceProvider;
use Capell\Frontend\Providers\FrontendServiceProvider;
use Capell\Newsletter\Providers\AdminServiceProvider as NewsletterAdminServiceProvider;
use Capell\Newsletter\Providers\NewsletterServiceProvider;
use Capell\Tags\Providers\TagsServiceProvider;
use Capell\Tests\AbstractTestCase;
use Illuminate\Support\Facades\Config;
use Livewire\LivewireServiceProvider;
use Override;

class NewsletterTestCase extends AbstractTestCase
{
    protected function getPackageServiceName(): string
    {
        return 'capell-newsletter';
    }

    protected function createNewsletterSite(string $name = 'Newsletter Site'): Site
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

    /**
     * @return class-string[]
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ...parent::getPackageProviders($app),
            CapellAdminServiceProvider::class,
            AdminPanelProvider::class,
            ContactsServiceProvider::class,
            CustomerPortalServiceProvider::class,
            FrontendServiceProvider::class,
            TagsServiceProvider::class,
            FormBuilderServiceProvider::class,
            NewsletterServiceProvider::class,
            NewsletterAdminServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        Config::set('app.key', 'base64:' . base64_encode(str_repeat('n', 32)));

        CapellCore::forcePackageInstalled(CapellAdminServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(ContactsServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(CustomerPortalServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(FrontendServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(TagsServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(FormBuilderServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(NewsletterServiceProvider::$packageName);
    }
}
