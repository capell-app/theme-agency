<?php

declare(strict_types=1);

use Capell\Blog\Models\Article;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Translation;
use Capell\Diagnostics\Data\Dashboard\ConfigDriftEntryData;
use Capell\Diagnostics\Data\Dashboard\ContentHealthIssueData;
use Capell\Diagnostics\Data\Dashboard\PackageInfoData;
use Capell\Diagnostics\Data\Dashboard\TailwindSiteStatusData;
use Capell\MigrationAssistant\Actions\InstallMigrationAssistantPermissionsAction;
use Capell\MigrationAssistant\Services\Import\Resolvers\MatchResolution;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Pest\Expectation;
use Spatie\Permission\Models\Permission;

if (! function_exists('capell_test_collect')) {
    /**
     * @return Collection<array-key, mixed>
     */
    function capell_test_collect(mixed $items = []): Collection
    {
        if ($items instanceof Collection) {
            return $items;
        }

        if (is_iterable($items)) {
            return new Collection($items);
        }

        return new Collection;
    }
}

if (! function_exists('capell_expect')) {
    /** @return Expectation<mixed> */
    function capell_expect(mixed $value): Expectation
    {
        return expect($value);
    }
}

if (! function_exists('capell_test_instance')) {
    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T
     */
    function capell_test_instance(mixed $value, string $class, ?string $message = null): object
    {
        throw_unless($value instanceof $class, RuntimeException::class, $message ?? sprintf('Expected instance of %s.', $class));

        return $value;
    }
}

if (! function_exists('capell_test_array')) {
    /**
     * @return array<array-key, mixed>
     */
    function capell_test_array(mixed $value, ?string $message = null): array
    {
        throw_unless(is_array($value), RuntimeException::class, $message ?? 'Expected array.');

        return $value;
    }
}

if (! function_exists('blogTestArticle')) {
    function blogTestArticle(mixed $article): Article
    {
        return capell_test_instance($article, Article::class, 'Expected blog article.');
    }
}

if (! function_exists('blogTestBlueprint')) {
    function blogTestBlueprint(mixed $blueprint): Blueprint
    {
        return capell_test_instance($blueprint, Blueprint::class, 'Expected blueprint.');
    }
}

if (! function_exists('blogTestLanguage')) {
    function blogTestLanguage(mixed $language): Language
    {
        return capell_test_instance($language, Language::class, 'Expected language.');
    }
}

if (! function_exists('blogTestLayout')) {
    function blogTestLayout(mixed $layout): Layout
    {
        return capell_test_instance($layout, Layout::class, 'Expected layout.');
    }
}

if (! function_exists('blogTestPage')) {
    function blogTestPage(mixed $page): Page
    {
        return capell_test_instance($page, Page::class, 'Expected page.');
    }
}

if (! function_exists('blogTestPageUrl')) {
    function blogTestPageUrl(mixed $pageUrl): PageUrl
    {
        return capell_test_instance($pageUrl, PageUrl::class, 'Expected page URL.');
    }
}

if (! function_exists('blogTestTranslation')) {
    function blogTestTranslation(mixed $translation): Translation
    {
        return capell_test_instance($translation, Translation::class, 'Expected translation.');
    }
}

if (! function_exists('blogTestArray')) {
    /**
     * @return array<string, mixed>
     */
    function blogTestArray(mixed $value): array
    {
        /** @var array<string, mixed> $array */
        $array = capell_test_array($value, 'Expected array.');

        return $array;
    }
}

if (! function_exists('blogTestContainerWidgets')) {
    /**
     * @param  array<string, mixed>  $containers
     * @return array<array-key, mixed>
     */
    function blogTestContainerWidgets(array $containers, string $container): array
    {
        $containerData = blogTestArray($containers[$container] ?? null);
        $widgets = $containerData['widgets'] ?? [];

        throw_unless(is_array($widgets), RuntimeException::class, 'Expected container widgets.');

        return $widgets;
    }
}

if (! function_exists('diagnosticsConfigDriftEntry')) {
    function diagnosticsConfigDriftEntry(?ConfigDriftEntryData $entry): ConfigDriftEntryData
    {
        throw_unless($entry instanceof ConfigDriftEntryData, RuntimeException::class, 'Expected config drift entry.');

        return $entry;
    }
}

if (! function_exists('diagnosticsPackageInfo')) {
    function diagnosticsPackageInfo(?PackageInfoData $package): PackageInfoData
    {
        throw_unless($package instanceof PackageInfoData, RuntimeException::class, 'Expected package info row.');

        return $package;
    }
}

if (! function_exists('diagnosticsTailwindSiteStatus')) {
    function diagnosticsTailwindSiteStatus(?TailwindSiteStatusData $status): TailwindSiteStatusData
    {
        throw_unless($status instanceof TailwindSiteStatusData, RuntimeException::class, 'Expected Tailwind site status row.');

        return $status;
    }
}

if (! function_exists('diagnosticsContentHealthIssue')) {
    function diagnosticsContentHealthIssue(?ContentHealthIssueData $issue): ContentHealthIssueData
    {
        throw_unless($issue instanceof ContentHealthIssueData, RuntimeException::class, 'Expected content health issue.');

        return $issue;
    }
}

if (! function_exists('layoutBuilderTestInstance')) {
    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T
     */
    function layoutBuilderTestInstance(mixed $value, string $class, ?string $message = null): object
    {
        return capell_test_instance($value, $class, $message);
    }
}

if (! function_exists('layoutBuilderTestArray')) {
    /**
     * @return array<string, mixed>
     */
    function layoutBuilderTestArray(mixed $value): array
    {
        /** @var array<string, mixed> $array */
        $array = capell_test_array($value, 'Expected array.');

        return $array;
    }
}

if (! function_exists('migrationAssistantActingAsImportPagesUser')) {
    function migrationAssistantActingAsImportPagesUser(): void
    {
        Permission::findOrCreate('View:ImportPagesPage', 'web');
        InstallMigrationAssistantPermissionsAction::run();

        test()->actingAsAdmin();
        migrationAssistantAdminUser()->givePermissionTo('View:ImportPagesPage');
    }
}

if (! function_exists('migrationAssistantAdminUser')) {
    function migrationAssistantAdminUser(): User
    {
        $user = auth()->user();

        throw_unless($user instanceof User, RuntimeException::class, 'Expected migration assistant admin user to be authenticated.');

        return $user;
    }
}

if (! function_exists('migrationAssistantMatchResolution')) {
    function migrationAssistantMatchResolution(?MatchResolution $resolution): MatchResolution
    {
        throw_unless($resolution instanceof MatchResolution, RuntimeException::class, 'Expected migration assistant match resolution.');

        return $resolution;
    }
}

if (! function_exists('migrationAssistantSummary')) {
    /**
     * @param  array<mixed>|null  $summary
     * @return array<mixed>
     */
    function migrationAssistantSummary(?array $summary): array
    {
        throw_unless(is_array($summary), RuntimeException::class, 'Expected migration assistant summary.');

        return $summary;
    }
}

if (! function_exists('migrationAssistantImportedPage')) {
    function migrationAssistantImportedPage(?Page $page): Page
    {
        throw_unless($page instanceof Page, RuntimeException::class, 'Expected imported page to exist.');

        return $page;
    }
}

if (! function_exists('publishingStudioTestInstance')) {
    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T
     */
    function publishingStudioTestInstance(mixed $value, string $class, ?string $message = null): object
    {
        return capell_test_instance($value, $class, $message);
    }
}

if (! function_exists('publishingStudioTestArray')) {
    /**
     * @return array<string, mixed>
     */
    function publishingStudioTestArray(mixed $value): array
    {
        /** @var array<string, mixed> $array */
        $array = capell_test_array($value, 'Expected array.');

        return $array;
    }
}
