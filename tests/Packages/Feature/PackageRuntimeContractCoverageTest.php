<?php

declare(strict_types=1);

use Capell\Admin\Filament\Contracts\TableConfigurator;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Filament\Pages\Page as FilamentPage;
use Filament\Resources\Resource;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;

it('verifies package extension health and manifest contribution contracts expose compatible api versions', function (): void {
    $checked = 0;

    foreach (packageRuntimeContractClasses(static fn (string $path): bool => str_contains($path, '/src/Health/') || str_contains($path, '/src/Manifest/')) as $className) {
        if (! class_exists($className)) {
            continue;
        }

        if (! is_a($className, ChecksExtensionHealth::class, true) && ! is_a($className, ExtensionContribution::class, true)) {
            continue;
        }

        $checked++;

        expect($className::compatibleCapellApiVersion())->toBe('^4.0');
    }

    expect($checked)->toBeGreaterThan(40);
});

it('resolves package enum labels colors and icons used by admin choices', function (): void {
    $checkedCases = 0;

    foreach (packageRuntimeContractEnums() as $enumClass) {
        $reflection = new ReflectionEnum($enumClass);

        if ($reflection->isAbstract()) {
            continue;
        }

        foreach ($enumClass::cases() as $case) {
            $checkedCases++;

            if (method_exists($case, 'getLabel')) {
                expect($case->getLabel())->toBeString();
            }

            if (method_exists($case, 'getColor')) {
                expect($case->getColor())->not->toBeArray();
            }

            if (method_exists($case, 'getIcon')) {
                expect($case->getIcon())->not->toBeArray();
            }
        }
    }

    expect($checkedCases)->toBeGreaterThan(200);
});

it('resolves package Filament resource metadata used by admin navigation and discovery', function (): void {
    $checked = 0;

    foreach (packageRuntimeContractClasses(static fn (string $path): bool => str_contains($path, '/src/Filament/Resources/') && str_ends_with($path, 'Resource.php')) as $className) {
        if (! is_subclass_of($className, Resource::class)) {
            continue;
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract()) {
            continue;
        }

        $checked++;

        expect($className::getModel())->toBeString()
            ->and($className::getNavigationLabel())->toBeString()
            ->and($className::getModelLabel())->toBeString()
            ->and($className::getPluralModelLabel())->toBeString()
            ->and($className::getPages())->toBeArray()
            ->and($className::getRelations())->toBeArray()
            ->and($className::getWidgets())->toBeArray()
            ->and($className::getGloballySearchableAttributes())->toBeArray();
    }

    expect($checked)->toBeGreaterThan(40);
});

it('builds package table configurators used by Filament list and relation manager surfaces', function (): void {
    $checked = 0;

    foreach (packageRuntimeContractClasses(static fn (string $path): bool => str_contains($path, '/src/Filament/') && str_contains($path, '/Tables/')) as $className) {
        if (! is_subclass_of($className, TableConfigurator::class)) {
            continue;
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract()) {
            continue;
        }

        $checked++;

        $table = $className::configure(packageRuntimeContractTable());

        expect($table->getColumns())->toBeArray()
            ->and($table->getFilters())->toBeArray()
            ->and($table->getRecordActions())->toBeArray()
            ->and($table->getToolbarActions())->toBeArray();
    }

    expect($checked)->toBeGreaterThan(20);
});

it('resolves package Filament page metadata used by admin navigation', function (): void {
    $checked = 0;

    foreach (packageRuntimeContractClasses(static fn (string $path): bool => str_contains($path, '/src/Filament/Pages/')) as $className) {
        if (! is_subclass_of($className, FilamentPage::class)) {
            continue;
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract()) {
            continue;
        }

        $checked++;

        expect($className::getNavigationLabel())->toBeString()
            ->and($className::getNavigationGroup())->not->toBeArray()
            ->and($className::getNavigationIcon())->not->toBeArray()
            ->and($className::getActiveNavigationIcon())->not->toBeArray()
            ->and($className::getNavigationBadge())->not->toBeArray()
            ->and($className::getNavigationBadgeTooltip())->not->toBeArray()
            ->and($className::getNavigationBadgeColor())->not->toBeObject()
            ->and($className::getNavigationSort())->not->toBeString()
            ->and($className::shouldRegisterNavigation())->toBeBool()
            ->and($className::isDiscovered())->toBeBool();
    }

    expect($checked)->toBeGreaterThan(15);
});

/**
 * @return list<class-string>
 */
function packageRuntimeContractClasses(Closure $pathFilter): array
{
    $classes = [];

    foreach (File::allFiles(getcwd() . '/packages') as $file) {
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

        $classes[] = $namespaceMatches[1] . '\\' . $classMatches[1];
    }

    sort($classes);

    return array_values(array_unique($classes));
}

/**
 * @return list<class-string<UnitEnum>>
 */
function packageRuntimeContractEnums(): array
{
    $classes = [];

    foreach (File::allFiles(getcwd() . '/packages') as $file) {
        $path = $file->getPathname();
        if (! str_ends_with($path, '.php')) {
            continue;
        }

        if (! str_contains($path, '/src/Enums/')) {
            continue;
        }

        $source = File::get($path);

        if (! preg_match('/^namespace\s+([^;]+);/m', $source, $namespaceMatches)) {
            continue;
        }

        if (! preg_match('/^enum\s+([A-Za-z0-9_]+)/m', $source, $enumMatches)) {
            continue;
        }

        $className = $namespaceMatches[1] . '\\' . $enumMatches[1];

        if (enum_exists($className)) {
            $classes[] = $className;
        }
    }

    sort($classes);

    return array_values(array_unique($classes));
}

function packageRuntimeContractTable(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}
