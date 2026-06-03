<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Lorisleiva\Actions\Concerns\AsObject;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitises author-authored article HTML before it is rendered as raw markup on
 * the anonymous frontend. The public article body is emitted via Blade
 * `{!! !!}` in `resources/views/article.blade.php`, so any unsanitised
 * `<script>`, `<iframe>`, or inline event handler authored in the admin would
 * execute for every visitor (stored XSS). This action strips dangerous markup
 * while preserving the safe rich-text elements that legitimate documentation
 * relies on (headings, paragraphs, lists, links, images, tables, code, etc.).
 */
final class SanitizeKnowledgeBaseArticleHtmlAction
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
