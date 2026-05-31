<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Resources\NewsletterSends\Pages;

use Capell\Newsletter\Filament\Resources\NewsletterSends\NewsletterSendResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListNewsletterSends extends ListRecords
{
    protected static string $resource = NewsletterSendResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
