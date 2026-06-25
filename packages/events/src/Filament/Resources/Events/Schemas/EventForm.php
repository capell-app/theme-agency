<?php

declare(strict_types=1);

namespace Capell\Events\Filament\Resources\Events\Schemas;

use Capell\Admin\Data\Configurators\ConfiguratorContextData;
use Capell\Admin\Filament\Components\Forms\Page\BlueprintSelect;
use Capell\Admin\Filament\Components\Forms\Page\LayoutSelect;
use Capell\Admin\Filament\Components\Forms\SiteSelect;
use Capell\Admin\Filament\Contracts\FormConfigurator;
use Capell\Admin\Filament\Livewire\PublishStatusPanel;
use Capell\Events\Enums\EventBookingModeEnum;
use Capell\Events\Enums\EventLocationModeEnum;
use Capell\Events\Enums\EventVisibilityEnum;
use Capell\Events\Enums\ResourceEnum;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventVenue;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm implements FormConfigurator
{
    public static function configure(Schema $schema, ?ConfiguratorContextData $context = null): Schema
    {
        return $schema
            ->components([
                ...self::publishPanel($schema),
                Section::make(__('capell-events::form.event_details'))
                    ->schema([
                        SiteSelect::make('site_id'),
                        BlueprintSelect::make('blueprint_id')
                            ->pageGroup(strtolower(ResourceEnum::Event->name))
                            ->withRelation()
                            ->required(),
                        LayoutSelect::make('layout_id')->required(),
                        TextInput::make('name')
                            ->label(__('capell-events::table.name'))
                            ->required()
                            ->maxLength(255),
                        Select::make('visibility')
                            ->label(__('capell-events::form.visibility'))
                            ->options(EventVisibilityEnum::class)
                            ->required(),
                    ])
                    ->columns(),
                Section::make(__('capell-events::form.event_schedule'))
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label(__('capell-events::form.starts_at'))
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->label(__('capell-events::form.ends_at')),
                        TextInput::make('timezone')
                            ->label(__('capell-events::form.timezone'))
                            ->default('UTC')
                            ->required(),
                        Toggle::make('all_day')
                            ->label(__('capell-events::form.all_day')),
                        Textarea::make('recurrence_rule')
                            ->label(__('capell-events::form.recurrence_rule'))
                            ->columnSpanFull(),
                    ])
                    ->columns(),
                Section::make(__('capell-events::form.event_settings'))
                    ->schema([
                        Select::make('location_mode')
                            ->label(__('capell-events::form.location_mode'))
                            ->options(EventLocationModeEnum::class)
                            ->required(),
                        Select::make('event_venue_id')
                            ->label(__('capell-events::form.venue'))
                            ->relationship('venue', 'name')
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => EventVenue::query()->ordered()->pluck('name', 'id')->all()),
                    ])
                    ->columns(),
                Section::make(__('capell-events::form.registration'))
                    ->schema([
                        Select::make('booking_mode')
                            ->label(__('capell-events::form.booking_mode'))
                            ->options(EventBookingModeEnum::class)
                            ->required(),
                        TextInput::make('booking_url')
                            ->label(__('capell-events::form.booking_url'))
                            ->url()
                            ->maxLength(255),
                        TextInput::make('capacity')
                            ->label(__('capell-events::form.capacity'))
                            ->numeric()
                            ->minValue(1),
                        Toggle::make('waitlist_enabled')
                            ->label(__('capell-events::form.waitlist_enabled'))
                            ->default(true),
                        Toggle::make('notification_settings.reminders_enabled')
                            ->label(__('capell-events::form.reminders_enabled'))
                            ->default(true),
                        TagsInput::make('notification_settings.reminder_offsets_minutes')
                            ->label(__('capell-events::form.reminder_offsets_minutes'))
                            ->placeholder(__('capell-events::form.reminder_offsets_minutes_placeholder'))
                            ->helperText(__('capell-events::form.reminder_offsets_minutes_help')),
                    ])
                    ->columns(),
            ])
            ->columns();
    }

    /**
     * The shared WordPress-style publish panel, pinned to the top of the event
     * editor. Edit only — on create there is no record to act on yet.
     *
     * @return array<int, Livewire>
     */
    protected static function publishPanel(Schema $schema): array
    {
        $record = $schema->getRecord();

        if (! $record instanceof Event || $schema->getOperation() !== 'edit') {
            return [];
        }

        $key = $record->getKey();

        return [
            Livewire::make(PublishStatusPanel::class, [
                'recordClass' => Event::class,
                'recordId' => is_scalar($key) ? (int) $key : 0,
            ])->columnSpanFull(),
        ];
    }
}
