<?php

declare(strict_types=1);

use Capell\ThemeStudio\EditorialCrm\EditorialCrmThemeServiceProvider;
use Capell\ThemeStudio\EditorialCrm\Health\ThemeEditorialCrmHealthCheck;

it('defines the editorial-crm renderer contract', function (): void {
    $definition = EditorialCrmThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-editorial-crm')
        ->and($definition->key)->toBe(EditorialCrmThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Editorial CRM')
        ->and($definition->description)->toContain('Editorial CRM theme')
        ->and($definition->tags)->toContain('CRM', 'SaaS', 'Operations', 'Automation', 'Dashboards')
        ->and($definition->bestFit)->toContain('CRM platforms', 'Revenue operations products', 'Customer data platforms')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'product-dashboard',
            'data-model',
            'workflow-automation',
            'collaboration',
            'integrations-reporting',
            'customer-stories',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('editorial-crm')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeEditorialCrmHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
