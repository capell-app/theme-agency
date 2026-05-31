<?php

declare(strict_types=1);

namespace Capell\Events\Livewire;

use Capell\Core\Models\Site;
use Capell\Events\Actions\QueryPublicEventOccurrencesAction;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Support\Calendar\CalendarMonth;
use Capell\Frontend\Facades\Frontend;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Collection;
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
        $occurrences = QueryPublicEventOccurrencesAction::run($this->site(), $month->startOfMonth()->startOfWeek(), $month->endOfMonth()->endOfWeek());

        return view('capell-events::livewire.event-calendar', [
            'monthDate' => $month,
            'weeks' => resolve(CalendarMonth::class)->weeks($month),
            'occurrencesByDate' => $this->occurrencesByDate($occurrences),
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
            return 'UTC';
        }

        $timezone = $site->getAttribute('timezone');

        return is_string($timezone) && $timezone !== '' ? $timezone : 'UTC';
    }

    /**
     * @param  Collection<int, EventOccurrence>  $occurrences
     * @return array<string, Collection<int, EventOccurrence>>
     */
    private function occurrencesByDate(Collection $occurrences): array
    {
        return $occurrences
            ->groupBy(fn (EventOccurrence $occurrence): string => $occurrence->starts_at->setTimezone($occurrence->timezone)->toDateString())
            ->all();
    }
}
