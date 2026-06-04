<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Capell\FrontendOptimizer\Settings\FrontendOptimizerSettings;
use Capell\FrontendOptimizer\Support\CriticalCssSettings;

it('normalizes critical css settings from persisted settings values', function (): void {
    app()->instance(FrontendOptimizerSettings::class, frontendOptimizerSettings([
        'enable_critical_css' => true,
        'automatic_generation' => true,
        'profile_scope' => OptimizationScope::PageUrl->value,
        'viewports' => [
            '390x844',
            ['width' => 1440, 'height' => 900],
            ['width' => '768', 'height' => '1024'],
            ['width' => 0, 'height' => 900],
            'invalid',
        ],
        'fold_multiplier' => 0.0,
        'extra_fold_pixels' => -20,
        'playwright_wait_strategy' => 'paint',
        'playwright_timeout' => 0,
        'max_inline_css_bytes' => 0,
    ]));

    $settings = new CriticalCssSettings;

    expect($settings->enabled())->toBeTrue()
        ->and($settings->automaticGenerationEnabled())->toBeTrue()
        ->and($settings->scope())->toBe(OptimizationScope::PageUrl)
        ->and($settings->viewports())->toBe([
            ['width' => 390, 'height' => 844],
            ['width' => 1440, 'height' => 900],
            ['width' => 768, 'height' => 1024],
        ])
        ->and($settings->foldMultiplier())->toBe(0.1)
        ->and($settings->extraFoldPixels())->toBe(0)
        ->and($settings->playwrightWaitStrategy())->toBe('networkidle')
        ->and($settings->playwrightTimeout())->toBe(1)
        ->and($settings->maxInlineCssBytes())->toBe(1);
});

it('falls back to config values when settings cannot be resolved', function (): void {
    app()->bind(FrontendOptimizerSettings::class, static function (): never {
        throw new RuntimeException('Settings are unavailable.');
    });

    config()->set('capell-frontend-optimizer.enabled', true);
    config()->set('capell-frontend-optimizer.scope', OptimizationScope::RenderProfile->value);
    config()->set('capell-frontend-optimizer.playwright.timeout', 45);
    config()->set('capell-frontend-optimizer.playwright.viewports', [
        ['width' => 1024, 'height' => 768],
    ]);

    $settings = new CriticalCssSettings;

    expect($settings->enabled())->toBeTrue()
        ->and($settings->scope())->toBe(OptimizationScope::RenderProfile)
        ->and($settings->viewports())->toBe([
            ['width' => 1024, 'height' => 768],
        ])
        ->and($settings->playwrightTimeout())->toBe(45);
});

it('detects page type opt out metadata across supported context shapes', function (array $context): void {
    $settings = new CriticalCssSettings;

    expect($settings->pageTypeDisablesCriticalCss($context))->toBeTrue()
        ->and($settings->profileDisablesCriticalCss(['context' => $context]))->toBeTrue();
})->with([
    'frontend context meta' => [[
        'meta' => [
            'frontend_optimizer' => ['disable_critical_css' => true],
        ],
    ]],
    'page type meta' => [[
        'page_type' => [
            'meta' => [
                'frontend_optimizer' => ['disable_critical_css' => true],
            ],
        ],
    ]],
    'legacy page type meta' => [[
        'page_type_meta' => [
            'frontend_optimizer' => ['disable_critical_css' => true],
        ],
    ]],
]);

it('keeps critical css enabled for malformed or false opt out metadata', function (): void {
    $settings = new CriticalCssSettings;

    expect($settings->pageTypeDisablesCriticalCss([
        'page_type_meta' => [
            'frontend_optimizer' => ['disable_critical_css' => 'true'],
        ],
    ]))->toBeFalse()
        ->and($settings->profileDisablesCriticalCss(null))->toBeFalse()
        ->and($settings->profileDisablesCriticalCss(['context' => 'invalid']))->toBeFalse();
});

/** @param array<string, mixed> $overrides */
function frontendOptimizerSettings(array $overrides = []): FrontendOptimizerSettings
{
    $reflection = new ReflectionClass(FrontendOptimizerSettings::class);
    $settings = $reflection->newInstanceWithoutConstructor();
    assert($settings instanceof FrontendOptimizerSettings);

    foreach (array_merge([
        'enable_critical_css' => true,
        'automatic_generation' => true,
        'profile_scope' => OptimizationScope::Layout->value,
        'viewports' => ['390x844', '1440x900'],
        'fold_multiplier' => 1.0,
        'extra_fold_pixels' => 0,
        'playwright_wait_strategy' => 'networkidle',
        'playwright_timeout' => 120,
        'max_inline_css_bytes' => 20000,
    ], $overrides) as $key => $value) {
        $settings->{$key} = $value;
    }

    return $settings;
}
