<?php

declare(strict_types=1);

use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Page;
use Capell\Frontend\Data\FrontendContext;
use Capell\Frontend\Events\FrontendContextResolved;
use Capell\FrontendOptimizer\Listeners\CaptureCriticalCssPageTypeOptOut;
use Illuminate\Support\Facades\DB;

it('captures critical css opt out from an already hydrated page blueprint without querying', function (): void {
    $blueprint = Blueprint::factory()->page()->meta([
        'frontend_optimizer' => ['disable_critical_css' => true],
    ])->create();
    $page = Page::factory()->type($blueprint)->create();
    $page->setRelation('blueprint', $blueprint);

    DB::enableQueryLog();
    DB::flushQueryLog();

    $context = frontendOptimizerContextForPage($page);

    (new CaptureCriticalCssPageTypeOptOut)->handle(new FrontendContextResolved($context));

    expect($context->getFrontendData('frontend_optimizer.disable_critical_css'))->toBeTrue()
        ->and(DB::getQueryLog())->toBe([]);
});

it('loads the page blueprint when needed and records a disabled opt out as false', function (): void {
    $blueprint = Blueprint::factory()->page()->meta([
        'frontend_optimizer' => ['disable_critical_css' => false],
    ])->create();
    $page = Page::factory()->type($blueprint)->create();

    $context = frontendOptimizerContextForPage($page);

    (new CaptureCriticalCssPageTypeOptOut)->handle(new FrontendContextResolved($context));

    expect($context->getFrontendData('frontend_optimizer.disable_critical_css'))->toBeFalse()
        ->and($page->relationLoaded('blueprint'))->toBeTrue();
});

it('leaves frontend data untouched when no page model is available', function (): void {
    $context = frontendOptimizerContextForPage(null);

    (new CaptureCriticalCssPageTypeOptOut)->handle(new FrontendContextResolved($context));

    expect($context->getFrontendData('frontend_optimizer.disable_critical_css'))->toBeNull();
});

function frontendOptimizerContextForPage(?Page $page): FrontendContext
{
    return new FrontendContext(
        site: null,
        language: null,
        page: $page,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    );
}
