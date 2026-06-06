<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Frontend\Support\Cache\CacheInvalidationRegistry;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleFeedback;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Models\KnowledgeBaseRelatedArticle;
use Illuminate\Database\Eloquent\Relations\Relation;
use Override;
use Spatie\LaravelPackageTools\Package;

final class KnowledgeBaseServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-knowledge-base';

    public static string $packageName = 'capell-app/knowledge-base';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-knowledge-base')
            ->hasTranslations()
            ->hasViews()
            ->hasRoute('web')
            ->hasMigrations([
                '2026_05_31_000001_create_knowledge_base_tables',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->register(AdminServiceProvider::class);

        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerModels()
                ->registerMorphMap()
                ->registerCacheInvalidationDependencies()
                ->registerProtectedTables();
        });
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            KnowledgeBaseCollection::class,
            KnowledgeBaseArticle::class,
            KnowledgeBaseArticleVersion::class,
            KnowledgeBaseArticleFeedback::class,
            KnowledgeBaseRelatedArticle::class,
        ]);

        return $this;
    }

    private function registerMorphMap(): self
    {
        Relation::morphMap([
            'knowledge_base_collection' => KnowledgeBaseCollection::class,
            'knowledge_base_article' => KnowledgeBaseArticle::class,
            'knowledge_base_article_version' => KnowledgeBaseArticleVersion::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable('knowledge_base_collections');
        CapellCore::registerProtectedTable('knowledge_base_articles');
        CapellCore::registerProtectedTable('knowledge_base_article_versions');
        CapellCore::registerProtectedTable('knowledge_base_article_feedback');
        CapellCore::registerProtectedTable('knowledge_base_related_articles');

        return $this;
    }

    private function registerCacheInvalidationDependencies(): self
    {
        $cacheInvalidationRegistryClass = CacheInvalidationRegistry::class;

        if (! class_exists($cacheInvalidationRegistryClass) || ! $this->app->bound($cacheInvalidationRegistryClass)) {
            return $this;
        }

        $registry = resolve($cacheInvalidationRegistryClass);

        if (! is_object($registry) || ! method_exists($registry, 'registerDependency')) {
            return $this;
        }

        $registry->registerDependency(KnowledgeBaseArticle::class, 'knowledge-base-*');
        $registry->registerDependency(KnowledgeBaseArticleVersion::class, 'knowledge-base-*');
        $registry->registerDependency(KnowledgeBaseCollection::class, 'knowledge-base-*');

        return $this;
    }
}
