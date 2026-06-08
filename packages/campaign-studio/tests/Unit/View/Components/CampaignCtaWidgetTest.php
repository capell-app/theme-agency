<?php

declare(strict_types=1);

use Capell\CampaignStudio\Actions\BuildCampaignUrlAction;
use Capell\CampaignStudio\Data\UtmData;
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

it('decorates campaign hero button URLs with configured UTM metadata', function (): void {
    $widget = Widget::factory()->create([
        'meta' => [
            'primary_button_text' => 'Start trial',
            'primary_button_url' => '/signup?plan=pro#pricing',
            'secondary_button_text' => 'View demo',
            'secondary_button_url' => '/demo',
            'goal_key' => 'trial-started',
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'spring-launch',
            'utm_content' => 'hero',
        ],
    ]);

    $html = view('capell-campaign-studio::components.widget.campaign-hero', [
        'container' => [],
        'containerKey' => 'main',
        'containerWidth' => null,
        'loop' => (object) ['index' => 0, 'first' => true, 'last' => true],
        'widget' => $widget,
    ])->render();

    expect($html)
        ->toContain('href="/signup?plan=pro&amp;utm_source=newsletter&amp;utm_medium=email&amp;utm_campaign=spring-launch&amp;utm_content=hero#pricing"')
        ->toContain('href="/demo?utm_source=newsletter&amp;utm_medium=email&amp;utm_campaign=spring-launch&amp;utm_content=hero"')
        ->toContain('data-campaign-goal="trial-started"')
        ->not->toContain('campaign_group_id')
        ->not->toContain('data-campaign-id');
});

it('sanitizes campaign widget rich text before raw public rendering', function (): void {
    $payload = '<p class="lead" onclick="alert(1)">Safe <strong>copy</strong><script>alert(2)</script><a href="javascript:alert(3)">link</a></p>';
    $widget = Widget::factory()->create();
    $widget->setRelation('translation', (object) [
        'title' => 'Lead form',
        'content' => $payload,
    ]);

    $heroHtml = view('capell-campaign-studio::components.widget.campaign-hero', [
        'container' => [],
        'containerKey' => 'main',
        'containerWidth' => null,
        'content' => $payload,
        'loop' => (object) ['index' => 0, 'first' => true, 'last' => true],
        'widget' => $widget,
    ])->render();
    $leadFormHtml = view('capell-campaign-studio::components.widget.campaign-lead-form', [
        'container' => [],
        'containerKey' => 'main',
        'containerWidth' => null,
        'loop' => (object) ['index' => 0, 'first' => true, 'last' => true],
        'widget' => $widget,
    ])->render();

    expect($heroHtml)
        ->toContain('<strong>copy</strong>')
        ->toContain('class="lead"')
        ->not->toContain('<script')
        ->not->toContain('onclick')
        ->not->toContain('javascript:')
        ->and($leadFormHtml)
        ->toContain('<strong>copy</strong>')
        ->toContain('class="lead"')
        ->not->toContain('<script')
        ->not->toContain('onclick')
        ->not->toContain('javascript:');
});

it('neutralizes unsafe campaign CTA URLs before rendering', function (): void {
    $utm = new UtmData(source: 'newsletter');

    expect(BuildCampaignUrlAction::run('javascript:alert(1)', $utm))->toBe('#')
        ->and(BuildCampaignUrlAction::run("java\nscript:alert(1)", $utm))->toBe('#')
        ->and(BuildCampaignUrlAction::run('mailto:hello@example.test', $utm))->toBe('mailto:hello@example.test')
        ->and(BuildCampaignUrlAction::run('/demo', $utm))->toBe('/demo?utm_source=newsletter');

    $widget = Widget::factory()->create([
        'meta' => [
            'primary_button_text' => 'Unsafe',
            'primary_button_url' => 'javascript:alert(1)',
            'utm_source' => 'newsletter',
        ],
    ]);

    $html = view('capell-campaign-studio::components.widget.campaign-hero', [
        'container' => [],
        'containerKey' => 'main',
        'containerWidth' => null,
        'loop' => (object) ['index' => 0, 'first' => true, 'last' => true],
        'widget' => $widget,
    ])->render();

    expect($html)
        ->toContain('href="#"')
        ->not->toContain('javascript:');
});
