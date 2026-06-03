<?php

declare(strict_types=1);

namespace Capell\Events\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Events\Actions\BuildCalendarFeedAction;
use Capell\Events\Actions\BuildEventSchemaAction;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventNotificationLog;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Capell\Events\Models\EventVenue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RRule\RRule;
use Spatie\IcalendarGenerator\Components\Calendar;

final class EventsHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<class-string<Model>>
     */
    private const array MODELS = [
        Event::class,
        EventVenue::class,
        EventOccurrence::class,
        EventRegistration::class,
        EventNotificationLog::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->recurrenceCheck(),
            $check->registrationCapacityCheck(),
            $check->calendarFeedCheck(),
            $check->eventSchemaCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts recurring events can expand into occurrence records: the RRULE
     * library is installed and the event/occurrence storage tables exist.
     */
    public function recurrenceCheck(): DoctorCheckResultData
    {
        $hasRruleLibrary = class_exists(RRule::class);
        $hasOccurrenceTables = Schema::hasTable('events') && Schema::hasTable('event_occurrences');
        $passed = $hasRruleLibrary && $hasOccurrenceTables;

        return new DoctorCheckResultData(
            label: 'Recurring events expand into occurrence records',
            passed: $passed,
            message: $passed
                ? 'The RRULE expander is available and the events and event_occurrences tables are present.'
                : 'Recurrence expansion is unavailable: '
                    . ($hasRruleLibrary ? '' : 'the rlanvin/php-rrule library is missing; ')
                    . ($hasOccurrenceTables ? '' : 'the events or event_occurrences table is missing.'),
            remediation: $passed
                ? null
                : 'Install rlanvin/php-rrule and run the Capell migrations to create the events and event_occurrences tables.',
        );
    }

    /**
     * Asserts the registration table exists and the registration model is
     * morph-mapped, so capacity and waitlist rules have storage to enforce.
     */
    public function registrationCapacityCheck(): DoctorCheckResultData
    {
        $hasRegistrationTable = Schema::hasTable('event_registrations');
        $unregisteredAliases = $this->unregisteredMorphAliases();
        $passed = $hasRegistrationTable && $unregisteredAliases === [];

        return new DoctorCheckResultData(
            label: 'Registrations enforce occurrence capacity and waitlist rules',
            passed: $passed,
            message: $passed
                ? 'The event_registrations table is present and event models are registered in the morph map.'
                : 'Registration capacity enforcement is unavailable: '
                    . ($hasRegistrationTable ? '' : 'the event_registrations table is missing; ')
                    . ($unregisteredAliases === [] ? '' : 'unregistered morph aliases: ' . implode(', ', $unregisteredAliases) . '.'),
            remediation: $passed
                ? null
                : 'Run the Capell migrations and ensure EventsServiceProvider registers the event models in the morph map.',
        );
    }

    /**
     * Asserts the iCalendar generator used to build public feeds is installed.
     */
    public function calendarFeedCheck(): DoctorCheckResultData
    {
        $passed = class_exists(Calendar::class) && class_exists(BuildCalendarFeedAction::class);

        return new DoctorCheckResultData(
            label: 'Public iCal feeds include event occurrence URLs',
            passed: $passed,
            message: $passed
                ? 'The iCalendar generator and calendar feed builder are available.'
                : 'Calendar feed generation is unavailable: the spatie/icalendar-generator library or feed builder is missing.',
            remediation: $passed
                ? null
                : 'Install spatie/icalendar-generator so public .ics feeds can be generated.',
        );
    }

    /**
     * Asserts the schema.org Event builder is available for structured data.
     */
    public function eventSchemaCheck(): DoctorCheckResultData
    {
        $passed = class_exists(BuildEventSchemaAction::class);

        return new DoctorCheckResultData(
            label: 'Event occurrences expose schema.org Event structured data',
            passed: $passed,
            message: $passed
                ? 'The schema.org Event builder is available to emit JSON-LD.'
                : 'Structured data generation is unavailable: the event schema builder is missing.',
            remediation: $passed
                ? null
                : 'Ensure the Events package is installed completely so BuildEventSchemaAction is autoloadable.',
        );
    }

    /**
     * @return list<string>
     */
    public function unregisteredMorphAliases(): array
    {
        return array_values(collect(self::MODELS)
            ->mapWithKeys(static fn (string $modelClass): array => [Str::snake(class_basename($modelClass)) => $modelClass])
            ->reject(static fn (string $modelClass, string $morphAlias): bool => Relation::getMorphedModel($morphAlias) === $modelClass)
            ->keys()
            ->values()
            ->all());
    }
}
