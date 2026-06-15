<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Tests\Fixtures;

use Capell\EmailStudio\Contracts\CapturesEmailTemplateThemeScreenshots;
use Capell\EmailStudio\Models\EmailTemplateTheme;

class ThemeScreenshotAdapterFixture implements CapturesEmailTemplateThemeScreenshots
{
    public function capture(EmailTemplateTheme $theme): ?string
    {
        return sprintf('email-themes/%s.png', $theme->key);
    }
}
