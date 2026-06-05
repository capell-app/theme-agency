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
            '#(/authoring/regions/)[A-Za-z0-9_-]+#',
            '$1[redacted]',
            $content,
        );

        $redacted = is_string($redacted) ? $redacted : $content;

        $redacted = preg_replace(
            '/([?&](?:expires|signature|token)=)[^\s&<>"\']+/i',
            '$1[redacted]',
            $redacted,
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
            __('capell-seo-suite::generic.public_output_leak_admin_url') => [
                '/admin/',
                '/admin?',
                '/admin#',
                '/admin/api/page-tree',
            ],
            __('capell-seo-suite::generic.public_output_leak_filament_url') => [
                '/filament/',
                '/filament?',
                '/filament#',
                '/filament-peek/preview',
            ],
            __('capell-seo-suite::generic.public_output_leak_signature_parameter') => ['signature='],
            __('capell-seo-suite::generic.public_output_leak_expires_parameter') => ['expires='],
            __('capell-seo-suite::generic.public_output_leak_livewire_internal') => [
                '/livewire/',
                'livewire/update',
                'wire:id',
                'wire:snapshot',
                'wire:effects',
                'data-livewire',
            ],
            __('capell-seo-suite::generic.public_output_leak_field_path') => ['data-field-path'],
            __('capell-seo-suite::generic.public_output_leak_model_id') => ['data-model-id'],
            __('capell-seo-suite::generic.public_output_leak_page_id') => ['data-page-id'],
            __('capell-seo-suite::generic.public_output_leak_editor_metadata') => [
                '/authoring/regions/',
                'capell-authoring-',
                'capell-frontend-authoring',
                'capellfrontendauthoring',
                'capell-editor',
                'data-capell-authoring',
                'data-capell-editor',
                'data-editable',
                'edit_url',
                'editable_regions',
                'editor-only',
                'frontend-authoring',
                'window.capellfrontendauthoring',
            ],
            __('capell-seo-suite::generic.public_output_leak_permission_marker') => [
                'can-edit',
                'data-can-edit',
                'data-permission',
            ],
            __('capell-seo-suite::generic.public_output_leak_draft_marker') => [
                'data-draft',
                'draft=true',
                'is_draft',
                'isdraft',
                'status=draft',
            ],
            __('capell-seo-suite::generic.public_output_leak_unpublished_marker') => [
                'data-unpublished',
                'is_unpublished',
                'isunpublished',
                'status=unpublished',
                'unpublished=true',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function regexPatterns(): array
    {
        return [
            __('capell-seo-suite::generic.public_output_leak_admin_url') => [
                '~(?<![A-Za-z0-9_-])/(?:admin)(?:[/?#)"\'\s]|$)~i',
            ],
            __('capell-seo-suite::generic.public_output_leak_filament_url') => [
                '~(?<![A-Za-z0-9_-])/(?:filament|filament-peek)(?:[/?#)"\'\s]|$)~i',
            ],
            __('capell-seo-suite::generic.public_output_leak_signature_parameter') => [
                '/\bsignature\s*[:=]\s*["\']?[^"\'\s,}]+/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_expires_parameter') => [
                '/\bexpires\s*[:=]\s*["\']?[^"\'\s,}]+/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_livewire_directive') => [
                '/\bwire:[A-Za-z][\w.-]*\s*=/',
            ],
            __('capell-seo-suite::generic.public_output_leak_livewire_internal') => [
                '/\bLivewire\.(?:dispatch|find|first|on|start)\s*\(/',
                '/\bwindow\.Livewire\b/',
                '/\bwire:(?:effects|id|snapshot)\s*=/',
            ],
            __('capell-seo-suite::generic.public_output_leak_field_path') => [
                '/(?:\bdata-field-path\b|["\'](?:fieldPath|field[_-]?path)["\'])\s*(?:=|:)\s*["\'][^"\']+["\']/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_model_id') => [
                '/(?:\bdata-model-id\b|["\'](?:modelId|model[_-]?id)["\'])\s*(?:=|:)\s*["\']?\d+/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_page_id') => [
                '/(?:\bdata-page-id\b|["\'](?:pageId|page[_-]?id)["\'])\s*(?:=|:)\s*["\']?\d+/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_editor_metadata') => [
                '#/authoring/regions/[A-Za-z0-9_-]+#',
                '/["\'](?:currentUrl|edit_url|editable_regions|languageId|pageUrlId|recordKey|regionKey|selector|siteId)["\']\s*:/',
                '/\bCapell\\\\(?:Admin|Core|Frontend|FrontendAuthoring|LayoutBuilder)\\\\[A-Za-z0-9_\\\\]+/',
            ],
            __('capell-seo-suite::generic.public_output_leak_permission_marker') => [
                '/["\']permissions?["\']\s*:/i',
            ],
            __('capell-seo-suite::generic.public_output_leak_draft_marker') => [
                '/\b(?:data-status|status)\s*=\s*["\']draft["\']/i',
                '/["\'](?:draft|isDraft|is_draft)["\']\s*:\s*true/i',
                '~(?<![A-Za-z0-9_-])/draft(?:[/?#)"\'\s]|$)~i',
            ],
            __('capell-seo-suite::generic.public_output_leak_unpublished_marker') => [
                '/\b(?:data-status|status)\s*=\s*["\']unpublished["\']/i',
                '/["\'](?:isUnpublished|is_unpublished|unpublished)["\']\s*:\s*true/i',
                '~(?<![A-Za-z0-9_-])/unpublished(?:[/?#)"\'\s]|$)~i',
            ],
        ];
    }
}
