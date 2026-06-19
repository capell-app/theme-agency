<?php

declare(strict_types=1);

use Capell\ThemeStudio\ProductCompanyEditorial\Health\ThemeProductCompanyEditorialHealthCheck;
use Capell\ThemeStudio\ProductCompanyEditorial\ProductCompanyEditorialThemeServiceProvider;

it('defines the product-company-editorial renderer contract', function (): void {
    $definition = ProductCompanyEditorialThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-product-company-editorial')
        ->and($definition->key)->toBe(ProductCompanyEditorialThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Product Company Editorial')
        ->and($definition->description)->toContain('Product-company editorial theme')
        ->and($definition->tags)->toContain('Editorial', 'Product Updates', 'Design', 'Engineering', 'Templates')
        ->and($definition->bestFit)->toContain('Product companies', 'Design tools', 'Creative software teams')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'featured-posts',
            'topic-areas',
            'story-cards',
            'product-updates',
            'template-stories',
            'author-callouts',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('product-company-editorial')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeProductCompanyEditorialHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
