<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\SentEmails\Pages;

use Capell\EmailStudio\Filament\Resources\SentEmails\Schemas\SentEmailInfolist;
use Capell\EmailStudio\Filament\Resources\SentEmails\SentEmailResource;
use Capell\EmailStudio\Models\SentEmail;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

/**
 * @property-read SentEmail $record
 */
final class ViewSentEmail extends ViewRecord
{
    /** @return class-string<SentEmailResource> */
    #[Override]
    public static function getResource(): string
    {
        return SentEmailResource::class;
    }

    #[Override]
    public function infolist(Schema $schema): Schema
    {
        return SentEmailInfolist::configure($schema);
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
