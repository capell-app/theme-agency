<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\KnowledgeBase\Actions\BuildAiReadableKnowledgeBaseOutputAction;
use Capell\KnowledgeBase\Actions\BuildKnowledgeBaseSearchDocumentsAction;
use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseArticleDataAction;
use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseNavigationAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseArticleAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseArticleVersionAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseCollectionAction;
use Capell\KnowledgeBase\Actions\PublishKnowledgeBaseArticleVersionAction;
use Capell\KnowledgeBase\Actions\RecordKnowledgeBaseArticleFeedbackAction;
use Capell\KnowledgeBase\Actions\RelateKnowledgeBaseArticlesAction;
use Capell\KnowledgeBase\Actions\SanitizeKnowledgeBaseArticleHtmlAction;
use Capell\KnowledgeBase\Filament\Resources\Articles\KnowledgeBaseArticleResource;
use Capell\KnowledgeBase\Filament\Resources\Collections\KnowledgeBaseCollectionResource;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleFeedback;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Models\KnowledgeBaseRelatedArticle;
use Capell\KnowledgeBase\Providers\AdminServiceProvider;
use Capell\KnowledgeBase\Providers\KnowledgeBaseServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class KnowledgeBaseHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_TABLES = [
        'knowledge_base_collections',
        'knowledge_base_articles',
        'knowledge_base_article_versions',
        'knowledge_base_article_feedback',
        'knowledge_base_related_articles',
    ];

    /**
     * @var list<class-string>
     */
    private const array REQUIRED_MODELS = [
        KnowledgeBaseCollection::class,
        KnowledgeBaseArticle::class,
        KnowledgeBaseArticleVersion::class,
        KnowledgeBaseArticleFeedback::class,
        KnowledgeBaseRelatedArticle::class,
    ];

    /**
     * @var list<class-string>
     */
    private const array REQUIRED_ACTIONS = [
        BuildPublicKnowledgeBaseNavigationAction::class,
        BuildPublicKnowledgeBaseArticleDataAction::class,
        BuildAiReadableKnowledgeBaseOutputAction::class,
        BuildKnowledgeBaseSearchDocumentsAction::class,
        CreateKnowledgeBaseCollectionAction::class,
        CreateKnowledgeBaseArticleAction::class,
        CreateKnowledgeBaseArticleVersionAction::class,
        PublishKnowledgeBaseArticleVersionAction::class,
        RecordKnowledgeBaseArticleFeedbackAction::class,
        RelateKnowledgeBaseArticlesAction::class,
        SanitizeKnowledgeBaseArticleHtmlAction::class,
    ];

    /**
     * @var list<class-string>
     */
    private const array REQUIRED_ADMIN_RESOURCES = [
        KnowledgeBaseCollectionResource::class,
        KnowledgeBaseArticleResource::class,
    ];

    /**
     * @var list<class-string>
     */
    private const array REQUIRED_PROVIDERS = [
        KnowledgeBaseServiceProvider::class,
        AdminServiceProvider::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->storageTablesCheck(),
            $check->modelsDiscoverableCheck(),
            $check->actionsDiscoverableCheck(),
            $check->adminResourcesDiscoverableCheck(),
            $check->providersDiscoverableCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts every Knowledge Base storage table has been migrated.
     */
    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: (string) __('capell-knowledge-base::generic.health.storage_tables.label'),
            passed: $missingTables === [],
            message: $missingTables === []
                ? (string) __('capell-knowledge-base::generic.health.storage_tables.passed')
                : (string) __('capell-knowledge-base::generic.health.storage_tables.failed', ['tables' => implode(', ', $missingTables)]),
            remediation: $missingTables === []
                ? null
                : (string) __('capell-knowledge-base::generic.health.storage_tables.remediation'),
        );
    }

    /**
     * Asserts the Knowledge Base Eloquent models are autoloadable.
     */
    public function modelsDiscoverableCheck(): DoctorCheckResultData
    {
        $missingModels = $this->missingClasses(self::REQUIRED_MODELS);

        return new DoctorCheckResultData(
            label: (string) __('capell-knowledge-base::generic.health.models.label'),
            passed: $missingModels === [],
            message: $missingModels === []
                ? (string) __('capell-knowledge-base::generic.health.models.passed')
                : (string) __('capell-knowledge-base::generic.health.models.failed', ['classes' => implode(', ', $missingModels)]),
            remediation: $missingModels === []
                ? null
                : (string) __('capell-knowledge-base::generic.health.autoload_remediation'),
        );
    }

    /**
     * Asserts the package Actions declared by the Knowledge Base feature surface
     * are autoloadable.
     */
    public function actionsDiscoverableCheck(): DoctorCheckResultData
    {
        $missingActions = $this->missingClasses(self::REQUIRED_ACTIONS);

        return new DoctorCheckResultData(
            label: (string) __('capell-knowledge-base::generic.health.actions.label'),
            passed: $missingActions === [],
            message: $missingActions === []
                ? (string) __('capell-knowledge-base::generic.health.actions.passed')
                : (string) __('capell-knowledge-base::generic.health.actions.failed', ['classes' => implode(', ', $missingActions)]),
            remediation: $missingActions === []
                ? null
                : (string) __('capell-knowledge-base::generic.health.autoload_remediation'),
        );
    }

    /**
     * Asserts the contributed Filament resources are autoloadable.
     */
    public function adminResourcesDiscoverableCheck(): DoctorCheckResultData
    {
        $missingResources = $this->missingClasses(self::REQUIRED_ADMIN_RESOURCES);

        return new DoctorCheckResultData(
            label: (string) __('capell-knowledge-base::generic.health.admin_resources.label'),
            passed: $missingResources === [],
            message: $missingResources === []
                ? (string) __('capell-knowledge-base::generic.health.admin_resources.passed')
                : (string) __('capell-knowledge-base::generic.health.admin_resources.failed', ['classes' => implode(', ', $missingResources)]),
            remediation: $missingResources === []
                ? null
                : (string) __('capell-knowledge-base::generic.health.autoload_remediation'),
        );
    }

    /**
     * Asserts the runtime and admin service providers named in the manifest are
     * autoloadable.
     */
    public function providersDiscoverableCheck(): DoctorCheckResultData
    {
        $missingProviders = $this->missingClasses(self::REQUIRED_PROVIDERS);

        return new DoctorCheckResultData(
            label: (string) __('capell-knowledge-base::generic.health.providers.label'),
            passed: $missingProviders === [],
            message: $missingProviders === []
                ? (string) __('capell-knowledge-base::generic.health.providers.passed')
                : (string) __('capell-knowledge-base::generic.health.providers.failed', ['classes' => implode(', ', $missingProviders)]),
            remediation: $missingProviders === []
                ? null
                : (string) __('capell-knowledge-base::generic.health.autoload_remediation'),
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect(self::REQUIRED_TABLES)
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all());
    }

    /**
     * @param  list<class-string>  $classes
     * @return list<string>
     */
    private function missingClasses(array $classes): array
    {
        return array_values(collect($classes)
            ->reject(static fn (string $className): bool => class_exists($className))
            ->values()
            ->all());
    }
}
