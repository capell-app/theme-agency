<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\LessonSeries;

use BackedEnum;
use Capell\Bookings\Filament\Resources\LessonSeries\Pages\CreateLessonSeries;
use Capell\Bookings\Filament\Resources\LessonSeries\Pages\EditLessonSeries;
use Capell\Bookings\Filament\Resources\LessonSeries\Pages\ListLessonSeries;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Bookings\Models\LessonSeries;
use Capell\Core\Support\Database\RuntimeSchemaState;
use Filament\Forms\Components\DatePicker;
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

final class LessonSeriesResource extends Resource
{
    protected static ?string $slug = 'bookings/lesson-series';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?string $recordTitleAttribute = 'customer_name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.lesson_series'))
                ->schema([
                    TextInput::make('site_id')->label(__('capell-bookings::admin.fields.site_id'))->numeric()->minValue(1),
                    TextInput::make('portal_account_id')->label(__('capell-bookings::admin.fields.portal_account_id'))->numeric()->minValue(1),
                    Select::make('service_id')->label(__('capell-bookings::admin.fields.service'))->options(self::serviceOptions())->searchable()->required(),
                    Select::make('staff_member_id')->label(__('capell-bookings::admin.fields.staff_member'))->options(self::staffOptions())->searchable(),
                    Select::make('location_id')->label(__('capell-bookings::admin.fields.location'))->options(self::locationOptions())->searchable(),
                    TextInput::make('customer_name')->label(__('capell-bookings::admin.fields.customer_name'))->required()->maxLength(255),
                    TextInput::make('customer_email')->label(__('capell-bookings::admin.fields.customer_email'))->email()->required()->maxLength(255),
                    TextInput::make('customer_phone')->label(__('capell-bookings::admin.fields.customer_phone'))->maxLength(255),
                    Select::make('day_of_week')->label(__('capell-bookings::admin.fields.day_of_week'))->options(__('capell-bookings::admin.weekdays'))->required(),
                    TextInput::make('starts_at')->label(__('capell-bookings::admin.fields.starts_at'))->required()->maxLength(8),
                    TextInput::make('duration_minutes')->label(__('capell-bookings::admin.fields.duration_minutes'))->numeric()->minValue(1),
                    TextInput::make('timezone')->label(__('capell-bookings::admin.fields.timezone'))->required()->default('UTC')->maxLength(64),
                    TextInput::make('cadence_weeks')->label(__('capell-bookings::admin.fields.cadence_weeks'))->numeric()->minValue(1)->default(1)->required(),
                    DatePicker::make('active_from')->label(__('capell-bookings::admin.fields.active_from'))->required(),
                    DatePicker::make('active_until')->label(__('capell-bookings::admin.fields.active_until')),
                    DatePicker::make('materialized_until')->label(__('capell-bookings::admin.fields.materialized_until'))->disabled(),
                    Toggle::make('active')->label(__('capell-bookings::admin.fields.active'))->default(true),
                    Toggle::make('auto_confirm_instances')->label(__('capell-bookings::admin.fields.auto_confirm_instances'))->default(true),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('customer_name')->label(__('capell-bookings::admin.fields.customer_name'))->searchable()->sortable(),
            TextColumn::make('customer_email')->label(__('capell-bookings::admin.fields.customer_email'))->searchable()->toggleable(),
            TextColumn::make('service.name')->label(__('capell-bookings::admin.fields.service'))->sortable(),
            TextColumn::make('day_of_week')->label(__('capell-bookings::admin.fields.day_of_week'))->formatStateUsing(fn (int|string|null $state): string => self::weekdayLabel($state))->sortable(),
            TextColumn::make('starts_at')->label(__('capell-bookings::admin.fields.starts_at'))->sortable(),
            TextColumn::make('cadence_weeks')->label(__('capell-bookings::admin.fields.cadence_weeks'))->numeric()->sortable(),
            IconColumn::make('active')->label(__('capell-bookings::admin.fields.active'))->boolean()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return LessonSeries::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.lesson_series');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListLessonSeries::route('/'),
            'create' => CreateLessonSeries::route('/create'),
            'edit' => EditLessonSeries::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int|string, string>
     */
    private static function serviceOptions(): array
    {
        if (! resolve(RuntimeSchemaState::class)->hasTable((new BookingService)->getTable())) {
            return [];
        }

        $serviceOptions = [];

        foreach (BookingService::query()->orderBy('name')->pluck('name', 'id')->all() as $id => $name) {
            if (! is_string($name) && ! is_int($name) && ! is_float($name)) {
                continue;
            }

            $serviceOptions[$id] = (string) $name;
        }

        return $serviceOptions;
    }

    /**
     * @return array<int|string, string>
     */
    private static function staffOptions(): array
    {
        if (! resolve(RuntimeSchemaState::class)->hasTable((new BookingStaffMember)->getTable())) {
            return [];
        }

        $staffOptions = [];

        foreach (BookingStaffMember::query()->orderBy('display_name')->pluck('display_name', 'id')->all() as $id => $name) {
            if (! is_string($name) && ! is_int($name) && ! is_float($name)) {
                continue;
            }

            $staffOptions[$id] = (string) $name;
        }

        return $staffOptions;
    }

    /**
     * @return array<int|string, string>
     */
    private static function locationOptions(): array
    {
        if (! resolve(RuntimeSchemaState::class)->hasTable((new BookingLocation)->getTable())) {
            return [];
        }

        $locationOptions = [];

        foreach (BookingLocation::query()->orderBy('name')->pluck('name', 'id')->all() as $id => $name) {
            if (! is_string($name) && ! is_int($name) && ! is_float($name)) {
                continue;
            }

            $locationOptions[$id] = (string) $name;
        }

        return $locationOptions;
    }

    private static function weekdayLabel(int|string|null $state): string
    {
        $weekdays = __('capell-bookings::admin.weekdays');

        if (! is_array($weekdays)) {
            return (string) $state;
        }

        return (string) ($weekdays[(int) $state] ?? $state);
    }
}
