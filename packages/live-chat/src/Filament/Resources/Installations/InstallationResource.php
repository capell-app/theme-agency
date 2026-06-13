<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\Installations;

use BackedEnum;
use Capell\LiveChat\Enums\LiveChatSourcePolicy;
use Capell\LiveChat\Filament\Resources\Installations\Pages\CreateInstallation;
use Capell\LiveChat\Filament\Resources\Installations\Pages\EditInstallation;
use Capell\LiveChat\Filament\Resources\Installations\Pages\ListInstallations;
use Capell\LiveChat\Models\LiveChatInstallation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

final class InstallationResource extends Resource
{
    protected static ?string $slug = 'live-chat/installations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-live-chat::generic.resources.installation'))
                ->schema([
                    TextInput::make('site_id')->label(__('capell-live-chat::generic.fields.site_id'))->numeric()->required(),
                    TextInput::make('name')->label(__('capell-live-chat::generic.fields.name'))->required()->maxLength(255),
                    TextInput::make('public_key')->label(__('capell-live-chat::generic.fields.public_key'))->disabled()->dehydrated(false),
                    TagsInput::make('allowed_domains')->label(__('capell-live-chat::generic.fields.allowed_domains'))->placeholder('example.com')->columnSpanFull(),
                    TextInput::make('timezone')->label(__('capell-live-chat::generic.fields.timezone'))->required()->maxLength(255)->default('Europe/London'),
                    Select::make('source_policy')->label(__('capell-live-chat::generic.fields.source_policy'))->options(self::sourcePolicyOptions())->required()->default(LiveChatSourcePolicy::Manual->value),
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
            TextColumn::make('site_id')->label(__('capell-live-chat::generic.fields.site_id'))->sortable(),
            TextColumn::make('public_key')->label(__('capell-live-chat::generic.fields.public_key'))->copyable()->toggleable(),
            TextColumn::make('timezone')->label(__('capell-live-chat::generic.fields.timezone'))->toggleable(),
            TextColumn::make('source_policy')->label(__('capell-live-chat::generic.fields.source_policy'))->badge()->sortable(),
            IconColumn::make('is_active')->label(__('capell-live-chat::generic.fields.is_active'))->boolean()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return LiveChatInstallation::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-live-chat::generic.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-live-chat::generic.resources.installations');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListInstallations::route('/'),
            'create' => CreateInstallation::route('/create'),
            'edit' => EditInstallation::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function sourcePolicyOptions(): array
    {
        return collect(LiveChatSourcePolicy::cases())
            ->mapWithKeys(static fn (LiveChatSourcePolicy $policy): array => [$policy->value => $policy->getLabel()])
            ->all();
    }
}
