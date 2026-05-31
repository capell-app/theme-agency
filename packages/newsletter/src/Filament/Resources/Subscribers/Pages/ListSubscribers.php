<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Resources\Subscribers\Pages;

use Capell\Admin\Filament\Components\Forms\SiteSelect;
use Capell\Newsletter\Actions\ExportSubscribersAction;
use Capell\Newsletter\Filament\Resources\Subscribers\SubscriberResource;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Support\NewsletterAdminAccess;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Gate;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListSubscribers extends ListRecords
{
    protected static string $resource = SubscriberResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('export_subscribers')
                ->label(__('capell-newsletter::actions.export_subscribers'))
                ->form([
                    SiteSelect::make('site_id')->required(),
                ])
                ->action(function (array $data): StreamedResponse {
                    Gate::authorize('viewAny', Subscriber::class);
                    NewsletterAdminAccess::authorizeSiteId((int) $data['site_id']);

                    return $this->exportSubscribers((int) $data['site_id']);
                }),
        ];
    }

    private function exportSubscribers(int $siteId): StreamedResponse
    {
        return response()->streamDownload(function () use ($siteId): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fputcsv($output, ['email', 'first_name', 'last_name', 'status', 'subscribed_at', 'unsubscribed_at']);

            ExportSubscribersAction::run($siteId)
                ->each(static function (array $row) use ($output): void {
                    fputcsv($output, $row);
                });
        }, 'newsletter-subscribers.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
