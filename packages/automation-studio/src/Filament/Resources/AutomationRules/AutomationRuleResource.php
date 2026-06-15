<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Filament\Resources\AutomationRules;

use BackedEnum;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleConditionOperator;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Filament\Resources\AutomationRules\Pages\CreateAutomationRule;
use Capell\AutomationStudio\Filament\Resources\AutomationRules\Pages\EditAutomationRule;
use Capell\AutomationStudio\Filament\Resources\AutomationRules\Pages\ListAutomationRules;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Providers\AutomationStudioServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
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

final class AutomationRuleResource extends Resource
{
    protected static ?string $slug = 'automation-studio/automation-rules';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-automation-studio::generic.resources.rules'))
                ->schema([
                    TextInput::make('key')->label(__('capell-automation-studio::generic.fields.key'))->required()->maxLength(120),
                    TextInput::make('name')->label(__('capell-automation-studio::generic.fields.name'))->required()->maxLength(255),
                    Select::make('trigger_type')->label(__('capell-automation-studio::generic.fields.trigger'))->options(self::triggerOptions())->required(),
                    Select::make('status')->label(__('capell-automation-studio::generic.fields.status'))->options(self::statusOptions())->required()->default(AutomationRuleStatus::Active->value),
                    Repeater::make('conditions')
                        ->label(__('capell-automation-studio::generic.fields.conditions'))
                        ->schema([
                            TextInput::make('field')
                                ->label(__('capell-automation-studio::generic.fields.condition_field'))
                                ->required()
                                ->maxLength(120),
                            Select::make('operator')
                                ->label(__('capell-automation-studio::generic.fields.condition_operator'))
                                ->options(self::conditionOperatorOptions())
                                ->required()
                                ->default(AutomationRuleConditionOperator::Equals->value),
                            TextInput::make('value')
                                ->label(__('capell-automation-studio::generic.fields.condition_value'))
                                ->maxLength(255),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                    Repeater::make('actions')
                        ->label(__('capell-automation-studio::generic.fields.actions'))
                        ->schema([
                            TextInput::make('key')->label(__('capell-automation-studio::generic.fields.key'))->required()->maxLength(120),
                            Select::make('type')->label(__('capell-automation-studio::generic.fields.action_type'))->options(self::actionOptions())->required(),
                            KeyValue::make('settings')->label(__('capell-automation-studio::generic.fields.settings')),
                        ])
                        ->defaultItems(1)
                        ->columnSpanFull(),
                    KeyValue::make('settings')->label(__('capell-automation-studio::generic.fields.settings'))->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('capell-automation-studio::generic.fields.name'))->searchable()->sortable(),
            TextColumn::make('key')->label(__('capell-automation-studio::generic.fields.key'))->searchable()->toggleable(),
            TextColumn::make('trigger_type')->label(__('capell-automation-studio::generic.fields.trigger'))->badge()->sortable(),
            TextColumn::make('status')->label(__('capell-automation-studio::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('runs_count')->label(__('capell-automation-studio::generic.fields.runs'))->counts('runs')->sortable(),
            TextColumn::make('updated_at')->label(__('capell-automation-studio::generic.fields.updated_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return AutomationRule::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('runs');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-automation-studio::generic.navigation.group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-automation-studio::generic.resources.rules');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(AutomationStudioServiceProvider::$packageName);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListAutomationRules::route('/'),
            'create' => CreateAutomationRule::route('/create'),
            'edit' => EditAutomationRule::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function triggerOptions(): array
    {
        return collect(AutomationTriggerType::cases())
            ->mapWithKeys(static fn (AutomationTriggerType $type): array => [$type->value => $type->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function actionOptions(): array
    {
        return collect(AutomationActionType::cases())
            ->mapWithKeys(static fn (AutomationActionType $type): array => [$type->value => $type->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(AutomationRuleStatus::cases())
            ->mapWithKeys(static fn (AutomationRuleStatus $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function conditionOperatorOptions(): array
    {
        return collect(AutomationRuleConditionOperator::cases())
            ->mapWithKeys(static fn (AutomationRuleConditionOperator $operator): array => [$operator->value => $operator->getLabel()])
            ->all();
    }
}
