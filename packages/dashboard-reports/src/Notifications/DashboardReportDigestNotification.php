<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Notifications;

use Capell\Admin\Data\Dashboard\ContentHealthData;
use Capell\Admin\Data\Dashboard\ContentHealthIssueData;
use Capell\DashboardReports\Data\Dashboard\PublishingTrendData;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class DashboardReportDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ContentHealthData $contentHealth,
        private readonly PublishingTrendData $publishingTrend,
        private readonly CarbonImmutable $rangeStart,
        private readonly CarbonImmutable $rangeEnd,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('capell-dashboard-reports::dashboard.digest_subject'))
            ->line(__('capell-dashboard-reports::dashboard.digest_intro', [
                'from' => $this->rangeStart->toFormattedDateString(),
                'to' => $this->rangeEnd->toFormattedDateString(),
            ]))
            ->line(__('capell-dashboard-reports::dashboard.digest_publishing_summary', [
                'published' => $this->publishingTrend->totalPublished,
                'scheduled' => $this->publishingTrend->totalScheduled,
            ]));

        foreach ($this->contentHealth->issues as $issue) {
            if (! $issue instanceof ContentHealthIssueData) {
                continue;
            }

            $message->line(__('capell-dashboard-reports::dashboard.digest_content_issue', [
                'label' => $issue->label,
                'count' => $issue->count,
            ]));
        }

        return $message;
    }
}
