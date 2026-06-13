<?php

declare(strict_types=1);

namespace Capell\Events\Support\RenderHooks;

use Capell\Events\Actions\BuildEventSchemaAction;
use Capell\Events\Actions\ResolvePublicEventSchemaOccurrenceAction;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Providers\EventsServiceProvider;
use Capell\Frontend\Actions\Performance\RecordExtensionRenderContributionAction;
use Capell\Frontend\Contracts\RenderHookExtensionInterface;
use Capell\Frontend\Data\RenderHookContext;
use Capell\Frontend\Facades\Frontend;

final class RegisterEventSchemaHooks implements RenderHookExtensionInterface
{
    public function render(RenderHookContext $context): string
    {
        $startedAt = microtime(true);

        $pageable = Frontend::page()->pageable ?? null;

        if (! $pageable instanceof Event) {
            return '';
        }

        $occurrence = ResolvePublicEventSchemaOccurrenceAction::run($pageable);

        if (! $occurrence instanceof EventOccurrence) {
            return '';
        }

        $schema = BuildEventSchemaAction::run($occurrence);

        $html = '<script type="application/ld+json">' . json_encode($schema, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . '</script>';

        RecordExtensionRenderContributionAction::run(
            packageName: EventsServiceProvider::$packageName,
            surface: 'frontend',
            contributionType: 'event-schema',
            contributionClass: self::class,
            elapsedMilliseconds: (microtime(true) - $startedAt) * 1000,
            frontendRenderBudgetMs: 20,
            cacheTags: ['events'],
            cacheable: false,
            sensitiveOutput: false,
            variesBy: ['page'],
        );

        return $html;
    }
}
