<?php

declare(strict_types=1);

use Filament\Forms\Components\DateTimePicker;

require_once __DIR__ . '/../Pest.php';

use Capell\Experiments\Actions\CreateExperimentAction;
use Capell\Experiments\Data\ExperimentData;
use Capell\Experiments\Data\ExperimentVariantData;
use Capell\Experiments\Enums\AllocationStrategy;
use Capell\Experiments\Enums\AudienceOperator;
use Capell\Experiments\Enums\AudienceRuleType;
use Capell\Experiments\Enums\ExperimentGoalType;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Enums\ExperimentSubjectType;
use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\ExperimentAudienceRuleResource;
use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\Pages\ListExperimentAudienceRules;
use Capell\Experiments\Filament\Resources\ExperimentGoals\ExperimentGoalResource;
use Capell\Experiments\Filament\Resources\ExperimentGoals\Pages\ListExperimentGoals;
use Capell\Experiments\Filament\Resources\Experiments\ExperimentResource;
use Capell\Experiments\Filament\Resources\Experiments\Pages\ListExperiments;
use Capell\Experiments\Filament\Resources\ExperimentVariants\ExperimentVariantResource;
use Capell\Experiments\Filament\Resources\ExperimentVariants\Pages\ListExperimentVariants;
use Capell\Experiments\Manifest\ExperimentAudienceRuleResourceContribution;
use Capell\Experiments\Manifest\ExperimentGoalResourceContribution;
use Capell\Experiments\Manifest\ExperimentResourceContribution;
use Capell\Experiments\Manifest\ExperimentsModelsContribution;
use Capell\Experiments\Manifest\ExperimentVariantResourceContribution;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as SchemaFacade;

it('builds experiments resource forms with configured fields and enum option labels', function (): void {
    CreateExperimentAction::run(new ExperimentData(
        name: 'Header experiment',
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
    ));

    expect(experimentsResourceFormComponentClasses(ExperimentResource::form(Schema::make())))->toBe([
        TextInput::class,
        TextInput::class,
        TextInput::class,
        Select::class,
        Select::class,
        TextInput::class,
        TextInput::class,
        Select::class,
        TextInput::class,
        DateTimePicker::class,
        DateTimePicker::class,
        KeyValue::class,
    ])
        ->and(experimentsResourceFormComponentClasses(ExperimentVariantResource::form(Schema::make())))->toBe([
            Select::class,
            TextInput::class,
            TextInput::class,
            TextInput::class,
            TextInput::class,
            Toggle::class,
            Toggle::class,
            KeyValue::class,
        ])
        ->and(experimentsResourceFormComponentClasses(ExperimentGoalResource::form(Schema::make())))->toBe([
            Select::class,
            TextInput::class,
            TextInput::class,
            Select::class,
            TextInput::class,
            TextInput::class,
            Toggle::class,
            Toggle::class,
        ])
        ->and(experimentsResourceFormComponentClasses(ExperimentAudienceRuleResource::form(Schema::make())))->toBe([
            Select::class,
            Select::class,
            TextInput::class,
            Select::class,
            TextInput::class,
            Toggle::class,
            Toggle::class,
            KeyValue::class,
        ])
        ->and(ExperimentStatus::Active->getLabel())->toBe(__('capell-experiments::generic.statuses.active'))
        ->and(AllocationStrategy::StickyWeighted->getLabel())->toBe(__('capell-experiments::generic.allocation_strategies.sticky_weighted'))
        ->and(ExperimentSubjectType::Campaign->getLabel())->toBe(__('capell-experiments::generic.subject_types.campaign'))
        ->and(ExperimentGoalType::Click->getLabel())->toBe(__('capell-experiments::generic.goal_types.click'))
        ->and(AudienceRuleType::Segment->getLabel())->toBe(__('capell-experiments::generic.audience_rule_types.segment'))
        ->and(AudienceOperator::NotIn->getLabel())->toBe(__('capell-experiments::generic.audience_operators.not_in'));
});

it('builds experiments resource forms when experiment storage has not been installed', function (): void {
    SchemaFacade::drop('experiments');

    expect(experimentsResourceFormComponentClasses(ExperimentVariantResource::form(Schema::make())))->toHaveCount(8)
        ->and(experimentsResourceFormComponentClasses(ExperimentGoalResource::form(Schema::make())))->toHaveCount(8)
        ->and(experimentsResourceFormComponentClasses(ExperimentAudienceRuleResource::form(Schema::make())))->toHaveCount(8);
});

it('builds experiments resource tables with expected columns', function (
    string $resourceClass,
    array $expectedColumnNames,
    array $expectedColumnClasses,
): void {
    $table = $resourceClass::table(experimentsResourceTableForCoverage());
    $columns = $table->getColumns();

    expect(array_keys($columns))->toBe($expectedColumnNames)
        ->and(array_map(static fn (object $column): string => $column::class, array_values($columns)))->toBe($expectedColumnClasses);
})->with([
    'experiments' => [
        ExperimentResource::class,
        ['name', 'key', 'status', 'subject_type', 'variants_count', 'goals_count', 'allocations_count', 'starts_at', 'ends_at', 'updated_at'],
        [TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class],
    ],
    'variants' => [
        ExperimentVariantResource::class,
        ['experiment.name', 'name', 'key', 'weight', 'is_control', 'is_active', 'sort_order', 'updated_at'],
        [TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, IconColumn::class, IconColumn::class, TextColumn::class, TextColumn::class],
    ],
    'goals' => [
        ExperimentGoalResource::class,
        ['experiment.name', 'name', 'key', 'type', 'target', 'is_primary', 'is_active', 'updated_at'],
        [TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, IconColumn::class, IconColumn::class, TextColumn::class],
    ],
    'audience rules' => [
        ExperimentAudienceRuleResource::class,
        ['experiment.name', 'type', 'key', 'operator', 'is_required', 'is_active', 'sort_order', 'updated_at'],
        [TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, IconColumn::class, IconColumn::class, TextColumn::class, TextColumn::class],
    ],
]);

it('declares experiments list page create actions and contribution api versions', function (
    object $page,
    string $contributionClass,
): void {
    $actions = experimentsListPageHeaderActions($page);

    expect($actions)->toHaveCount(1)
        ->and($actions[0])->toBeInstanceOf(CreateAction::class)
        ->and($actions[0]->getName())->toBe('create')
        ->and($contributionClass::compatibleCapellApiVersion())->toBe('^4.0');
})->with([
    'experiments' => [new ListExperiments, ExperimentResourceContribution::class],
    'variants' => [new ListExperimentVariants, ExperimentVariantResourceContribution::class],
    'goals' => [new ListExperimentGoals, ExperimentGoalResourceContribution::class],
    'audience rules' => [new ListExperimentAudienceRules, ExperimentAudienceRuleResourceContribution::class],
]);

it('declares the experiments model contribution api version', function (): void {
    expect(ExperimentsModelsContribution::compatibleCapellApiVersion())->toBe('^4.0');
});

/**
 * @return list<class-string|string>
 */
function experimentsResourceFormComponentClasses(Schema $schema): array
{
    $components = $schema->getComponents();

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(Section::class);

    return array_map(
        static fn (object $component): string => $component::class,
        experimentsResourceChildComponents($components[0]),
    );
}

/**
 * @return list<object>
 */
function experimentsResourceChildComponents(object $component): array
{
    if (method_exists($component, 'getDefaultChildComponents')) {
        $components = $component->getDefaultChildComponents();

        return is_array($components) ? array_values($components) : [];
    }

    $reflectionProperty = new ReflectionProperty($component, 'childComponents');
    $childComponents = $reflectionProperty->getValue($component);

    return array_values($childComponents['default'] ?? []);
}

function experimentsResourceTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}

/**
 * @return array<int, CreateAction>
 */
function experimentsListPageHeaderActions(object $page): array
{
    $reflectionMethod = new ReflectionMethod($page, 'getHeaderActions');

    return $reflectionMethod->invoke($page);
}
