<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Tests\Integration;

use Capell\PublishingStudio\Checks\AccessibilityCheck;
use Capell\PublishingStudio\Checks\BrokenLinkCheck;
use Capell\PublishingStudio\Checks\MissingAltTextCheck;
use Capell\PublishingStudio\Checks\PublishCheck;
use Capell\PublishingStudio\Checks\PublishCheckPipeline;
use Capell\PublishingStudio\Checks\SeoMetaCheck;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Tests\Fixtures\Autoload\FixtureFailingCheck;
use Capell\PublishingStudio\Tests\Fixtures\Autoload\FixturePassingCheck;
use Illuminate\Support\Facades\Config;

it('merges default publish checks that resolve through the container', function (): void {
    $checkClasses = Config::array('capell.publishing-studio.publish_checks');

    expect($checkClasses)
        ->not->toBeEmpty()
        ->toContain(AccessibilityCheck::class)
        ->toContain(BrokenLinkCheck::class)
        ->toContain(MissingAltTextCheck::class)
        ->toContain(SeoMetaCheck::class);

    foreach ($checkClasses as $checkClass) {
        expect(is_string($checkClass))->toBeTrue()
            ->and(is_subclass_of($checkClass, PublishCheck::class))->toBeTrue()
            ->and(resolve($checkClass))->toBeInstanceOf(PublishCheck::class);
    }

    $results = resolve(PublishCheckPipeline::class)->run(Workspace::factory()->create());

    expect($results)->not->toBeEmpty();
});

it('runs each configured check and returns their results', function (): void {
    Config::set('capell.publishing-studio.publish_checks', [
        FixturePassingCheck::class,
        FixtureFailingCheck::class,
    ]);

    $workspace = Workspace::factory()->create();

    $results = resolve(PublishCheckPipeline::class)->run($workspace);

    expect($results)->toHaveCount(2)
        ->and($results[0]->identifier)->toBe('fixture-pass')
        ->and($results[1]->identifier)->toBe('fixture-fail');
});

it('detects blocking errors when any result is Error severity with findings', function (): void {
    Config::set('capell.publishing-studio.publish_checks', [
        FixtureFailingCheck::class,
    ]);

    $pipeline = resolve(PublishCheckPipeline::class);
    $results = $pipeline->run(Workspace::factory()->create());

    expect($pipeline->hasBlockingErrors($results))->toBeTrue();
});

it('clean Error-severity results are not blocking', function (): void {
    Config::set('capell.publishing-studio.publish_checks', [
        FixturePassingCheck::class,
    ]);

    $pipeline = resolve(PublishCheckPipeline::class);
    $results = $pipeline->run(Workspace::factory()->create());

    expect($pipeline->hasBlockingErrors($results))->toBeFalse();
});
