<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Contracts;

use Capell\EmailStudio\Models\EmailTemplateTheme;

interface CapturesEmailTemplateThemeScreenshots
{
    public function capture(EmailTemplateTheme $theme): ?string;
}
