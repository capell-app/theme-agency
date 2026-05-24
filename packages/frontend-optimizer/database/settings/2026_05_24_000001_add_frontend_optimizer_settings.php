<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->addIfMissing('frontend_optimizer.enable_critical_css', true);
        $this->addIfMissing('frontend_optimizer.automatic_generation', true);
        $this->addIfMissing('frontend_optimizer.profile_scope', OptimizationScope::Layout->value);
        $this->addIfMissing('frontend_optimizer.viewports', [
            '390x844',
            '1440x900',
        ]);
        $this->addIfMissing('frontend_optimizer.fold_multiplier', 1.0);
        $this->addIfMissing('frontend_optimizer.extra_fold_pixels', 0);
        $this->addIfMissing('frontend_optimizer.playwright_wait_strategy', 'networkidle');
        $this->addIfMissing('frontend_optimizer.playwright_timeout', 120);
        $this->addIfMissing('frontend_optimizer.max_inline_css_bytes', 20000);
        $this->addIfMissing('frontend_optimizer.debug_query_support', true);
    }

    private function addIfMissing(string $key, mixed $value): void
    {
        if (! $this->migrator->exists($key)) {
            $this->migrator->add($key, $value);
        }
    }
};
