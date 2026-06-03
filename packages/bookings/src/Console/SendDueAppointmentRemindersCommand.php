<?php

declare(strict_types=1);

namespace Capell\Bookings\Console;

use Capell\Bookings\Actions\QueueAppointmentReminderAction;
use Capell\Bookings\Enums\AppointmentAuditEventEnum;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

final class SendDueAppointmentRemindersCommand extends Command
{
    protected $signature = 'capell:bookings:send-due-reminders
        {--lead-minutes= : Minutes before appointment start to queue reminders}
        {--limit= : Maximum reminders to queue in one run}';

    protected $description = 'Queue reminders for confirmed booking appointments due within the reminder lead window.';

    public function handle(): int
    {
        $leadMinutes = $this->positiveIntOption('lead-minutes', (int) config('capell-bookings.reminder_lead_minutes', 1440));
        $limit = $this->positiveIntOption('limit', (int) config('capell-bookings.reminder_dispatch_limit', 100));
        $now = CarbonImmutable::now();
        $dueUntil = $now->addMinutes($leadMinutes);
        $queued = 0;

        AppointmentRequest::query()
            ->with(['service'])
            ->where('status', AppointmentRequestStatusEnum::Confirmed->value)
            ->whereBetween('requested_starts_at', [$now, $dueUntil])
            ->whereDoesntHave('auditLogs', function (Builder $query): void {
                $query->where('event', AppointmentAuditEventEnum::ReminderQueued->value);
            })
            ->orderBy('requested_starts_at')
            ->limit($limit)
            ->get()
            ->each(function (AppointmentRequest $appointmentRequest) use (&$queued): void {
                QueueAppointmentReminderAction::run($appointmentRequest);

                $queued++;
            });

        $this->components->info(sprintf('Queued %d booking appointment reminder%s.', $queued, $queued === 1 ? '' : 's'));

        return self::SUCCESS;
    }

    private function positiveIntOption(string $name, int $fallback): int
    {
        $value = $this->option($name);

        if (! is_numeric($value) || (int) $value < 1) {
            return max(1, $fallback);
        }

        return (int) $value;
    }
}
