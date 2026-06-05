<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\SentEmails\Pages;

use Capell\EmailStudio\Filament\Resources\SentEmails\SentEmailResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ListSentEmails extends ListRecords
{
    /** @return class-string<SentEmailResource> */
    #[Override]
    public static function getResource(): string
    {
        return SentEmailResource::class;
    }

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return __('capell-email-studio::mail_tracker.subheading.sent_emails');
    }

    /**
     * @return array<int, mixed>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [];
    }
}
