<?php

declare(strict_types=1);

namespace Capell\Events\Livewire;

use Capell\Core\Models\Site;
use Capell\Events\Actions\BuildEventOccurrenceViewDataAction;
use Capell\Events\Actions\QueryPublicEventOccurrencesAction;
use Capell\Events\Data\EventOccurrenceViewData;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Support\Calendar\CalendarMonth;
use Capell\Frontend\Facades\Frontend;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeZone;
use Livewire\Component;
use RuntimeException;

class EventCalendar extends Component
{
    public string $month;

    public function mount(?string $month = null): void
    {
        $this->month = $month ?? CarbonImmutable::now($this->siteTimezone())->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->month = $this->calendarMonth()->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->calendarMonth()->addMonth()->format('Y-m');
    }

    public function render(): mixed
    {
        $month = $this->calendarMonth();
        $viewerTimezone = $this->viewerTimezone();
        $occurrences = QueryPublicEventOccurrencesAction::run($this->site(), $month->startOfMonth()->startOfWeek(), $month->endOfMonth()->endOfWeek())
            ->map(fn (EventOccurrence $occurrence): EventOccurrenceViewData => BuildEventOccurrenceViewDataAction::run($occurrence, $viewerTimezone))
            ->values();

        return view('capell-events::livewire.event-calendar', [
            'monthDate' => $month,
            'weeks' => resolve(CalendarMonth::class)->weeks($month),
            'occurrences' => $occurrences,
        ]);
    }

    private function site(): Site
    {
        $site = Frontend::site();

        throw_unless($site instanceof Site, RuntimeException::class, 'Event calendar requires a frontend site context.');

        return $site;
    }

    private function calendarMonth(): CarbonImmutable
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->month)) {
            return $this->fallbackMonth();
        }

        try {
            $month = CarbonImmutable::createFromFormat('!Y-m-d', $this->month . '-01');
        } catch (InvalidFormatException) {
            return $this->fallbackMonth();
        }

        return $month instanceof CarbonImmutable ? $month : $this->fallbackMonth();
    }

    private function fallbackMonth(): CarbonImmutable
    {
        return CarbonImmutable::now($this->siteTimezone())->startOfMonth();
    }

    private function siteTimezone(): string
    {
        $site = $this->site();

        if (! array_key_exists('timezone', $site->getAttributes())) {
            return $this->defaultTimezone();
        }

        $timezone = $site->getAttribute('timezone');

        return is_string($timezone) && in_array($timezone, DateTimeZone::listIdentifiers(), true)
            ? $timezone
            : $this->defaultTimezone();
    }

    private function viewerTimezone(): ?string
    {
        $timezone = request()->query('timezone');

        if (! is_string($timezone) || $timezone === '') {
            $page = Frontend::page();
            $timezone = $page?->meta['viewer_timezone']
                ?? $page?->meta['default_timezone']
                ?? config('capell-events.display.default_timezone');
        }

        return is_string($timezone) && in_array($timezone, DateTimeZone::listIdentifiers(), true) ? $timezone : null;
    }

    private function defaultTimezone(): string
    {
        $timezone = config('capell-events.display.default_timezone', 'UTC');

        return is_string($timezone) && in_array($timezone, DateTimeZone::listIdentifiers(), true) ? $timezone : 'UTC';
    }
}
