<?php

declare(strict_types=1);

use Capell\CampaignStudio\Models\CampaignCtaWidget;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\View\Components\Widget\CampaignCtaWidget as CampaignCtaWidgetComponent;
use Capell\Core\Models\Site;
use Capell\Frontend\Support\State\FrontendState;
use Capell\LayoutBuilder\Models\Widget;
use Illuminate\Contracts\View\View;

it('hydrates CTA widgets for campaign widgets in one batch', function (): void {
    $firstCtaWidget = CampaignCtaWidget::factory()->create(['headline' => 'First CTA']);
    $secondCtaWidget = CampaignCtaWidget::factory()->create(['headline' => 'Second CTA']);
    $firstWidget = Widget::factory()->create(['meta' => ['cta_widget_id' => $firstCtaWidget->getKey()]]);
    $secondWidget = Widget::factory()->create(['meta' => ['cta_widget_id' => $secondCtaWidget->getKey()]]);

    CampaignCtaWidgetComponent::hydrateWidgets(collect([$firstWidget, $secondWidget]));

    expect($firstWidget->relationLoaded('campaignCtaWidget'))->toBeTrue()
        ->and($firstWidget->getRelation('campaignCtaWidget')->headline)->toBe('First CTA')
        ->and($secondWidget->relationLoaded('campaignCtaWidget'))->toBeTrue()
        ->and($secondWidget->getRelation('campaignCtaWidget')->headline)->toBe('Second CTA');
});

it('hydrates only active CTA widgets for the current frontend site', function (): void {
    $site = Site::factory()->create();
    $otherSite = Site::factory()->create();
    resolve(FrontendState::class)->withSite($site);

    $siteCtaWidget = CampaignCtaWidget::factory()->create([
        'site_id' => $site->getKey(),
        'headline' => 'Site CTA',
    ]);
    $hiddenCtaWidget = CampaignCtaWidget::factory()->create([
        'site_id' => $otherSite->getKey(),
        'headline' => 'Hidden CTA',
    ]);
    $inactiveCtaWidget = CampaignCtaWidget::factory()->create([
        'site_id' => $site->getKey(),
        'is_active' => false,
        'headline' => 'Inactive CTA',
    ]);
    $siteWidget = Widget::factory()->create(['meta' => ['cta_widget_id' => $siteCtaWidget->getKey()]]);
    $hiddenWidget = Widget::factory()->create(['meta' => ['cta_widget_id' => $hiddenCtaWidget->getKey()]]);
    $inactiveWidget = Widget::factory()->create(['meta' => ['cta_widget_id' => $inactiveCtaWidget->getKey()]]);

    CampaignCtaWidgetComponent::hydrateWidgets(collect([$siteWidget, $hiddenWidget, $inactiveWidget]));

    expect($siteWidget->getRelation('campaignCtaWidget')?->headline)->toBe('Site CTA')
        ->and($hiddenWidget->getRelation('campaignCtaWidget'))->toBeNull()
        ->and($inactiveWidget->getRelation('campaignCtaWidget'))->toBeNull();
});

it('renders public CTA tracking attributes without leaking numeric campaign ids', function (): void {
    $campaignGroup = CampaignGroup::factory()->create(['slug' => 'spring-launch']);
    $ctaWidget = CampaignCtaWidget::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'key' => 'primary-cta',
            'headline' => 'Start today',
        ]);
    $widget = Widget::factory()->create(['meta' => ['cta_widget_id' => $ctaWidget->getKey()]]);

    CampaignCtaWidgetComponent::hydrateWidgets(collect([$widget]));

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
    $html = match (true) {
        is_string($rendered) => $rendered,
        $rendered instanceof View => $rendered->render(),
        default => throw new RuntimeException('Campaign CTA widget did not return renderable output.'),
    };

    expect($html)
        ->toContain('data-campaign="spring-launch"')
        ->toContain('data-campaign-cta="primary-cta"')
        ->not->toContain('data-campaign-id')
        ->not->toContain('campaign_group_id');
});
