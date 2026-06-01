<?php

declare(strict_types=1);

use Capell\Admin\Testing\Filament\ReadsRawSchemaComponents;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Events\Filament\Resources\Events\EventResource;
use Capell\Events\Filament\Resources\Events\Schemas\EventForm;
use Capell\Events\Filament\Widgets\EventCalendarWidget;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Support\Calendar\CalendarMonth;
use Capell\Events\Support\Calendar\CalendarWeek;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

it('builds visible calendar weeks for a whole month grid', function (): void {
    $weeks = (new CalendarMonth)->weeks(CarbonImmutable::parse('2026-02-15'));
    $firstWeek = $weeks->first();
    $lastWeek = $weeks->last();

    throw_if(! $firstWeek instanceof CalendarWeek || ! $lastWeek instanceof CalendarWeek, RuntimeException::class, 'Expected calendar month to return first and last weeks.');

    $firstDay = $firstWeek->days->first();
    $lastDay = $lastWeek->days->last();

    throw_if(! $firstDay instanceof CarbonImmutable || ! $lastDay instanceof CarbonImmutable, RuntimeException::class, 'Expected calendar weeks to contain first and last days.');

    expect($weeks)->toHaveCount(5)
        ->and($firstWeek)->toBeInstanceOf(CalendarWeek::class)
        ->and($firstWeek->days)->toHaveCount(7)
        ->and($firstDay->toDateString())->toBe('2026-01-26')
        ->and($lastDay->toDateString())->toBe('2026-03-01');
});

it('builds the event resource form schema', function (): void {
    $components = EventForm::configure(Schema::make())->getComponents();

    expect($components)
        ->toHaveCount(4)
        ->each->toBeInstanceOf(Section::class);

    $fields = collect($components)
        ->flatMap(function (mixed $section): array {
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

it('groups upcoming event occurrences by calendar date', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 09:00:00'));

    EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-05 10:00:00'),
        'ends_at' => CarbonImmutable::parse('2026-06-05 12:00:00'),
    ]);
    EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-05 14:00:00'),
        'ends_at' => CarbonImmutable::parse('2026-06-05 16:00:00'),
    ]);
    EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-09-15 10:00:00'),
        'ends_at' => CarbonImmutable::parse('2026-09-15 12:00:00'),
    ]);

    try {
        $groups = (new EventCalendarWidget)->occurrencesByDate();

        expect($groups->keys()->all())->toBe(['2026-06-05'])
            ->and($groups->get('2026-06-05'))->toHaveCount(2);
    } finally {
        CarbonImmutable::setTestNow();
    }
});
