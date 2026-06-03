<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Filament\Configurators\Types\FrontendOptimizerPageTypeConfigurator;
use Capell\FrontendOptimizer\Tests\Fixtures\FrontendOptimizerSchemaHarness;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

it('adds critical css controls to the page type frontend tab without replacing core rendering controls', function (): void {
    $method = new ReflectionMethod(FrontendOptimizerPageTypeConfigurator::class, 'frontendTab');
    $tab = $method->invoke(new FrontendOptimizerPageTypeConfigurator);

    expect($tab)->toBeInstanceOf(Tab::class);

    $preparedTab = Schema::make(new FrontendOptimizerSchemaHarness)
        ->components([$tab])
        ->getComponents()[0];
    $preparedTab = capell_test_instance($preparedTab, Tab::class);

    $componentNames = frontendOptimizerComponentNames($preparedTab->getChildComponents());

    expect($componentNames)->toContain(
        'component',
        'meta.cache_time',
        'meta.cache_frequency',
        'disable_visit_logs',
        'frontend_optimizer.disable_critical_css',
        'accessible',
        'listable',
        'sitemap',
        'with_next_prev',
        'layout_editable',
    );
});

/**
 * @param  array<int, mixed>  $components
 * @return array<int, string>
 */
function frontendOptimizerComponentNames(array $components): array
{
    /** @var array<int, string> $componentNames */
    $componentNames = collect(frontendOptimizerFlattenComponents($components))
        ->map(static function (mixed $component): ?string {
            if (! is_object($component) || ! method_exists($component, 'getName')) {
                return null;
            }

            $name = $component->getName();

            return is_string($name) ? $name : null;
        })
        ->filter(static fn (?string $name): bool => is_string($name))
        ->values()
        ->all();

    return $componentNames;
}

/**
 * @param  array<int, mixed>  $components
 * @return array<int, mixed>
 */
function frontendOptimizerFlattenComponents(array $components): array
{
    $flattened = [];

    foreach ($components as $component) {
        $flattened[] = $component;
        if (! is_object($component)) {
            continue;
        }

        if (! method_exists($component, 'getChildComponents')) {
            continue;
        }

        array_push($flattened, ...frontendOptimizerFlattenComponents($component->getChildComponents()));
    }

    return $flattened;
}
