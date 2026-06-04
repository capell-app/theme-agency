<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Listeners;

use Capell\Core\Models\Blueprint;
use Capell\Frontend\Events\FrontendContextResolved;
use Illuminate\Database\Eloquent\Model;

final class CaptureCriticalCssPageTypeOptOut
{
    public function handle(FrontendContextResolved $event): void
    {
        $page = $event->context->page();

        if (! $page instanceof Model) {
            return;
        }

        if (! method_exists($page, 'blueprint')) {
            return;
        }

        $page->loadMissing('blueprint');

        $blueprint = $page->getRelation('blueprint');

        if (! $blueprint instanceof Blueprint) {
            return;
        }

        $event->context->setFrontendData(
            'frontend_optimizer.disable_critical_css',
            $blueprint->getMeta('frontend_optimizer.disable_critical_css') === true,
        );
    }
}
