<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Models\EmailTemplateTheme;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EmailTemplateTheme run(?int $siteId = null, string $siteScopeKey = 'global', string $key = 'default', string $name = 'Default Email Theme', array<string, string> $colors = [])
 */
class CreateDefaultEmailTemplateThemeAction
{
    use AsAction;

    /**
     * @param  array<string, string>  $colors
     */
    public function handle(
        ?int $siteId = null,
        string $siteScopeKey = 'global',
        string $key = 'default',
        string $name = 'Default Email Theme',
        array $colors = [],
    ): EmailTemplateTheme {
        return DB::transaction(
            function () use ($siteId, $siteScopeKey, $key, $name, $colors): EmailTemplateTheme {
                EmailTemplateTheme::query()
                    ->where('site_scope_key', $siteScopeKey)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);

                /** @var EmailTemplateTheme $theme */
                $theme = EmailTemplateTheme::query()->updateOrCreate(
                    [
                        'site_scope_key' => $siteScopeKey,
                        'key' => $key,
                    ],
                    [
                        'site_id' => $siteId,
                        'name' => $name,
                        'is_default' => true,
                        'colors' => [
                            ...$this->defaultColors(),
                            ...$colors,
                        ],
                    ],
                );

                return $theme;
            },
            attempts: 3,
        );
    }

    /**
     * @return array<string, string>
     */
    private function defaultColors(): array
    {
        return [
            'body_bg_color' => '#f8fafc',
            'content_bg_color' => '#ffffff',
            'header_bg_color' => '#111827',
            'body_color' => '#111827',
            'muted_color' => '#64748b',
            'border_color' => '#e2e8f0',
            'button_bg_color' => '#2563eb',
            'button_color' => '#ffffff',
        ];
    }
}
