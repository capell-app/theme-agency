<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentAudienceRules;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Experiments\Enums\AudienceOperator;
use Capell\Experiments\Enums\AudienceRuleType;
use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\Pages\CreateExperimentAudienceRule;
use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\Pages\EditExperimentAudienceRule;
use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\Pages\ListExperimentAudienceRules;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentAudienceRule;
use Capell\Experiments\Providers\ExperimentsServiceProvider;
use Filament\Forms\Components\KeyValue;
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

final class ExperimentAudienceRuleResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $recordTitleAttribute = 'key';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-experiments::generic.resources.audience_rules'))
                ->schema([
                    Select::make('experiment_id')->label(__('capell-experiments::generic.fields.experiment'))->options(self::experimentOptions())->searchable()->required(),
                    Select::make('type')->label(__('capell-experiments::generic.fields.type'))->options(self::typeOptions())->required()->default(AudienceRuleType::Path->value),
                    TextInput::make('key')->label(__('capell-experiments::generic.fields.key'))->required()->maxLength(255),
                    Select::make('operator')->label(__('capell-experiments::generic.fields.operator'))->options(self::operatorOptions())->required()->default(AudienceOperator::Equals->value),
                    TextInput::make('sort_order')->label(__('capell-experiments::generic.fields.sort_order'))->numeric()->minValue(0)->default(0)->required(),
                    Toggle::make('is_required')->label(__('capell-experiments::generic.fields.is_required'))->default(true),
                    Toggle::make('is_active')->label(__('capell-experiments::generic.fields.is_active'))->default(true),
                    KeyValue::make('value')->label(__('capell-experiments::generic.fields.value'))->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('experiment.name')->label(__('capell-experiments::generic.fields.experiment'))->searchable()->sortable(),
            TextColumn::make('type')->label(__('capell-experiments::generic.fields.type'))->badge()->sortable(),
            TextColumn::make('key')->label(__('capell-experiments::generic.fields.key'))->searchable()->sortable(),
            TextColumn::make('operator')->label(__('capell-experiments::generic.fields.operator'))->badge()->sortable(),
            IconColumn::make('is_required')->label(__('capell-experiments::generic.fields.is_required'))->boolean()->sortable(),
            IconColumn::make('is_active')->label(__('capell-experiments::generic.fields.is_active'))->boolean()->sortable(),
            TextColumn::make('sort_order')->label(__('capell-experiments::generic.fields.sort_order'))->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')->label(__('capell-experiments::generic.fields.updated_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return ExperimentAudienceRule::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-experiments::generic.navigation.group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-experiments::generic.resources.audience_rules');
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
            'index' => ListExperimentAudienceRules::route('/'),
            'create' => CreateExperimentAudienceRule::route('/create'),
            'edit' => EditExperimentAudienceRule::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int|string, string>
     */
    private static function experimentOptions(): array
    {
        return Experiment::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private static function typeOptions(): array
    {
        return collect(AudienceRuleType::cases())
            ->mapWithKeys(static fn (AudienceRuleType $type): array => [$type->value => $type->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function operatorOptions(): array
    {
        return collect(AudienceOperator::cases())
            ->mapWithKeys(static fn (AudienceOperator $operator): array => [$operator->value => $operator->getLabel()])
            ->all();
    }
}
