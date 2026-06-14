<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\EmailTemplateVariants\Pages;

use Capell\EmailStudio\Filament\Resources\EmailTemplateVariants\EmailTemplateVariantResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListEmailTemplateVariants extends ListRecords
{
    /** @return class-string<EmailTemplateVariantResource> */
    #[Override]
    public static function getResource(): string
    {
        return EmailTemplateVariantResource::class;
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
