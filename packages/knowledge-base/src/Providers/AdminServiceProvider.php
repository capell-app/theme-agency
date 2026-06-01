<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\CapellAdminManager;
use Capell\Core\Facades\CapellCore;
use Capell\KnowledgeBase\Enums\ResourceEnum;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Policies\KnowledgeBaseArticlePolicy;
use Capell\KnowledgeBase\Policies\KnowledgeBaseCollectionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Override;

final class AdminServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->booted(function (): void {
            if (! CapellCore::isPackageInstalled(KnowledgeBaseServiceProvider::$packageName) || ! $this->app->bound(CapellAdminManager::class)) {
                return;
            }

            $this
                ->registerPolicies()
                ->registerResources();
        });
    }

    private function registerPolicies(): self
    {
        Gate::policy(KnowledgeBaseCollection::class, KnowledgeBaseCollectionPolicy::class);
        Gate::policy(KnowledgeBaseArticle::class, KnowledgeBaseArticlePolicy::class);

        return $this;
    }

    private function registerResources(): self
    {
        foreach (ResourceEnum::cases() as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }

        return $this;
    }
}
