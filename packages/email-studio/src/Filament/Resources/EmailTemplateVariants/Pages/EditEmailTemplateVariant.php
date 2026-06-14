<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\EmailTemplateVariants\Pages;

use Capell\EmailStudio\Filament\Resources\EmailTemplateVariants\EmailTemplateVariantResource;
use Filament\Resources\Pages\EditRecord;

final class EditEmailTemplateVariant extends EditRecord
{
    protected static string $resource = EmailTemplateVariantResource::class;
}
