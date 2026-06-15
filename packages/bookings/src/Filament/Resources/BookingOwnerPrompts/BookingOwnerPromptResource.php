<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingOwnerPrompts;

use BackedEnum;
use Capell\Bookings\Enums\BookingOwnerPromptStatusEnum;
use Capell\Bookings\Filament\Resources\BookingOwnerPrompts\Pages\EditBookingOwnerPrompt;
use Capell\Bookings\Filament\Resources\BookingOwnerPrompts\Pages\ListBookingOwnerPrompts;
use Capell\Bookings\Models\BookingOwnerPrompt;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class BookingOwnerPromptResource extends Resource
{
    protected static ?string $slug = 'bookings/owner-prompts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.owner_prompts'))
                ->schema([
                    TextInput::make('type')->label(__('capell-bookings::admin.fields.type'))->required(),
                    Select::make('status')->label(__('capell-bookings::admin.fields.status'))->options(self::statusOptions())->required(),
                    TextInput::make('title')->label(__('capell-bookings::admin.fields.title'))->required(),
                    Textarea::make('body')->label(__('capell-bookings::admin.fields.message'))->rows(6)->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('type')->label(__('capell-bookings::admin.fields.type'))->badge()->sortable(),
            TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
            TextColumn::make('title')->label(__('capell-bookings::admin.fields.title'))->searchable()->sortable(),
            TextColumn::make('created_at')->label(__('capell-bookings::admin.fields.created_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingOwnerPrompt::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.owner_prompts');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingOwnerPrompts::route('/'),
            'edit' => EditBookingOwnerPrompt::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(BookingOwnerPromptStatusEnum::cases())
            ->mapWithKeys(static fn (BookingOwnerPromptStatusEnum $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
