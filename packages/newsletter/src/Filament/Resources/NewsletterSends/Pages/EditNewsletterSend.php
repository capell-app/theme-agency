<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Resources\NewsletterSends\Pages;

use Capell\Newsletter\Filament\Resources\NewsletterSends\NewsletterSendResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditNewsletterSend extends EditRecord
{
    protected static string $resource = NewsletterSendResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
