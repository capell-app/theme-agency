<?php

declare(strict_types=1);

use Capell\CampaignStudio\Actions\InstallCampaignLayoutsAction;
use Capell\Core\Models\Layout;
use Capell\LayoutBuilder\Models\Widget;

it('installs campaign layouts with layout-builder compatible widget references', function (): void {
    $result = InstallCampaignLayoutsAction::run();

    $layout = Layout::query()->where('key', 'campaign-lead-generation')->firstOrFail();
    $containers = $layout->getAttribute('containers');
    $widgets = $layout->widgets;

    expect($result)->toBe(['created' => 3, 'updated' => 0, 'skipped' => 0])
        ->and($containers)->toHaveKeys(['hero', 'proof', 'form'])
        ->and($containers['hero']['widgets'][0])->toMatchArray([
            'widget_key' => 'campaign-lead-generation-campaign-hero',
            'occurrence' => 1,
        ])
        ->and($widgets)->toContain('campaign-lead-generation-campaign-hero')
        ->and(Widget::query()->where('key', 'campaign-lead-generation-campaign-hero')->exists())->toBeTrue()
        ->and(Widget::query()->where('key', 'campaign-lead-generation-campaign-cta-widget')->exists())->toBeTrue()
        ->and(Widget::query()->where('key', 'campaign-lead-generation-campaign-lead-form')->exists())->toBeTrue();
});

it('skips existing campaign layouts unless forced', function (): void {
    InstallCampaignLayoutsAction::run();

    expect(InstallCampaignLayoutsAction::run())->toBe(['created' => 0, 'updated' => 0, 'skipped' => 3])
        ->and(InstallCampaignLayoutsAction::run(force: true))->toBe(['created' => 0, 'updated' => 3, 'skipped' => 0]);
});
