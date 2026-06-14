<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\EscalationRules;

use BackedEnum;
use Capell\LiveChat\Enums\EscalationTriggerType;
use Capell\LiveChat\Enums\LiveChatPriority;
use Capell\LiveChat\Filament\Resources\Concerns\ScopesLiveChatResourcesToSites;
use Capell\LiveChat\Filament\Resources\EscalationRules\Pages\CreateEscalationRule;
use Capell\LiveChat\Filament\Resources\EscalationRules\Pages\EditEscalationRule;
use Capell\LiveChat\Filament\Resources\EscalationRules\Pages\ListEscalationRules;
use Capell\LiveChat\Models\LiveChatEscalationRule;
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
use Illuminate\Database\Eloquent\Builder;
use Override;

final class EscalationRuleResource extends Resource
{
    use ScopesLiveChatResourcesToSites;

    protected static ?string $slug = 'live-chat/escalation-rules';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-live-chat::generic.resources.escalation_rule'))
                ->schema([
                    Select::make('site_id')->label(__('capell-live-chat::generic.fields.site_id'))->options(fn (): array => self::liveChatSiteOptions())->searchable()->preload(),
                    TextInput::make('name')->label(__('capell-live-chat::generic.fields.name'))->required()->maxLength(255),
                    Select::make('trigger_type')->label(__('capell-live-chat::generic.fields.trigger_type'))->options(self::triggerOptions())->required(),
                    TextInput::make('trigger_value')->label(__('capell-live-chat::generic.fields.trigger_value'))->maxLength(255),
                    Select::make('priority')->label(__('capell-live-chat::generic.fields.priority'))->options(self::priorityOptions())->required()->default(LiveChatPriority::High->value),
                    TextInput::make('route_to')->label(__('capell-live-chat::generic.fields.route_to'))->maxLength(255),
                    Toggle::make('is_active')->label(__('capell-live-chat::generic.fields.is_active'))->default(true),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('capell-live-chat::generic.fields.name'))->searchable()->sortable(),
            TextColumn::make('trigger_type')->label(__('capell-live-chat::generic.fields.trigger_type'))->badge()->sortable(),
            TextColumn::make('trigger_value')->label(__('capell-live-chat::generic.fields.trigger_value'))->limit(32),
            TextColumn::make('priority')->label(__('capell-live-chat::generic.fields.priority'))->badge()->sortable(),
            TextColumn::make('route_to')->label(__('capell-live-chat::generic.fields.route_to'))->toggleable(),
            IconColumn::make('is_active')->label(__('capell-live-chat::generic.fields.is_active'))->boolean()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return LiveChatEscalationRule::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return self::scopeLiveChatQueryToActorSites(parent::getEloquentQuery());
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-live-chat::generic.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-live-chat::generic.resources.escalation_rules');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListEscalationRules::route('/'),
            'create' => CreateEscalationRule::route('/create'),
            'edit' => EditEscalationRule::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function triggerOptions(): array
    {
        return collect(EscalationTriggerType::cases())
            ->mapWithKeys(static fn (EscalationTriggerType $type): array => [$type->value => $type->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function priorityOptions(): array
    {
        return collect(LiveChatPriority::cases())
            ->mapWithKeys(static fn (LiveChatPriority $priority): array => [$priority->value => $priority->getLabel()])
            ->all();
    }
}
