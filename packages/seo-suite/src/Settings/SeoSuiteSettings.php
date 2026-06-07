<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Settings;

use Capell\Core\Contracts\SettingsContract;
use Capell\Core\Contracts\SettingsSchemaContract;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Enums\SeoCheckModeEnum;
use Capell\SeoSuite\Enums\SeoQualityGatePresetEnum;
use Capell\SeoSuite\Filament\Settings\SeoSettingsSchema;
use Spatie\LaravelSettings\Settings;

class SeoSuiteSettings extends Settings implements SettingsContract, SettingsSchemaContract
{
    public bool $seo_audit_enabled = true;

    public bool $seo_check_meta_description = true;

    public bool $seo_check_meta_title = true;

    public bool $seo_check_duplicate_title = true;

    public bool $seo_authoring_strict_meta_enabled = false;

    public int $seo_meta_description_min_length = 65;

    public int $seo_meta_description_max_length = 200;

    public bool $seo_authoring_require_title_in_description = false;

    public string $seo_quality_gate_preset = 'standard';

    public string $seo_quality_gate_meta_description_mode = 'blocker';

    public string $seo_quality_gate_meta_title_mode = 'warning';

    public string $seo_quality_gate_social_image_mode = 'warning';

    public string $seo_quality_gate_canonical_mode = 'warning';

    public string $seo_quality_gate_robots_mode = 'warning';

    public string $seo_quality_gate_schema_mode = 'warning';

    public bool $ai_discovery_audit_enabled = true;

    public bool $ai_discovery_default_enabled = true;

    public string $ai_discovery_crawler_policy = 'search_visible_training_restricted';

    public bool $pagespeed_audit_enabled = true;

    public bool $pagespeed_weekly_digest_enabled = true;

    public int $pagespeed_scheduled_limit = 50;

    public int $pagespeed_stale_after_days = 14;

    public static function group(): string
    {
        return 'seo_suite';
    }

    public static function schema(): string
    {
        return SeoSettingsSchema::class;
    }

    public function metaDescriptionMinimumLength(): int
    {
        return max(1, $this->seo_meta_description_min_length);
    }

    public function metaDescriptionMaximumLength(): int
    {
        return max($this->metaDescriptionMinimumLength(), $this->seo_meta_description_max_length);
    }

    public function qualityGatePreset(): SeoQualityGatePresetEnum
    {
        return SeoQualityGatePresetEnum::tryFrom($this->seo_quality_gate_preset) ?? SeoQualityGatePresetEnum::Standard;
    }

    public function qualityGateMode(SeoCheckKeyEnum $key): SeoCheckModeEnum
    {
        $value = match ($key) {
            SeoCheckKeyEnum::MetaDescription => $this->seo_quality_gate_meta_description_mode,
            SeoCheckKeyEnum::MetaTitle => $this->seo_quality_gate_meta_title_mode,
            SeoCheckKeyEnum::SocialImage => $this->seo_quality_gate_social_image_mode,
            SeoCheckKeyEnum::Canonical => $this->seo_quality_gate_canonical_mode,
            SeoCheckKeyEnum::Robots => $this->seo_quality_gate_robots_mode,
            SeoCheckKeyEnum::Schema => $this->seo_quality_gate_schema_mode,
            default => SeoCheckModeEnum::Ignored->value,
        };

        return SeoCheckModeEnum::tryFrom($value) ?? SeoCheckModeEnum::Ignored;
    }
}
