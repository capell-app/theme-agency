<?php

declare(strict_types=1);

use Capell\CampaignStudio\Data\CampaignCtaActionData;
use Capell\CampaignStudio\Enums\ConversionGoalType;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignCtaWidget;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Support\RenderHooks\RegisterCampaignTrackerHook;
use Capell\CampaignStudio\View\Components\Widget\CampaignCtaWidget as CampaignCtaWidgetComponent;
use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Capell\Frontend\Actions\Performance\RecordExtensionRenderContributionAction;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\LayoutBuilder\Models\Widget;
use Illuminate\Contracts\View\View;

it('renders full public campaign output without authoring or numeric campaign markers', function (): void {
    $campaign = CampaignGroup::factory()->create(['slug' => 'spring-launch']);
    CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create([
            'key' => 'book-demo',
            'type' => ConversionGoalType::CtaClick,
        ]);
    $ctaWidget = CampaignCtaWidget::factory()
        ->for($campaign, 'campaignGroup')
        ->create([
            'key' => 'hero-cta',
            'headline' => 'Book a demo',
            'actions' => [
                new CampaignCtaActionData(
                    label: 'Book now',
                    url: '/demo',
                    goalKey: 'book-demo',
                ),
            ],
        ]);
    $widget = Widget::factory()->create(['meta' => ['cta_widget_id' => $ctaWidget->getKey()]]);

    CampaignCtaWidgetComponent::hydrateWidgets(collect([$widget]));

    $widgetHtml = campaignWidgetHtml($widget);
    $trackerHtml = resolve(RenderHookRegistry::class)->renderAll(RenderHookLocation::BodyEnd);
    $html = '<!doctype html><html><head><title>Campaign</title></head><body><main>' . $widgetHtml . '</main>' . $trackerHtml . '</body></html>';

    AssertPublicHtmlContainsNoAuthoringSurfaceAction::run(response($html, 200, ['Content-Type' => 'text/html']));

    expect($html)
        ->toContain('data-campaign="spring-launch"')
        ->toContain('data-campaign-cta="hero-cta"')
        ->toContain('data-campaign-goal="book-demo"')
        ->toContain('data-campaign-tracker')
        ->toContain('/capell/campaigns/conversions')
        ->not->toContain('campaign_group_id')
        ->not->toContain('campaign_cta_widget_id')
        ->not->toContain('data-campaign-id')
        ->not->toContain('field_path')
        ->not->toContain('model_id')
        ->not->toContain('signed-editor')
        ->not->toContain('campaign_group_id="' . $campaign->getKey() . '"')
        ->not->toContain('campaign_cta_widget_id="' . $ctaWidget->getKey() . '"');
});

it('marks the campaign tracker as non-cacheable frontend output', function (): void {
    resolve(RecordExtensionRenderContributionAction::class)->clear();

    $registry = new RenderHookRegistry;
    (new RegisterCampaignTrackerHook($registry))->register();

    $html = $registry->renderAll(RenderHookLocation::BodyEnd);
    $contribution = collect(resolve(RecordExtensionRenderContributionAction::class)->recorded())
        ->first(fn (mixed $record): bool => $record->contributionClass === RegisterCampaignTrackerHook::class);

    expect($html)
        ->toContain('data-campaign-tracker')
        ->toContain('/capell/campaigns/conversions')
        ->and($contribution?->cacheable)->toBeFalse()
        ->and($contribution?->sensitiveOutput)->toBeFalse()
        ->and($contribution?->variesBy)->toContain('utm_campaign', 'utm_content', 'utm_term');
});

function campaignWidgetHtml(Widget $widget): string
{
    $component = new CampaignCtaWidgetComponent(
        container: [],
        containerKey: 'main',
        widgetIndex: 0,
        loop: (object) ['index' => 0, 'first' => true, 'last' => true],
        widget: $widget,
    );

    $rendered = $component->render([
        'container' => [],
        'containerKey' => 'main',
        'containerWidth' => null,
        'loop' => (object) ['index' => 0, 'first' => true, 'last' => true],
        'widget' => $widget,
    ]);

    return match (true) {
        is_string($rendered) => $rendered,
        $rendered instanceof View => $rendered->render(),
        default => throw new RuntimeException('Campaign CTA widget did not return renderable output.'),
    };
}
