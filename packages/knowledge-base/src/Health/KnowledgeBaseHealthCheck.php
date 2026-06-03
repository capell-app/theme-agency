<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\KnowledgeBase\Actions\BuildAiReadableKnowledgeBaseOutputAction;
use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseArticleDataAction;
use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseNavigationAction;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleFeedback;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Models\KnowledgeBaseRelatedArticle;
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
    private const array PUBLIC_OUTPUT_ACTIONS = [
        BuildPublicKnowledgeBaseNavigationAction::class,
        BuildPublicKnowledgeBaseArticleDataAction::class,
        BuildAiReadableKnowledgeBaseOutputAction::class,
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
            $check->publicOutputActionsDiscoverableCheck(),
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
            label: 'Knowledge Base storage tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'All Knowledge Base collection, article, version, feedback, and related-article tables are present.'
                : 'Missing tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the Knowledge Base storage tables.',
        );
    }

    /**
     * Asserts the Knowledge Base Eloquent models are autoloadable.
     */
    public function modelsDiscoverableCheck(): DoctorCheckResultData
    {
        $missingModels = $this->missingClasses(self::REQUIRED_MODELS);

        return new DoctorCheckResultData(
            label: 'Knowledge Base models',
            passed: $missingModels === [],
            message: $missingModels === []
                ? 'Collection, article, version, feedback, and related-article models are discoverable.'
                : 'Undiscoverable models: ' . implode(', ', $missingModels) . '.',
            remediation: $missingModels === []
                ? null
                : 'Ensure the Knowledge Base package autoloader is registered (composer dump-autoload).',
        );
    }

    /**
     * Asserts the public-facing output Actions that feed anonymous render data
     * are autoloadable.
     */
    public function publicOutputActionsDiscoverableCheck(): DoctorCheckResultData
    {
        $missingActions = $this->missingClasses(self::PUBLIC_OUTPUT_ACTIONS);

        return new DoctorCheckResultData(
            label: 'Knowledge Base public output actions',
            passed: $missingActions === [],
            message: $missingActions === []
                ? 'Public navigation, article, and AI-readable output actions are discoverable.'
                : 'Undiscoverable public output actions: ' . implode(', ', $missingActions) . '.',
            remediation: $missingActions === []
                ? null
                : 'Ensure the Knowledge Base package autoloader is registered (composer dump-autoload).',
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
