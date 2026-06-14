<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Contracts\CapturesEmailTemplateThemeScreenshots;
use Capell\EmailStudio\Models\EmailTemplateTheme;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * @method static EmailTemplateTheme run(EmailTemplateTheme $theme)
 */
class CaptureEmailTemplateThemeScreenshotAction
{
    use AsAction;

    public function handle(EmailTemplateTheme $theme): EmailTemplateTheme
    {
        $path = $this->adapter()->capture($theme);

        if (is_string($path) && $path !== '') {
            $theme->forceFill(['screenshot_path' => $path])->save();
        }

        return $theme->refresh();
    }

    private function adapter(): CapturesEmailTemplateThemeScreenshots
    {
        $configuredAdapter = config('capell-email-studio.screenshot_adapter');

        if (is_string($configuredAdapter) && $configuredAdapter !== '') {
            $adapter = resolve($configuredAdapter);

            if ($adapter instanceof CapturesEmailTemplateThemeScreenshots) {
                return $adapter;
            }
        }

        if (app()->bound(CapturesEmailTemplateThemeScreenshots::class)) {
            $adapter = resolve(CapturesEmailTemplateThemeScreenshots::class);

            if ($adapter instanceof CapturesEmailTemplateThemeScreenshots) {
                return $adapter;
            }
        }

        throw new RuntimeException('Email template theme screenshot adapter is not configured.');
    }
}
