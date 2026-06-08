<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\SeoSuite\Data\PageContentAnalysisData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PageContentAnalysisData run(?string $html, ?string $metaTitle = null, ?string $url = null, list<string> $targetKeywords = [])
 */
final class AnalyzePageContentAction
{
    use AsAction;

    /**
     * @param  list<string>  $targetKeywords
     */
    public function handle(?string $html, ?string $metaTitle = null, ?string $url = null, array $targetKeywords = []): PageContentAnalysisData
    {
        $document = $this->document($html ?? '');
        $xpath = new DOMXPath($document);
        $text = $this->normalizedText($document->textContent);
        $wordCount = $this->wordCount($text);
        $focusKeyword = $targetKeywords[0] ?? null;
        $keywordOccurrences = $focusKeyword !== null ? $this->phraseOccurrences($text, $focusKeyword) : 0;

        return new PageContentAnalysisData(
            focusKeyword: $focusKeyword,
            h1Count: $this->tagCount($document, 'h1'),
            headingOrderValid: $this->headingOrderValid($xpath),
            wordCount: $wordCount,
            focusKeywordOccurrences: $keywordOccurrences,
            focusKeywordDensity: $wordCount > 0 ? round(($keywordOccurrences / $wordCount) * 100, 2) : 0.0,
            titleContainsFocusKeyword: $focusKeyword !== null && $this->containsPhrase($metaTitle ?? '', $focusKeyword),
            urlContainsFocusKeyword: $focusKeyword !== null && $this->containsPhrase(str_replace(['-', '_', '/'], ' ', $url ?? ''), $focusKeyword),
            firstParagraphContainsFocusKeyword: $focusKeyword !== null && $this->containsPhrase($this->firstParagraph($document), $focusKeyword),
            headingTags: $this->headingTags($xpath),
        );
    }

    private function document(string $html): DOMDocument
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="UTF-8"><div>' . $html . '</div>',
            LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);

        return $document;
    }

    private function tagCount(DOMDocument $document, string $tag): int
    {
        return $document->getElementsByTagName($tag)->count();
    }

    private function headingOrderValid(DOMXPath $xpath): bool
    {
        $previousLevel = null;

        foreach ($this->headingElements($xpath) as $headingElement) {
            $level = (int) mb_substr($headingElement->tagName, 1);

            if ($previousLevel !== null && $level > $previousLevel + 1) {
                return false;
            }

            $previousLevel = $level;
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function headingTags(DOMXPath $xpath): array
    {
        $tags = [];

        foreach ($this->headingElements($xpath) as $headingElement) {
            $tags[] = $headingElement->tagName;
        }

        return $tags;
    }

    /**
     * @return list<DOMElement>
     */
    private function headingElements(DOMXPath $xpath): array
    {
        $nodes = $xpath->query('//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');

        if ($nodes === false) {
            return [];
        }

        $headings = [];

        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                $headings[] = $node;
            }
        }

        return $headings;
    }

    private function firstParagraph(DOMDocument $document): string
    {
        $paragraph = $document->getElementsByTagName('p')->item(0);

        if (! $paragraph instanceof DOMNode) {
            return '';
        }

        return $this->normalizedText($paragraph->textContent ?? '');
    }

    private function wordCount(string $text): int
    {
        preg_match_all('/[\p{L}\p{N}]+(?:[\'-][\p{L}\p{N}]+)*/u', $text, $matches);

        return count($matches[0]);
    }

    private function phraseOccurrences(string $text, string $phrase): int
    {
        $pattern = $this->phrasePattern($phrase);

        if ($pattern === null) {
            return 0;
        }

        return preg_match_all($pattern, $text) ?: 0;
    }

    private function containsPhrase(string $text, string $phrase): bool
    {
        $pattern = $this->phrasePattern($phrase);

        return $pattern !== null && preg_match($pattern, $text) === 1;
    }

    private function phrasePattern(string $phrase): ?string
    {
        $phrase = $this->normalizedText($phrase);

        if ($phrase === '') {
            return null;
        }

        $quotedPhrase = preg_quote($phrase, '/');
        $quotedPhrase = preg_replace('/\s+/', '\\s+', $quotedPhrase) ?? $quotedPhrase;

        return '/(?<![\p{L}\p{N}])' . $quotedPhrase . '(?![\p{L}\p{N}])/iu';
    }

    private function normalizedText(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim(mb_strtolower($text));
    }
}
