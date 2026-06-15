<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\EmailTemplateThemes\Pages;

use Capell\EmailStudio\Filament\Resources\EmailTemplateThemes\EmailTemplateThemeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListEmailTemplateThemes extends ListRecords
{
    /** @return class-string<EmailTemplateThemeResource> */
    #[Override]
    public static function getResource(): string
    {
        return EmailTemplateThemeResource::class;
    }

    /**
     * @return array<int, mixed>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
