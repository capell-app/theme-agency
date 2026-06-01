<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Support\PortalSelfServiceItemRegistry;
use Capell\DocumentLifecycle\Actions\PublishDocumentFromPublishingRevisionAction;
use Capell\DocumentLifecycle\Enums\ResourceEnum;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Capell\DocumentLifecycle\Policies\DocumentPolicy;
use Capell\DocumentLifecycle\Support\CustomerPortal\DocumentLifecyclePortalSelfServiceItemProvider;
use Capell\PublishingStudio\Models\PublishingRevision;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Override;
use Spatie\LaravelPackageTools\Package;

class DocumentLifecycleServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-document-lifecycle';

    public static string $packageName = 'capell-app/document-lifecycle';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasTranslations()
            ->hasMigrations([
                '2026_05_10_190868_01_create_document_lifecycle_documents_table',
                '2026_05_10_190868_02_create_document_lifecycle_publications_table',
                '2026_05_10_190868_03_extend_legal_acceptances_for_document_lifecycle',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->booting(function (): void {
            if ($this->isPackageInstalled()) {
                $this->registerAdminResources();
            }
        });

        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerPolicies()
                ->registerModels()
                ->registerMorphMap()
                ->registerProtectedTables()
                ->registerCustomerPortalIntegrations()
                ->registerPublishingRevisionListener();
        });
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(static::$packageName);
    }

    private function registerPolicies(): self
    {
        Gate::policy(Document::class, DocumentPolicy::class);

        return $this;
    }

    private function registerAdminResources(): self
    {
        if (! class_exists(CapellAdmin::class) || ! class_exists(AdminSurfaceContributionData::class)) {
            return $this;
        }

        foreach (ResourceEnum::cases() as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }

        return $this;
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            Document::class,
            DocumentAcceptance::class,
            DocumentPublication::class,
        ]);

        return $this;
    }

    private function registerMorphMap(): self
    {
        Relation::morphMap([
            'document_lifecycle_document' => Document::class,
            'document_lifecycle_publication' => DocumentPublication::class,
            'document_acceptance' => DocumentAcceptance::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable('document_lifecycle_documents');
        CapellCore::registerProtectedTable('document_lifecycle_publications');
        CapellCore::registerProtectedTable('legal_acceptances');

        return $this;
    }

    private function registerCustomerPortalIntegrations(): self
    {
        if (! class_exists(PortalSelfServiceItemRegistry::class)
            || ! interface_exists(PortalSelfServiceItemProvider::class)) {
            return $this;
        }

        /** @var object $registry */
        $registry = $this->app->make(PortalSelfServiceItemRegistry::class);

        if (! method_exists($registry, 'register')) {
            return $this;
        }

        $registry->register('document-lifecycle.acceptances', DocumentLifecyclePortalSelfServiceItemProvider::class);

        return $this;
    }

    private function registerPublishingRevisionListener(): self
    {
        PublishingRevision::created(static function (PublishingRevision $revision): void {
            PublishDocumentFromPublishingRevisionAction::run($revision);
        });

        return $this;
    }
}
