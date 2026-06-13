<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\AvailabilityWindows;

use BackedEnum;
use Capell\LiveChat\Filament\Resources\AvailabilityWindows\Pages\CreateAvailabilityWindow;
use Capell\LiveChat\Filament\Resources\AvailabilityWindows\Pages\EditAvailabilityWindow;
use Capell\LiveChat\Filament\Resources\AvailabilityWindows\Pages\ListAvailabilityWindows;
use Capell\LiveChat\Models\LiveChatAvailabilityWindow;
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

final class AvailabilityWindowResource extends Resource
{
    protected static ?string $slug = 'live-chat/availability-windows';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'label';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-live-chat::generic.resources.availability_window'))
                ->schema([
                    TextInput::make('site_id')->label(__('capell-live-chat::generic.fields.site_id'))->numeric()->required(),
                    Select::make('day_of_week')->label(__('capell-live-chat::generic.fields.day_of_week'))->options(self::dayOptions())->required(),
                    TextInput::make('opens_at')->label(__('capell-live-chat::generic.fields.opens_at'))->placeholder('09:00')->required()->maxLength(5),
                    TextInput::make('closes_at')->label(__('capell-live-chat::generic.fields.closes_at'))->placeholder('17:00')->required()->maxLength(5),
                    TextInput::make('timezone')->label(__('capell-live-chat::generic.fields.timezone'))->default('Europe/London')->required()->maxLength(64),
                    TextInput::make('label')->label(__('capell-live-chat::generic.fields.label'))->maxLength(255),
                    Toggle::make('is_active')->label(__('capell-live-chat::generic.fields.is_active'))->default(true),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('site_id')->label(__('capell-live-chat::generic.fields.site_id'))->sortable(),
            TextColumn::make('day_of_week')->label(__('capell-live-chat::generic.fields.day_of_week'))->formatStateUsing(static fn (int|string|null $state): string => self::dayOptions()[(int) $state] ?? ''),
            TextColumn::make('opens_at')->label(__('capell-live-chat::generic.fields.opens_at')),
            TextColumn::make('closes_at')->label(__('capell-live-chat::generic.fields.closes_at')),
            TextColumn::make('timezone')->label(__('capell-live-chat::generic.fields.timezone'))->toggleable(),
            IconColumn::make('is_active')->label(__('capell-live-chat::generic.fields.is_active'))->boolean()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return LiveChatAvailabilityWindow::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-live-chat::generic.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-live-chat::generic.resources.availability_windows');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListAvailabilityWindows::route('/'),
            'create' => CreateAvailabilityWindow::route('/create'),
            'edit' => EditAvailabilityWindow::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function dayOptions(): array
    {
        return [
            1 => __('capell-live-chat::generic.days.1'),
            2 => __('capell-live-chat::generic.days.2'),
            3 => __('capell-live-chat::generic.days.3'),
            4 => __('capell-live-chat::generic.days.4'),
            5 => __('capell-live-chat::generic.days.5'),
            6 => __('capell-live-chat::generic.days.6'),
            7 => __('capell-live-chat::generic.days.7'),
        ];
    }
}
