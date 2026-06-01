<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Resources\NewsletterSends\Pages;

use Capell\Newsletter\Filament\Resources\NewsletterSends\NewsletterSendResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterSend extends CreateRecord
{
    protected static string $resource = NewsletterSendResource::class;
}
