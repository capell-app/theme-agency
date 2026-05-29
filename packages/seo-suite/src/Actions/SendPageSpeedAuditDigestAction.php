<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Admin\Actions\Notifications\ResolveAdminNotificationRecipientsAction;
use Capell\SeoSuite\Data\PageSpeedAuditSummaryData;
use Capell\SeoSuite\Filament\Pages\SeoAuditPage;
use Capell\SeoSuite\Notifications\PageSpeedAuditDigestNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class SendPageSpeedAuditDigestAction
{
    use AsAction;

    public const string NOTIFICATION_GROUP = 'seo_suite_pagespeed_reports';

    public function handle(PageSpeedAuditSummaryData $summary): void
    {
        $recipients = ResolveAdminNotificationRecipientsAction::run(self::NOTIFICATION_GROUP);

        foreach ($recipients as $recipient) {
            if (! $recipient instanceof Model) {
                continue;
            }

            if (! $recipient instanceof Authenticatable) {
                continue;
            }

            $this->sendFilamentNotification($summary, $recipient);
        }

        if ($recipients->isNotEmpty()) {
            try {
                Notification::send($recipients, new PageSpeedAuditDigestNotification($summary));
            } catch (Throwable $throwable) {
                report($throwable);
            }
        }

        $summary->run->update([
            'notification_status' => $recipients->isEmpty() ? 'no_recipients' : 'sent',
            'notified_at' => now(),
        ]);
    }

    private function sendFilamentNotification(PageSpeedAuditSummaryData $summary, Authenticatable&Model $recipient): void
    {
        $notification = FilamentNotification::make('seo-suite-pagespeed-audit-' . $summary->run->getKey())
            ->title(__('capell-seo-suite::generic.pagespeed_digest_title'))
            ->body($this->databaseBody($summary))
            ->icon(Heroicon::OutlinedBolt)
            ->warning()
            ->persistent()
            ->actions([
                Action::make('viewSeoAudit')
                    ->label(__('capell-seo-suite::generic.pagespeed_digest_view'))
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->link()
                    ->close()
                    ->url(SeoAuditPage::getUrl()),
            ]);

        try {
            $notification->broadcast($recipient);

            if (Schema::hasTable('notifications')) {
                $notification->sendToDatabase($recipient);
            }
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }

    private function databaseBody(PageSpeedAuditSummaryData $summary): string
    {
        $body = (string) __('capell-seo-suite::generic.pagespeed_digest_body', [
            'pages' => $summary->auditedPages,
            'failed' => $summary->failedResults,
            'poor' => $summary->poorResults,
        ]);

        $worst = collect([...$summary->worstMobileResults, ...$summary->worstDesktopResults])
            ->sortBy('score')
            ->first();

        if (is_object($worst) && method_exists($worst, 'label')) {
            return $body . ' ' . __('capell-seo-suite::generic.pagespeed_digest_worst_result', [
                'result' => $worst->label(),
            ]);
        }

        return $body;
    }
}
