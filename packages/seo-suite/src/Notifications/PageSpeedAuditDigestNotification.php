<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Notifications;

use Capell\SeoSuite\Data\PageSpeedAuditSummaryData;
use Capell\SeoSuite\Filament\Pages\SeoAuditPage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PageSpeedAuditDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PageSpeedAuditSummaryData $summary,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject((string) __('capell-seo-suite::generic.pagespeed_digest_title'))
            ->line((string) __('capell-seo-suite::generic.pagespeed_digest_intro', [
                'pages' => $this->summary->auditedPages,
            ]))
            ->line((string) __('capell-seo-suite::generic.pagespeed_digest_summary', [
                'successful' => $this->summary->successfulResults,
                'failed' => $this->summary->failedResults,
                'poor' => $this->summary->poorResults,
            ]));

        foreach ($this->digestLines() as $line) {
            $message->line($line);
        }

        return $message->action((string) __('capell-seo-suite::generic.pagespeed_digest_view'), SeoAuditPage::getUrl());
    }

    /**
     * @return list<string>
     */
    private function digestLines(): array
    {
        return array_values(array_filter([
            $this->findingLine('pagespeed_digest_worst_mobile', $this->summary->worstMobileResults),
            $this->findingLine('pagespeed_digest_worst_desktop', $this->summary->worstDesktopResults),
            $this->findingLine('pagespeed_digest_biggest_drops', $this->summary->biggestDrops),
            $this->findingLine('pagespeed_digest_below_threshold', $this->summary->belowThresholdResults),
        ]));
    }

    /**
     * @param  array<int, object>  $findings
     */
    private function findingLine(string $translationKey, array $findings): ?string
    {
        if ($findings === []) {
            return null;
        }

        $labels = collect($findings)
            ->filter(fn (object $finding): bool => method_exists($finding, 'label'))
            ->take(3)
            ->map(fn (object $finding): string => $finding->label())
            ->implode('; ');

        if ($labels === '') {
            return null;
        }

        return (string) __('capell-seo-suite::generic.' . $translationKey, ['items' => $labels]);
    }
}
