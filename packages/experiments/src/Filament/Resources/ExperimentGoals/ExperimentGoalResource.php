<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentGoals;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\Experiments\Enums\ExperimentGoalType;
use Capell\Experiments\Filament\Resources\ExperimentGoals\Pages\CreateExperimentGoal;
use Capell\Experiments\Filament\Resources\ExperimentGoals\Pages\EditExperimentGoal;
use Capell\Experiments\Filament\Resources\ExperimentGoals\Pages\ListExperimentGoals;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentGoal;
use Capell\Experiments\Providers\ExperimentsServiceProvider;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class ExperimentGoalResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-experiments::generic.resources.goals'))
                ->schema([
                    Select::make('experiment_id')->label(__('capell-experiments::generic.fields.experiment'))->options(self::experimentOptions())->searchable()->required(),
                    TextInput::make('name')->label(__('capell-experiments::generic.fields.name'))->required()->maxLength(255),
                    TextInput::make('key')->label(__('capell-experiments::generic.fields.key'))->required()->maxLength(255),
                    Select::make('type')->label(__('capell-experiments::generic.fields.type'))->options(self::typeOptions())->required()->default(ExperimentGoalType::CustomEvent->value),
                    TextInput::make('target')->label(__('capell-experiments::generic.fields.target'))->maxLength(255),
                    TextInput::make('value_amount')->label(__('capell-experiments::generic.fields.value_amount'))->numeric()->minValue(0),
                    Toggle::make('is_primary')->label(__('capell-experiments::generic.fields.is_primary'))->default(false),
                    Toggle::make('is_active')->label(__('capell-experiments::generic.fields.is_active'))->default(true),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('experiment.name')->label(__('capell-experiments::generic.fields.experiment'))->searchable()->sortable(),
            TextColumn::make('name')->label(__('capell-experiments::generic.fields.name'))->searchable()->sortable(),
            TextColumn::make('key')->label(__('capell-experiments::generic.fields.key'))->searchable()->toggleable(),
            TextColumn::make('type')->label(__('capell-experiments::generic.fields.type'))->badge()->sortable(),
            TextColumn::make('target')->label(__('capell-experiments::generic.fields.target'))->searchable()->toggleable(),
            IconColumn::make('is_primary')->label(__('capell-experiments::generic.fields.is_primary'))->boolean()->sortable(),
            IconColumn::make('is_active')->label(__('capell-experiments::generic.fields.is_active'))->boolean()->sortable(),
            TextColumn::make('updated_at')->label(__('capell-experiments::generic.fields.updated_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return ExperimentGoal::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-experiments::generic.navigation.group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-experiments::generic.resources.goals');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(ExperimentsServiceProvider::$packageName);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListExperimentGoals::route('/'),
            'create' => CreateExperimentGoal::route('/create'),
            'edit' => EditExperimentGoal::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int|string, string>
     */
    private static function experimentOptions(): array
    {
        if (! resolve(RuntimeSchemaState::class)->hasTable((new Experiment)->getTable())) {
            return [];
        }

        return Experiment::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private static function typeOptions(): array
    {
        return collect(ExperimentGoalType::cases())
            ->mapWithKeys(static fn (ExperimentGoalType $type): array => [$type->value => $type->getLabel()])
            ->all();
    }
}
