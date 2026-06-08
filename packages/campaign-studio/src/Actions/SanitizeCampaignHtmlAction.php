<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Lorisleiva\Actions\Concerns\AsObject;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class SanitizeCampaignHtmlAction
{
    use AsObject;

    private static ?HtmlSanitizer $sanitizer = null;

    public function handle(string $html): string
    {
        return $this->sanitizer()->sanitize($html);
    }

    private function sanitizer(): HtmlSanitizer
    {
        if (self::$sanitizer instanceof HtmlSanitizer) {
            return self::$sanitizer;
        }

        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->allowAttribute('class', '*')
            ->allowAttribute('id', '*')
            ->withMaxInputLength(-1);

        return self::$sanitizer = new HtmlSanitizer($config);
    }
}
