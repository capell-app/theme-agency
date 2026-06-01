<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\ExperimentVariants;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Experiments\Filament\Resources\ExperimentVariants\Pages\CreateExperimentVariant;
use Capell\Experiments\Filament\Resources\ExperimentVariants\Pages\EditExperimentVariant;
use Capell\Experiments\Filament\Resources\ExperimentVariants\Pages\ListExperimentVariants;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentVariant;
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

final class ExperimentVariantResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-experiments::generic.resources.variants'))
                ->schema([
                    Select::make('experiment_id')->label(__('capell-experiments::generic.fields.experiment'))->options(self::experimentOptions())->searchable()->required(),
                    TextInput::make('name')->label(__('capell-experiments::generic.fields.name'))->required()->maxLength(255),
                    TextInput::make('key')->label(__('capell-experiments::generic.fields.key'))->required()->maxLength(255),
                    TextInput::make('weight')->label(__('capell-experiments::generic.fields.weight'))->numeric()->minValue(0)->default(100)->required(),
                    TextInput::make('sort_order')->label(__('capell-experiments::generic.fields.sort_order'))->numeric()->minValue(0)->default(0)->required(),
                    Toggle::make('is_control')->label(__('capell-experiments::generic.fields.is_control'))->default(false),
                    Toggle::make('is_active')->label(__('capell-experiments::generic.fields.is_active'))->default(true),
                    KeyValue::make('payload')->label(__('capell-experiments::generic.fields.payload'))->columnSpanFull(),
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
            TextColumn::make('weight')->label(__('capell-experiments::generic.fields.weight'))->numeric()->sortable(),
            IconColumn::make('is_control')->label(__('capell-experiments::generic.fields.is_control'))->boolean()->sortable(),
            IconColumn::make('is_active')->label(__('capell-experiments::generic.fields.is_active'))->boolean()->sortable(),
            TextColumn::make('sort_order')->label(__('capell-experiments::generic.fields.sort_order'))->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')->label(__('capell-experiments::generic.fields.updated_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return ExperimentVariant::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-experiments::generic.navigation.group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-experiments::generic.resources.variants');
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
            'index' => ListExperimentVariants::route('/'),
            'create' => CreateExperimentVariant::route('/create'),
            'edit' => EditExperimentVariant::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int|string, string>
     */
    private static function experimentOptions(): array
    {
        return Experiment::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
