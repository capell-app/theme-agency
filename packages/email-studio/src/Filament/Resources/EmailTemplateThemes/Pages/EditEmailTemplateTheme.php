<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\EmailTemplateThemes\Pages;

use Capell\EmailStudio\Filament\Resources\EmailTemplateThemes\EmailTemplateThemeResource;
use Filament\Resources\Pages\EditRecord;

final class EditEmailTemplateTheme extends EditRecord
{
    protected static string $resource = EmailTemplateThemeResource::class;
}
