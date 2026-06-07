<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\Experiments;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Experiments\Enums\AllocationStrategy;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Enums\ExperimentSubjectType;
use Capell\Experiments\Filament\Resources\Experiments\Pages\CreateExperiment;
use Capell\Experiments\Filament\Resources\Experiments\Pages\EditExperiment;
use Capell\Experiments\Filament\Resources\Experiments\Pages\ExperimentResultsPage;
use Capell\Experiments\Filament\Resources\Experiments\Pages\ListExperiments;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Providers\ExperimentsServiceProvider;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ExperimentResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-experiments::generic.resources.experiments'))
                ->schema([
                    TextInput::make('name')->label(__('capell-experiments::generic.fields.name'))->required()->maxLength(255),
                    TextInput::make('key')->label(__('capell-experiments::generic.fields.key'))->required()->maxLength(255),
                    TextInput::make('site_id')->label(__('capell-experiments::generic.fields.site_id'))->numeric(),
                    Select::make('status')->label(__('capell-experiments::generic.fields.status'))->options(self::statusOptions())->required()->default(ExperimentStatus::Draft->value),
                    Select::make('subject_type')->label(__('capell-experiments::generic.fields.subject_type'))->options(self::subjectTypeOptions())->required()->default(ExperimentSubjectType::Generic->value),
                    TextInput::make('subject_class')->label(__('capell-experiments::generic.fields.subject_class'))->maxLength(255),
                    TextInput::make('subject_id')->label(__('capell-experiments::generic.fields.subject_id'))->numeric(),
                    Select::make('allocation_strategy')->label(__('capell-experiments::generic.fields.allocation_strategy'))->options(self::allocationStrategyOptions())->required()->default(AllocationStrategy::StickyWeighted->value),
                    TextInput::make('traffic_percentage')->label(__('capell-experiments::generic.fields.traffic_percentage'))->numeric()->minValue(0)->maxValue(100)->default(100)->required(),
                    DateTimePicker::make('starts_at')->label(__('capell-experiments::generic.fields.starts_at')),
                    DateTimePicker::make('ends_at')->label(__('capell-experiments::generic.fields.ends_at')),
                    KeyValue::make('metadata')->label(__('capell-experiments::generic.fields.metadata'))->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('capell-experiments::generic.fields.name'))->searchable()->sortable(),
            TextColumn::make('key')->label(__('capell-experiments::generic.fields.key'))->searchable()->toggleable(),
            TextColumn::make('status')->label(__('capell-experiments::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('subject_type')->label(__('capell-experiments::generic.fields.subject_type'))->badge()->sortable(),
            TextColumn::make('variants_count')->label(__('capell-experiments::generic.fields.variants'))->counts('variants')->sortable(),
            TextColumn::make('goals_count')->label(__('capell-experiments::generic.fields.goals'))->counts('goals')->sortable(),
            TextColumn::make('allocations_count')->label(__('capell-experiments::generic.fields.allocations'))->counts('allocations')->sortable(),
            TextColumn::make('starts_at')->label(__('capell-experiments::generic.fields.starts_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('ends_at')->label(__('capell-experiments::generic.fields.ends_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')->label(__('capell-experiments::generic.fields.updated_at'))->dateTime()->sortable(),
        ])->recordActions([
            self::resultsAction(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return Experiment::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['variants', 'goals', 'allocations']);
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-experiments::generic.navigation.group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-experiments::generic.resources.experiments');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(ExperimentsServiceProvider::$packageName);
    }

    public static function resultsAction(): Action
    {
        return Action::make('results')
            ->label(__('capell-experiments::generic.actions.results'))
            ->icon('heroicon-o-chart-bar')
            ->url(fn (Experiment $record): string => static::getUrl('results', ['record' => $record]));
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListExperiments::route('/'),
            'create' => CreateExperiment::route('/create'),
            'edit' => EditExperiment::route('/{record}/edit'),
            'results' => ExperimentResultsPage::route('/{record}/results'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(ExperimentStatus::cases())
            ->mapWithKeys(static fn (ExperimentStatus $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function subjectTypeOptions(): array
    {
        return collect(ExperimentSubjectType::cases())
            ->mapWithKeys(static fn (ExperimentSubjectType $subjectType): array => [$subjectType->value => $subjectType->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function allocationStrategyOptions(): array
    {
        return collect(AllocationStrategy::cases())
            ->mapWithKeys(static fn (AllocationStrategy $strategy): array => [$strategy->value => $strategy->getLabel()])
            ->all();
    }
}
