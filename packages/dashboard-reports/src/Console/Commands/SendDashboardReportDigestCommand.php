<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Console\Commands;

use Capell\DashboardReports\Actions\Dashboard\BuildDefaultContentHealthAction;
use Capell\DashboardReports\Actions\Dashboard\SendDashboardReportDigestAction;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

final class SendDashboardReportDigestCommand extends Command
{
    protected $signature = 'capell:dashboard-reports:send-digest
        {--recipient=* : Recipient email address. Defaults to capell-dashboard-reports.digest_recipients.}
        {--from= : Digest range start date/time.}
        {--to= : Digest range end date/time.}
        {--stale-days= : Content health stale-page threshold override.}';

    protected $description = 'Send Dashboard Reports content health and publishing trend email digests.';

    public function handle(): int
    {
        $rangeStart = $this->rangeStart();
        $rangeEnd = $this->rangeEnd();

        if (! $rangeStart instanceof CarbonImmutable || ! $rangeEnd instanceof CarbonImmutable) {
            return self::INVALID;
        }

        if ($rangeStart->greaterThanOrEqualTo($rangeEnd)) {
            $this->components->error(__('capell-dashboard-reports::dashboard.digest_invalid_range'));

            return self::INVALID;
        }

        $recipients = $this->recipients();

        if ($recipients === []) {
            $this->components->warn(__('capell-dashboard-reports::dashboard.digest_no_recipients'));

            return self::SUCCESS;
        }

        $result = SendDashboardReportDigestAction::run($recipients, $rangeStart, $rangeEnd, $this->staleDays());

        $this->components->info(__('capell-dashboard-reports::dashboard.digest_sent', [
            'sent' => $result['sent'],
            'skipped' => $result['skipped'],
        ]));

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function recipients(): array
    {
        $option = $this->option('recipient');
        $recipients = is_array($option) ? $option : [];

        if ($recipients === []) {
            $configured = config('capell-dashboard-reports.digest_recipients', []);
            $recipients = is_array($configured) ? $configured : [];
        }

        return array_values(array_filter(
            array_map(
                static fn (mixed $recipient): string => is_string($recipient) ? trim($recipient) : '',
                $recipients,
            ),
            static fn (string $recipient): bool => $recipient !== '',
        ));
    }

    private function staleDays(): int
    {
        $option = $this->option('stale-days');

        if (is_numeric($option)) {
            return max(1, (int) $option);
        }

        $configured = config('capell-dashboard-reports.stale_page_threshold_days');

        return is_numeric($configured)
            ? max(1, (int) $configured)
            : BuildDefaultContentHealthAction::DEFAULT_STALE_DAYS;
    }

    private function rangeStart(): ?CarbonImmutable
    {
        $option = $this->option('from');

        if (is_string($option) && trim($option) !== '') {
            return $this->parseDateOption($option, 'from');
        }

        return now()->toImmutable()->subWeek()->startOfDay();
    }

    private function rangeEnd(): ?CarbonImmutable
    {
        $option = $this->option('to');

        if (is_string($option) && trim($option) !== '') {
            return $this->parseDateOption($option, 'to');
        }

        return now()->toImmutable()->endOfDay();
    }

    private function parseDateOption(string $value, string $name): ?CarbonImmutable
    {
        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            $this->components->error(__('capell-dashboard-reports::dashboard.export_invalid_date', [
                'option' => $name,
            ]));

            return null;
        }
    }
}
