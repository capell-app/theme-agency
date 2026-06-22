<?php

declare(strict_types=1);

use Capell\AiCreator\Actions\RecommendAiCreatorPackagesAction;
use Capell\AiCreator\Enums\AiCreatorRecommendationLevel;

it('requires layout builder for complex page composition', function (): void {
    $recommendations = RecommendAiCreatorPackagesAction::run('Create a landing page with reusable sections, testimonials, and a contact form.');
    $packages = collect($recommendations)->keyBy('package');

    expect($packages->get('capell-app/layout-builder')->level)->toBe(AiCreatorRecommendationLevel::Required)
        ->and($packages)->toHaveKey('capell-app/content-sections')
        ->and($packages)->toHaveKey('capell-app/form-builder');
});

it('recommends growth and visibility packages for post launch measurement', function (): void {
    $recommendations = RecommendAiCreatorPackagesAction::run('Launch a campaign page with SEO metadata, analytics reporting, and traffic visibility.');
    $packages = collect($recommendations)->keyBy('package');

    expect($packages)->toHaveKey('capell-app/seo-suite')
        ->and($packages)->toHaveKey('capell-app/campaign-studio')
        ->and($packages)->toHaveKey('capell-app/insights')
        ->and($packages)->toHaveKey('capell-app/ga4-reports')
        ->and($packages)->toHaveKey('capell-app/site-monitor');
});

it('does not recommend blog just because the request mentions newsletter', function (): void {
    $recommendations = RecommendAiCreatorPackagesAction::run('Create a newsletter signup form for audience capture.');
    $packages = collect($recommendations)->keyBy('package');

    expect($packages)->toHaveKey('capell-app/newsletter')
        ->and($packages)->toHaveKey('capell-app/form-builder')
        ->and($packages)->not->toHaveKey('capell-app/blog');
});
