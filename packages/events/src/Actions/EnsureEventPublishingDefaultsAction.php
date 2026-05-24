<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Admin\Filament\Configurators\Blueprints\PageBlueprintConfigurator;
use Capell\Admin\Filament\Configurators\Pages\ResultsPageConfigurator;
use Capell\Core\Actions\GetOrCreateResultsLayoutAction;
use Capell\Core\Enums\BlueprintSubjectEnum;
use Capell\Core\Enums\UrlParamTypeEnum;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Layout;
use Capell\Core\Support\Creator\LayoutCreator;
use Capell\Events\Enums\LivewireComponentEnum;
use Filament\Support\Icons\Heroicon;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static void run()
 */
class EnsureEventPublishingDefaultsAction
{
    use AsAction;

    public function handle(): void
    {
        $this->eventPageType();
        $this->eventsListingPageType();
        $this->defaultEventLayout();
        $this->eventsListingLayout();
    }

    public function eventPageType(): Blueprint
    {
        return Blueprint::query()->firstOrCreate([
            'key' => 'event',
            'type' => BlueprintSubjectEnum::Page,
        ], [
            'name' => __('capell-events::generic.event'),
            'group' => 'Event',
            'admin' => [
                'type_configurator' => PageBlueprintConfigurator::getKey(),
                'icon' => 'heroicon-' . Heroicon::OutlinedCalendarDays->value,
                'required_fields' => ['title'],
                'resource' => 'event',
            ],
            'meta' => [
                'schema' => ['type' => 'Event'],
                'sitemap' => true,
                'url_params' => ['date' => UrlParamTypeEnum::String->value],
                'with_date' => true,
                'with_image' => true,
            ],
        ]);
    }

    public function eventsListingPageType(): Blueprint
    {
        $blueprint = Blueprint::query()->firstOrCreate([
            'key' => 'events',
            'type' => BlueprintSubjectEnum::Page,
        ], [
            'name' => __('capell-events::generic.events'),
            'group' => 'Event',
            'admin' => [
                'type_configurator' => PageBlueprintConfigurator::getKey(),
                'configurator' => ResultsPageConfigurator::getKey(),
                'icon' => 'heroicon-' . Heroicon::OutlinedCalendarDays->value,
                'required_fields' => ['title'],
            ],
            'meta' => [
                'component' => LivewireComponentEnum::EventsCalendarPage->value,
                'livewire' => true,
                'limit' => 24,
                'pagination' => true,
                'sitemap' => true,
            ],
        ]);

        $blueprint->forceFill([
            'component' => LivewireComponentEnum::EventsCalendarPage->value,
            'is_livewire' => true,
            'meta' => [
                ...($blueprint->meta ?? []),
                'component' => LivewireComponentEnum::EventsCalendarPage->value,
                'livewire' => true,
                'limit' => 24,
                'pagination' => true,
                'sitemap' => true,
            ],
        ])->save();

        return $blueprint;
    }

    public function defaultEventLayout(): Layout
    {
        return Layout::query()->firstWhere('key', 'event')
            ?? resolve(LayoutCreator::class)->create('event');
    }

    public function eventsListingLayout(): Layout
    {
        return GetOrCreateResultsLayoutAction::run();
    }
}
