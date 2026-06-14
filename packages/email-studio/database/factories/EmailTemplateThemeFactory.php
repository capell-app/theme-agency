<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Database\Factories;

use Capell\EmailStudio\Models\EmailTemplateTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailTemplateTheme>
 */
class EmailTemplateThemeFactory extends Factory
{
    protected $model = EmailTemplateTheme::class;

    public function definition(): array
    {
        return [
            'site_id' => null,
            'site_scope_key' => 'global',
            'key' => 'theme-' . $this->faker->unique()->slug(2),
            'name' => $this->faker->words(2, true),
            'is_default' => false,
            'logo_path' => null,
            'logo_url' => null,
            'screenshot_path' => null,
            'colors' => [
                'header_bg_color' => '#111827',
                'body_bg_color' => '#f8fafc',
                'content_bg_color' => '#ffffff',
                'button_bg_color' => '#2563eb',
                'button_color' => '#ffffff',
                'body_color' => '#111827',
            ],
        ];
    }
}
