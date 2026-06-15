<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\EmailStudio\Filament\Resources\EmailTemplates\EmailTemplateResource;
use Capell\EmailStudio\Filament\Resources\EmailTemplateThemes\EmailTemplateThemeResource;
use Capell\EmailStudio\Filament\Resources\EmailTemplateVariants\EmailTemplateVariantResource;
use Capell\EmailStudio\Filament\Resources\SentEmails\SentEmailResource;
use Capell\EmailStudio\Settings\EmailStudioSettings;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'capell-email-studio');

        if (! CapellCore::isPackageInstalled(EmailStudioServiceProvider::$packageName)) {
            return;
        }

        foreach ([EmailTemplateResource::class, EmailTemplateVariantResource::class, EmailTemplateThemeResource::class, SentEmailResource::class] as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                $resource,
                group: 'EmailStudio',
            ));
        }

        CapellAdmin::registerExtensionManagementSurface(ExtensionManagementSurfaceData::settings(
            packageName: EmailStudioServiceProvider::$packageName,
            label: 'capell-email-studio::settings.title',
            settingsGroup: EmailStudioSettings::group(),
            icon: 'heroicon-o-envelope',
        ));
    }
}
