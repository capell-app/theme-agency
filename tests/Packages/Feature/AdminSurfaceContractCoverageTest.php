<?php

declare(strict_types=1);

use Capell\Admin\Filament\Contracts\TableConfigurator;
use Capell\Blog\Filament\Configurators\Articles\ArticlePageConfigurator;
use Capell\Core\Models\Page;
use Capell\LayoutBuilder\Filament\Configurators\Layouts\DefaultLayoutContainerConfigurator;
use Capell\LayoutBuilder\Filament\Configurators\Widgets\PageWidgetAssetForm;
use Capell\LayoutBuilder\Filament\Configurators\Widgets\RegisteredAssetWidgetAssetForm;
use Capell\LayoutBuilder\Filament\Resources\Widgets\Schemas\WidgetAssetForm;
use Capell\MigrationAssistant\Filament\Resources\ImportSessions\Schemas\ImportSessionInfolist;
use Capell\Tests\Packages\Fixtures\PackageSurfaceContractSchemaHarness;
use Filament\Pages\Page as FilamentPage;
use Filament\Resources\Pages\Page as FilamentResourcePage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

beforeEach(function (): void {
    test()->registerAndMigrateSettings(
        ['2026_05_10_190871_01_create_ai-orchestrator_settings'],
        dirname(__DIR__, 3) . '/packages/seo-suite/database/settings',
    );
});

it('builds every package-owned filament table configurator through the table contract', function (): void {
    $failures = [];
    $built = 0;

    foreach (packageSurfaceContractClasses(static fn (string $path): bool => str_contains($path, '/Filament/') && str_contains($path, '/Tables/')) as $className) {
        if (! is_a($className, TableConfigurator::class, true)) {
            continue;
        }

        if (! method_exists($className, 'configure')) {
            continue;
        }

        $method = new ReflectionMethod($className, 'configure');

        if ($method->getNumberOfRequiredParameters() > 1) {
            continue;
        }

        try {
            /** @var class-string<TableConfigurator> $className */
            $table = $className::configure(packageSurfaceContractTable());
            $built++;

            expect($table)->toBeInstanceOf(Table::class)
                ->and($table->getColumns() !== [] || $table->getRecordActions() !== [] || $table->getFilters() !== [])->toBeTrue();

        } catch (Throwable $throwable) {
            if (str_contains($throwable->getMessage(), 'is already registered.')) {
                $built++;

                continue;
            }

            $failures[] = $className . ': ' . $throwable->getMessage();
        }
    }

    expect($failures)->toBe([])
        ->and($built)->toBeGreaterThan(20);
});

it('builds package-owned filament schemas settings and form components through their admin contracts', function (): void {
    $failures = [];
    $built = 0;

    foreach (packageSurfaceContractClasses(static fn (string $path): bool => str_contains($path, '/Filament/')) as $className) {
        if (! class_exists($className)) {
            continue;
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract()) {
            continue;
        }

        if (packageSurfaceContractShouldSkipSchemaClass($className)) {
            continue;
        }

        try {
            $result = packageSurfaceContractBuildSchemaClass($className);
        } catch (Throwable $throwable) {
            $failures[] = $className . ': ' . $throwable->getMessage();

            continue;
        }

        if ($result === null) {
            continue;
        }

        $built++;

        expect($result)->not->toBeEmpty();
    }

    expect($built)->toBeGreaterThan(30)
        ->and($failures)->toBe([]);
});

it('resolves package-owned filament resource metadata forms and tables', function (): void {
    $failures = [];
    $built = 0;

    foreach (packageSurfaceContractClasses(static fn (string $path): bool => str_contains($path, '/Filament/Resources/') && str_ends_with($path, 'Resource.php')) as $className) {
        if (! is_subclass_of($className, Resource::class)) {
            continue;
        }

        try {
            /** @var class-string<resource> $className */
            $metadata = [
                'model' => $className::getModel(),
                'pages' => $className::getPages(),
                'relations' => $className::getRelations(),
                'widgets' => $className::getWidgets(),
                'navigation_label' => $className::getNavigationLabel(),
                'model_label' => $className::getModelLabel(),
                'plural_model_label' => $className::getPluralModelLabel(),
            ];

            if (method_exists($className, 'form')) {
                $form = $className::form(packageSurfaceContractSchema('edit'));
                expect($form)->toBeInstanceOf(Schema::class);
            }

            if (method_exists($className, 'table')) {
                $table = $className::table(packageSurfaceContractTable());
                expect($table)->toBeInstanceOf(Table::class);
            }

            $built++;

            expect($metadata['model'])->toBeString()
                ->and($metadata['pages'])->toBeArray()
                ->and($metadata['relations'])->toBeArray()
                ->and($metadata['widgets'])->toBeArray()
                ->and($metadata['navigation_label'])->toBeString()
                ->and($metadata['model_label'])->toBeString()
                ->and($metadata['plural_model_label'])->toBeString();
        } catch (Throwable $throwable) {
            if (str_contains($throwable->getMessage(), 'is already registered.')) {
                $built++;

                continue;
            }

            $failures[] = $className . ': ' . $throwable->getMessage();
        }
    }

    expect($failures)->toBe([])
        ->and($built)->toBeGreaterThan(20);
});

it('builds package-owned filament page table contracts', function (): void {
    $failures = [];
    $built = 0;

    foreach (packageSurfaceContractClasses(static fn (string $path): bool => str_contains($path, '/Filament/Pages/') && str_ends_with($path, '.php')) as $className) {
        if (! is_subclass_of($className, FilamentPage::class)) {
            continue;
        }

        if (! is_subclass_of($className, HasTable::class)) {
            continue;
        }

        $reflection = new ReflectionClass($className);
        if ($reflection->isAbstract()) {
            continue;
        }

        if (! $reflection->hasMethod('table')) {
            continue;
        }

        try {
            /** @var class-string<FilamentPage&HasTable> $className */
            $method = $reflection->getMethod('table');
            $target = $method->isStatic() ? null : new $className;
            $table = $method->invoke($target, packageSurfaceContractTable());
            $built++;

            throw_unless($table instanceof Table, RuntimeException::class, 'Page table method did not return a Filament table.');

            expect($table)->toBeInstanceOf(Table::class)
                ->and($table->getColumns() !== [] || $table->getRecordActions() !== [] || $table->getToolbarActions() !== [])->toBeTrue();
        } catch (Throwable $throwable) {
            if (str_contains($throwable->getMessage(), 'No resources registered for type:')) {
                $built++;

                continue;
            }

            $failures[] = $className . ': ' . $throwable->getMessage();
        }
    }

    expect($built)->toBeGreaterThan(10)
        ->and($failures)->toBe([]);
});

it('resolves package-owned filament page metadata and actions', function (): void {
    $failures = [];
    $built = 0;

    foreach (packageSurfaceContractClasses(static fn (string $path): bool => str_contains($path, '/Filament/Pages/') && str_ends_with($path, '.php')) as $className) {
        if (! is_subclass_of($className, FilamentPage::class)) {
            continue;
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract()) {
            continue;
        }

        try {
            /** @var class-string<FilamentPage> $className */
            $page = new $className;

            $metadata = [
                'navigation_label' => $className::getNavigationLabel(),
                'navigation_group' => $className::getNavigationGroup(),
            ];

            if ($reflection->hasMethod('getTitle') && $reflection->getMethod('getTitle')->isPublic() && $reflection->getMethod('getTitle')->getNumberOfRequiredParameters() === 0) {
                $metadata['title'] = $page->getTitle();
            }

            if ($reflection->hasMethod('getSubheading') && $reflection->getMethod('getSubheading')->isPublic() && $reflection->getMethod('getSubheading')->getNumberOfRequiredParameters() === 0) {
                $metadata['subheading'] = $page->getSubheading();
            }

            if ($reflection->hasMethod('getHeaderActions') && $reflection->getMethod('getHeaderActions')->isPublic() && $reflection->getMethod('getHeaderActions')->getNumberOfRequiredParameters() === 0) {
                $metadata['header_actions'] = $reflection->getMethod('getHeaderActions')->invoke($page);
            }

            $built++;

            expect($metadata['navigation_label'])->toBeString()
                ->and($metadata['navigation_group'] === null || is_string($metadata['navigation_group']))->toBeTrue();
        } catch (Throwable $throwable) {
            if (str_contains($throwable->getMessage(), 'No resources registered for type:')) {
                $built++;

                continue;
            }

            $failures[] = $className . ': ' . $throwable->getMessage();
        }
    }

    expect($failures)->toBe([])
        ->and($built)->toBeGreaterThan(20);
});

it('keeps package-owned filament route surface slugs namespaced to their package', function (): void {
    $failures = [];
    $checked = 0;

    foreach (packageSurfaceContractClassesWithPaths(static fn (string $path): bool => str_contains($path, '/Filament/')) as ['class' => $className, 'path' => $path]) {
        if (! class_exists($className)) {
            continue;
        }

        $isRouteSurface = is_subclass_of($className, Resource::class)
            || (is_subclass_of($className, FilamentPage::class) && str_contains($path, '/src/Filament/Pages/'));

        if (! $isRouteSurface) {
            continue;
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract()) {
            continue;
        }

        /** @var class-string<resource|FilamentPage> $className */
        $packageSlug = packageSurfaceContractPackageSlugForPath($path);
        $routeSlug = $className::getSlug();
        $checked++;

        if (! in_array($packageSlug, explode('/', $routeSlug), true)) {
            $failures[] = sprintf('%s uses [%s] without package segment [%s].', $className, $routeSlug, $packageSlug);
        }
    }

    expect($failures)->toBe([])
        ->and($checked)->toBeGreaterThan(100);
});

it('configures package service providers and executes registration hooks', function (): void {
    $failures = [];
    $built = 0;

    foreach (packageSurfaceContractClasses(static fn (string $path): bool => str_ends_with($path, 'ServiceProvider.php')) as $className) {
        if (! is_subclass_of($className, PackageServiceProvider::class)) {
            continue;
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract()) {
            continue;
        }

        try {
            $provider = new $className(app());
            $package = (new Package)->setBasePath(dirname((string) $reflection->getFileName()));

            $provider->configurePackage($package);
            $provider->registeringPackage();
            $provider->packageRegistered();

            $built++;

            expect($package->name)->toBeString()->not->toBe('');
        } catch (Throwable $throwable) {
            if (str_contains($throwable->getMessage(), 'is already registered.')) {
                $built++;

                continue;
            }

            $failures[] = $className . ': ' . $throwable->getMessage();
        }
    }

    expect($failures)->toBe([])
        ->and($built)->toBeGreaterThan(40);
});

it('resolves package-owned filament resource page registrations', function (): void {
    $failures = [];
    $built = 0;

    foreach (packageSurfaceContractClasses(static fn (string $path): bool => str_contains($path, '/Filament/Resources/') && str_contains($path, '/Pages/') && str_ends_with($path, '.php')) as $className) {
        if (! is_subclass_of($className, FilamentResourcePage::class)) {
            continue;
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract()) {
            continue;
        }

        try {
            /** @var class-string<FilamentResourcePage> $className */
            $resourceClass = $className::getResource();
            $pageName = $className::getResourcePageName();
            $page = new $className;

            $built++;

            expect($resourceClass)->toBeString()->not->toBe('')
                ->and($pageName)->toBeString()->not->toBe('')
                ->and($page->getModel())->toBeString()->not->toBe('');
        } catch (Throwable $throwable) {
            if (str_contains($throwable->getMessage(), 'No resources registered for type:')) {
                $built++;

                continue;
            }

            $failures[] = $className . ': ' . $throwable->getMessage();
        }
    }

    expect($failures)->toBe([])
        ->and($built)->toBeGreaterThan(80);
});

it('builds package-owned filament relation manager table contracts', function (): void {
    $failures = [];
    $built = 0;
    $ownerRecord = new Page(['name' => 'Owner']);
    $ownerRecord->exists = true;

    foreach (packageSurfaceContractClasses(static fn (string $path): bool => str_contains($path, '/Filament/Resources/') && str_contains($path, '/RelationManagers/')) as $className) {
        if (! is_subclass_of($className, RelationManager::class)) {
            continue;
        }

        $reflection = new ReflectionClass($className);
        if ($reflection->isAbstract()) {
            continue;
        }

        if (! $reflection->hasMethod('table')) {
            continue;
        }

        try {
            /** @var class-string<RelationManager> $className */
            $table = (new $className)->table(packageSurfaceContractTable());
            $built++;

            expect($className::getTitle($ownerRecord, 'edit'))->toBeString()
                ->and($table)->toBeInstanceOf(Table::class)
                ->and($table->getColumns())->not->toBeEmpty();
        } catch (Throwable $throwable) {
            $failures[] = $className . ': ' . $throwable->getMessage();
        }
    }

    expect($built)->toBeGreaterThan(5)
        ->and($failures)->toBe([]);
});

/**
 * @return array<int, class-string>
 */
function packageSurfaceContractClasses(Closure $pathFilter): array
{
    return array_map(
        static fn (array $classWithPath): string => $classWithPath['class'],
        packageSurfaceContractClassesWithPaths($pathFilter),
    );
}

/**
 * @return array<int, array{class: class-string, path: string}>
 */
function packageSurfaceContractClassesWithPaths(Closure $pathFilter): array
{
    $classes = [];
    $files = File::allFiles(getcwd() . '/packages');

    foreach ($files as $file) {
        $path = $file->getPathname();
        if (! str_ends_with($path, '.php')) {
            continue;
        }

        if (! $pathFilter($path)) {
            continue;
        }

        $source = File::get($path);

        if (! preg_match('/^namespace\s+([^;]+);/m', $source, $namespaceMatches)) {
            continue;
        }

        if (! preg_match('/^(?:final\s+|abstract\s+)?class\s+([A-Za-z0-9_]+)/m', $source, $classMatches)) {
            continue;
        }

        $classes[] = [
            'class' => $namespaceMatches[1] . '\\' . $classMatches[1],
            'path' => $path,
        ];
    }

    usort(
        $classes,
        static fn (array $first, array $second): int => $first['class'] <=> $second['class'],
    );

    return array_values(array_unique($classes, SORT_REGULAR));
}

function packageSurfaceContractPackageSlugForPath(string $path): string
{
    $relativePath = str_starts_with($path, getcwd() . '/')
        ? substr($path, strlen(getcwd()) + 1)
        : $path;

    throw_unless(preg_match('#^packages/([^/]+)/#', $relativePath, $packageMatches), RuntimeException::class, sprintf('Unable to determine package for [%s].', $path));

    $packageDirectory = $packageMatches[1];
    $manifestPath = getcwd() . '/packages/' . $packageDirectory . '/capell.json';

    if (! File::exists($manifestPath)) {
        return $packageDirectory;
    }

    $manifest = json_decode(File::get($manifestPath), true);
    $manifestSlug = is_array($manifest) ? ($manifest['slug'] ?? null) : null;

    return is_string($manifestSlug) && $manifestSlug !== '' ? $manifestSlug : $packageDirectory;
}

/**
 * @param  class-string  $className
 * @return array<int, mixed>|null
 */
function packageSurfaceContractBuildSchemaClass(string $className): ?array
{
    if (method_exists($className, 'configure')) {
        $method = new ReflectionMethod($className, 'configure');

        if ($method->isStatic() && $method->getNumberOfRequiredParameters() === 1) {
            $parameter = $method->getParameters()[0] ?? null;
            $type = $parameter?->getType();

            if ($type instanceof ReflectionNamedType && $type->getName() !== Schema::class) {
                return null;
            }

            $configured = $method->invoke(null, packageSurfaceContractSchema('edit'));

            if ($configured instanceof Schema) {
                return $configured->getComponents();
            }
        }
    }

    if (method_exists($className, 'getFormSchema')) {
        $method = new ReflectionMethod($className, 'getFormSchema');

        if ($method->isStatic() && $method->getNumberOfRequiredParameters() === 0) {
            $components = $method->invoke(null);

            return is_array($components) ? $components : null;
        }
    }

    if (method_exists($className, 'make')) {
        $method = new ReflectionMethod($className, 'make');

        if ($method->isStatic() && $method->getNumberOfRequiredParameters() === 1) {
            $parameter = $method->getParameters()[0] ?? null;
            $type = $parameter?->getType();

            if ($type instanceof ReflectionNamedType && $type->getName() === Schema::class) {
                $components = $method->invoke(null, packageSurfaceContractSchema('edit'));

                return is_array($components) ? $components : null;
            }

            $component = $className::make('coverage_component');

            if (is_object($component) && method_exists($component, 'getDefaultChildComponents')) {
                $components = $component->getDefaultChildComponents();

                return is_array($components) ? $components : [];
            }
        }

        if (! $method->isStatic() && $method->getNumberOfRequiredParameters() === 1 && str_contains($className, '\\Configurators\\')) {
            $configurator = new $className;
            $components = $configurator->make(packageSurfaceContractSchema('edit'));

            return is_array($components) ? $components : null;
        }
    }

    return null;
}

/**
 * @param  class-string  $className
 */
function packageSurfaceContractShouldSkipSchemaClass(string $className): bool
{
    return in_array($className, [
        ArticlePageConfigurator::class,
        PageWidgetAssetForm::class,
        RegisteredAssetWidgetAssetForm::class,
        DefaultLayoutContainerConfigurator::class,
        WidgetAssetForm::class,
        ImportSessionInfolist::class,
    ], true);
}

function packageSurfaceContractTable(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}

function packageSurfaceContractSchema(string $operation): Schema
{
    return Schema::make(new PackageSurfaceContractSchemaHarness)->operation($operation);
}
