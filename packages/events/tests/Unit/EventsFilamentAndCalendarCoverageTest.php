<?php

declare(strict_types=1);

use Capell\Admin\Testing\Filament\ReadsRawSchemaComponents;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Events\Filament\Resources\Events\EventResource;
use Capell\Events\Filament\Resources\Events\Schemas\EventForm;
use Capell\Events\Models\Event;
use Capell\Events\Support\Calendar\CalendarMonth;
use Capell\Events\Support\Calendar\CalendarWeek;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

it('builds visible calendar weeks for a whole month grid', function (): void {
    $weeks = (new CalendarMonth)->weeks(CarbonImmutable::parse('2026-02-15'));

    expect($weeks)->toHaveCount(5)
        ->and($weeks->first())->toBeInstanceOf(CalendarWeek::class)
        ->and($weeks->first()->days)->toHaveCount(7)
        ->and($weeks->first()->days->first()->toDateString())->toBe('2026-01-26')
        ->and($weeks->last()->days->last()->toDateString())->toBe('2026-03-01');
});

it('builds the event resource form schema', function (): void {
    $components = EventForm::configure(Schema::make())->getComponents();

    expect($components)
        ->toHaveCount(4)
        ->each->toBeInstanceOf(Section::class);

    $fields = collect($components)
        ->flatMap(function (Component $section): array {
            throw_unless($section instanceof Section);

            return ReadsRawSchemaComponents::childComponents($section);
        })
        ->values();

    expect($fields)
        ->toHaveCount(16)
        ->and($fields[3])->toBeInstanceOf(TextInput::class)
        ->and($fields[5])->toBeInstanceOf(DateTimePicker::class)
        ->and($fields[8])->toBeInstanceOf(Toggle::class)
        ->and($fields[10])->toBeInstanceOf(Select::class)
        ->and($fields[9])->toBeInstanceOf(Textarea::class);
});

it('declares event resource metadata and route defaults', function (): void {
    $site = new Site;
    $language = new Language;

    expect(EventResource::getModel())->toBe(Event::class)
        ->and(EventResource::getNavigationGroup())->toBe('capell-admin::navigation.group_content')
        ->and(EventResource::getNavigationLabel())->toBe('Events')
        ->and(EventResource::getPluralModelLabel())->toBe('Events')
        ->and(EventResource::getBreadcrumb())->toBe('Events')
        ->and(EventResource::getNavigationParentItem())->toBeNull()
        ->and(EventResource::getResourceName())->toBe('event')
        ->and(EventResource::getBasePath($site, $language))->toBe('/events/')
        ->and(EventResource::getPages())->toHaveKeys(['index', 'create', 'edit']);
});
