<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Settings;

use Capell\Core\Contracts\SettingsContract;
use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Capell\FrontendOptimizer\Filament\Settings\FrontendOptimizerSettingsSchema;
use Spatie\LaravelSettings\Settings;

final class FrontendOptimizerSettings extends Settings implements SettingsContract
{
    public bool $enable_critical_css = true;

    public bool $automatic_generation = true;

    public string $profile_scope = OptimizationScope::Layout->value;

    /** @var array<int|string, string> */
    public array $viewports = [
        '390x844',
        '1440x900',
    ];

    public float $fold_multiplier = 1.0;

    public int $extra_fold_pixels = 0;

    public string $playwright_wait_strategy = 'networkidle';

    public int $playwright_timeout = 120;

    public int $max_inline_css_bytes = 20000;

    public bool $debug_query_support = true;

    public static function group(): string
    {
        return 'frontend_optimizer';
    }

    public static function schema(): string
    {
        return FrontendOptimizerSettingsSchema::class;
    }
}
