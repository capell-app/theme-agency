<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support;

final class PublicOutputLeakScanner
{
    /**
     * @return list<string>
     */
    public function labels(string $content): array
    {
        $matches = [];

        foreach ($this->substringPatterns() as $label => $needles) {
            if ($this->containsAnyNeedle($content, $needles)) {
                $matches[] = $label;
            }
        }

        foreach ($this->regexPatterns() as $label => $patterns) {
            if ($this->matchesAnyPattern($content, $patterns)) {
                $matches[] = $label;
            }
        }

        return array_values(array_unique($matches));
    }

    public function redact(string $content): string
    {
        $redacted = preg_replace(
            '/([?&](?:expires|signature|token)=)[^\s&<>"\']+/i',
            '$1[redacted]',
            $content,
        );

        $redacted = is_string($redacted) ? $redacted : $content;

        $redacted = preg_replace(
            '/(\b(?:editorUrl|editor_url|expires|signature|signedUrl|signed_url|token)\s*[:=]\s*["\']?)[^&"\'\s,}]+/i',
            '$1[redacted]',
            $redacted,
        );

        return is_string($redacted) ? $redacted : $content;
    }

    /**
     * @param  list<string>  $needles
     */
    private function containsAnyNeedle(string $content, array $needles): bool
    {
        $lowerContent = mb_strtolower($content);

        foreach ($needles as $needle) {
            if (str_contains($lowerContent, mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $patterns
     */
    private function matchesAnyPattern(string $content, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, list<string>>
     */
    private function substringPatterns(): array
    {
        return [
            __('capell-seo-suite::generic.public_output_leak_admin_url') => ['/admin'],
            __('capell-seo-suite::generic.public_output_leak_filament_url') => ['/filament'],
            __('capell-seo-suite::generic.public_output_leak_signature_parameter') => ['signature='],
            __('capell-seo-suite::generic.public_output_leak_expires_parameter') => ['expires='],
            __('capell-seo-suite::generic.public_output_leak_livewire_directive') => ['wire:'],
            __('capell-seo-suite::generic.public_output_leak_livewire_internal') => ['livewire'],
            __('capell-seo-suite::generic.public_output_leak_field_path') => ['field_path', 'field-path', 'fieldPath', 'data-field-path'],
            __('capell-seo-suite::generic.public_output_leak_model_id') => ['model_id', 'model-id', 'modelId', 'data-model-id'],
            __('capell-seo-suite::generic.public_output_leak_page_id') => ['page_id', 'page-id', 'pageId', 'data-page-id'],
            __('capell-seo-suite::generic.public_output_leak_editor_metadata') => [
                'editor-only',
                'capell-editor',
                'capell-authoring',
                'data-capell-editor',
                'data-editor',
                'data-editable',
                'frontend-authoring',
            ],
            __('capell-seo-suite::generic.public_output_leak_permission_marker') => [
                'can-edit',
                'data-permission',
                'permission:',
                'permissions:',
            ],
            __('capell-seo-suite::generic.public_output_leak_draft_marker') => ['draft=true', 'status=draft', '/draft'],
            __('capell-seo-suite::generic.public_output_leak_unpublished_marker') => ['unpublished=true', 'status=unpublished', '/unpublished'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function regexPatterns(): array
    {
        return [
            __('capell-seo-suite::generic.public_output_leak_signature_parameter') => [
                '/\bsignature\s*[:=]\s*["\']?[^"\'\s,}]+/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_expires_parameter') => [
                '/\bexpires\s*[:=]\s*["\']?[^"\'\s,}]+/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_field_path') => [
                '/\b(?:fieldPath|field[_-]?path|data-field-path)\b/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_model_id') => [
                '/\b(?:data-model-id|modelId|model[_-]?id)\b\s*(?:=|:)?\s*["\']?\d+/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_page_id') => [
                '/\b(?:data-page-id|pageId|page[_-]?id)\b\s*(?:=|:)?\s*["\']?\d+/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_permission_marker') => [
                '/["\']permissions?["\']\s*:/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_draft_marker') => [
                '/\b(?:data-status|status)\s*=\s*["\']draft["\']/i',
                '/["\'](?:draft|is_draft)["\']\s*:\s*true/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_unpublished_marker') => [
                '/\b(?:data-status|status)\s*=\s*["\']unpublished["\']/i',
                '/["\'](?:unpublished|is_unpublished)["\']\s*:\s*true/i',
            ],
        ];
    }
}
