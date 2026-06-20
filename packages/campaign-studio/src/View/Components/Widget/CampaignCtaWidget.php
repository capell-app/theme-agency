<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\View\Components\Widget;

use Capell\CampaignStudio\Models\CampaignCtaWidget as CampaignCtaWidgetModel;
use Capell\Core\Models\Site;
use Capell\Frontend\Facades\Frontend;
use Capell\LayoutBuilder\Models\Widget;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use stdClass;

class CampaignCtaWidget extends Component
{
    public ?CampaignCtaWidgetModel $ctaWidget = null;

    /**
     * @param  array<array-key, mixed>  $widgetData
     * @param  array<array-key, mixed>  $container
     */
    public function __construct(
        public array $container,
        public string $containerKey,
        public int $widgetIndex,
        public stdClass $loop,
        public Widget $widget,
        public array $widgetData = [],
        public ?int $containerIndex = null,
        public ?int $containerWidth = null,
        public ?int $containerColspan = null,
        public mixed $pageSlot = null,
        public int $occurrence = 1,
    ) {
        $this->ctaWidget = $this->widget->relationLoaded('campaignCtaWidget')
            ? $this->widget->getRelation('campaignCtaWidget')
            : null;
    }

    /**
     * @param  Collection<int, Widget>  $widgets
     */
    public static function hydrateWidgets(Collection $widgets): void
    {
        $ctaWidgetIds = $widgets
            ->map(fn (Widget $widget): mixed => $widget->getMeta('cta_layout_widget_id'))
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        if ($ctaWidgetIds->isEmpty()) {
            return;
        }

        $site = Frontend::site();

        $ctaWidgets = CampaignCtaWidgetModel::query()
            ->with('campaignGroup:id,slug,site_id')
            ->whereKey($ctaWidgetIds->all())
            ->where('is_active', true);

        if ($site instanceof Site) {
            $ctaWidgets->where(function (Builder $query) use ($site): void {
                $query->whereNull('site_id')
                    ->orWhere('site_id', $site->getKey());
            });
        }

        $ctaWidgets = $ctaWidgets
            ->get()
            ->keyBy(fn (CampaignCtaWidgetModel $ctaWidget): int => (int) $ctaWidget->getKey());

        $widgets->each(function (Widget $widget) use ($ctaWidgets): void {
            $ctaWidgetId = $widget->getMeta('cta_layout_widget_id');

            $widget->setRelation(
                'campaignCtaWidget',
                is_numeric($ctaWidgetId) ? $ctaWidgets->get((int) $ctaWidgetId) : null,
            );
        });
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function render(array $data = []): View|string|Closure
    {
        return view('capell-campaign-studio::components.widget.campaign-cta-widget', [
            ...$data,
            'ctaWidget' => $this->ctaWidget,
        ]);
    }
}
