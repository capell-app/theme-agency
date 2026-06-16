<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Actions\Dashboard;

use Capell\DashboardReports\Notifications\DashboardReportDigestNotification;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static array{sent: int, skipped: int} run(list<string> $recipients, CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd, int $staleDays)
 */
final class SendDashboardReportDigestAction
{
    use AsObject;

    /**
     * @param  list<string>  $recipients
     * @return array{sent: int, skipped: int}
     */
    public function handle(array $recipients, CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd, int $staleDays): array
    {
        $sent = 0;
        $skipped = 0;
        $previousUser = Auth::user();

        try {
            foreach ($this->normalizeRecipients($recipients) as $email) {
                $actor = $this->resolveActor($email);

                if (! $actor instanceof Model) {
                    $skipped++;

                    continue;
                }

                Auth::guard()->setUser($actor);

                $contentHealth = BuildDefaultContentHealthAction::run($staleDays);
                $publishingTrend = BuildPublishingTrendAction::run($rangeStart, $rangeEnd);

                Notification::route('mail', $email)
                    ->notify(new DashboardReportDigestNotification($contentHealth, $publishingTrend, $rangeStart, $rangeEnd));

                $sent++;
            }
        } finally {
            if ($previousUser instanceof Model) {
                Auth::guard()->setUser($previousUser);
            } else {
                Auth::guard()->logout();
            }
        }

        return [
            'sent' => $sent,
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  list<string>  $recipients
     * @return list<string>
     */
    private function normalizeRecipients(array $recipients): array
    {
        $emails = [];

        foreach ($recipients as $recipient) {
            $email = strtolower(trim($recipient));

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                $emails[] = $email;
            }
        }

        return array_values(array_unique($emails));
    }

    private function resolveActor(string $email): ?Model
    {
        $modelClass = config('auth.providers.users.model');

        if (! is_string($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return null;
        }

        /** @var Model|null $actor */
        $actor = $modelClass::query()
            ->where('email', $email)
            ->first();

        return $actor;
    }
}
