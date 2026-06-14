<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\EmailTemplates\Pages;

use Capell\EmailStudio\Filament\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListEmailTemplates extends ListRecords
{
    /** @return class-string<EmailTemplateResource> */
    #[Override]
    public static function getResource(): string
    {
        return EmailTemplateResource::class;
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
