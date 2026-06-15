<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Models\EmailTemplateTheme;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(string $html, EmailTemplateTheme $theme, ?string $previewText = null)
 */
class ApplyEmailTemplateThemeAction
{
    use AsAction;

    public function handle(string $html, EmailTemplateTheme $theme, ?string $previewText = null): string
    {
        return view('capell-email-studio::emails.themes.default', [
            'html' => $html,
            'previewText' => $previewText,
            'theme' => $theme,
            'colors' => $this->colors($theme),
        ])->render();
    }

    /**
     * @return array<string, string>
     */
    private function colors(EmailTemplateTheme $theme): array
    {
        $colors = $theme->colors;

        if (! is_array($colors)) {
            $colors = [];
        }

        return [
            'body_bg_color' => $this->color($colors['body_bg_color'] ?? null, '#f8fafc'),
            'content_bg_color' => $this->color($colors['content_bg_color'] ?? null, '#ffffff'),
            'header_bg_color' => $this->color($colors['header_bg_color'] ?? null, '#111827'),
            'body_color' => $this->color($colors['body_color'] ?? null, '#111827'),
            'muted_color' => $this->color($colors['muted_color'] ?? null, '#64748b'),
            'border_color' => $this->color($colors['border_color'] ?? null, '#e2e8f0'),
            'button_bg_color' => $this->color($colors['button_bg_color'] ?? null, '#2563eb'),
            'button_color' => $this->color($colors['button_color'] ?? null, '#ffffff'),
        ];
    }

    private function color(mixed $value, string $fallback): string
    {
        if (! is_string($value)) {
            return $fallback;
        }

        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1 ? $value : $fallback;
    }
}
