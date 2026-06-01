<?php

declare(strict_types=1);

namespace Capell\Events\Support\RenderHooks;

use Capell\Events\Actions\BuildEventSchemaAction;
use Capell\Events\Actions\ResolvePublicEventSchemaOccurrenceAction;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Capell\Frontend\Data\RenderHookContext;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Support\Render\RenderHookRegistry;

class RegisterEventSchemaHooks
{
    /** @param RenderHookRegistry<RenderHookContext> $registry */
    public function __construct(private readonly RenderHookRegistry $registry) {}

    public function register(): void
    {
        $this->registry->register(
            RenderHookLocation::HeadClose,
            function (): string {
                $pageable = Frontend::page()->pageable ?? null;

                if (! $pageable instanceof Event) {
                    return '';
                }

                $occurrence = ResolvePublicEventSchemaOccurrenceAction::run($pageable);

                if (! $occurrence instanceof EventOccurrence) {
                    return '';
                }

                $schema = BuildEventSchemaAction::run($occurrence);

                return '<script type="application/ld+json">' . json_encode($schema, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . '</script>';
            },
        );
    }
}
