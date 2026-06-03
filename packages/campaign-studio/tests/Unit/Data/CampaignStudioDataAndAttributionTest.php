<?php

declare(strict_types=1);

use Capell\CampaignStudio\Actions\BuildConversionAttributionAction;
use Capell\CampaignStudio\Data\CampaignCtaActionData;
use Capell\CampaignStudio\Data\ConversionAttributionData;
use Capell\CampaignStudio\Data\UtmData;
use Capell\CampaignStudio\Enums\AttributionModel;
use Capell\CampaignStudio\Enums\CampaignStatus;
use Capell\CampaignStudio\Enums\CampaignWidgetComponentEnum;
use Capell\CampaignStudio\Enums\CampaignWidgetConfiguratorEnum;
use Capell\CampaignStudio\Enums\ConversionGoalType;
use Capell\CampaignStudio\Health\CampaignStudioHealthCheck;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

it('maps campaign CTA and attribution data across snake case boundaries', function (): void {
    $cta = CampaignCtaActionData::from([
        'label' => 'Book demo',
        'url' => '/demo',
        'style' => 'secondary',
        'goal_key' => 'book-demo',
        'utm' => [
            'source' => 'newsletter',
            'medium' => 'email',
            'campaign' => 'spring-launch',
            'term' => 'launch',
            'content' => 'hero',
        ],
    ]);
    $attribution = ConversionAttributionData::from([
        'landing_url' => 'https://capell.test/demo',
        'referrer_url' => 'https://search.test',
        'utm_source' => 'newsletter',
        'utm_medium' => 'email',
        'utm_campaign' => 'spring-launch',
        'utm_term' => 'launch',
        'utm_content' => 'hero',
        'event_name' => 'form_submit',
        'event_label' => 'Demo form',
        'event_location' => 'hero',
        'first_touch_campaign' => 'spring-launch',
        'last_touch_campaign' => 'retargeting',
    ]);

    expect($cta->utm)->toBeInstanceOf(UtmData::class)
        ->and($cta->goalKey)->toBe('book-demo')
        ->and($cta->toArray())->toMatchArray([
            'goal_key' => 'book-demo',
        ])
        ->and($cta->toArray()['utm'])->toMatchArray(['campaign' => 'spring-launch'])
        ->and($attribution->lastTouchCampaign)->toBe('retargeting')
        ->and($attribution->toArray())->toMatchArray([
            'landing_url' => 'https://capell.test/demo',
            'first_touch_campaign' => 'spring-launch',
        ]);
});

it('builds conversion attribution from visit attributes and event metadata', function (): void {
    $visit = campaignStudioCoverageModel([
        'landing_url' => 'https://capell.test/pricing',
        'referrer_url' => 'https://search.test',
        'utm_source' => 'google',
        'utm_medium' => 'cpc',
        'utm_campaign' => 'acquisition',
    ]);
    $event = campaignStudioCoverageModel([
        'event_name' => 'cta_click',
        'label' => 'Book demo',
        'location' => 'pricing-hero',
        'metadata' => [
            'utm_term' => 'cms',
            'utm_content' => 'hero-button',
            'utm_campaign' => 'retargeting',
        ],
    ]);

    $attribution = BuildConversionAttributionAction::run($visit, $event);

    expect($attribution)->toBeInstanceOf(ConversionAttributionData::class)
        ->and($attribution->landingUrl)->toBe('https://capell.test/pricing')
        ->and($attribution->utmTerm)->toBe('cms')
        ->and($attribution->eventLocation)->toBe('pricing-hero')
        ->and($attribution->firstTouchCampaign)->toBe('acquisition')
        ->and($attribution->lastTouchCampaign)->toBe('retargeting');
});

it('falls back to visit campaign when event metadata omits last touch campaign', function (): void {
    $visit = campaignStudioCoverageModel([
        'utm_campaign' => 'first-touch',
        'landing_url' => ' ',
    ]);
    $event = campaignStudioCoverageModel([
        'event_name' => '',
        'metadata' => (object) [
            'utm_content' => 'body-link',
        ],
    ]);

    $attribution = BuildConversionAttributionAction::run($visit, $event);

    expect($attribution->landingUrl)->toBeNull()
        ->and($attribution->eventName)->toBeNull()
        ->and($attribution->utmContent)->toBe('body-link')
        ->and($attribution->firstTouchCampaign)->toBe('first-touch')
        ->and($attribution->lastTouchCampaign)->toBe('first-touch');
});

it('defines campaign studio package metadata and enum labels', function (): void {
    expect(CampaignStudioHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(AttributionModel::FirstTouch->getLabel())->toBe('capell-campaign-studio::generic.attribution_models.first_touch')
        ->and(CampaignStatus::Scheduled->getLabel())->toBe('Scheduled')
        ->and(ConversionGoalType::CustomAction->getLabel())->toBe('capell-campaign-studio::generic.goal_types.custom_action')
        ->and(CampaignWidgetComponentEnum::CampaignHero->value)->toBe('capell-campaign-studio::components.widget.campaign-hero')
        ->and(CampaignWidgetConfiguratorEnum::CampaignLeadForm->value)->toContain('CampaignLeadFormWidgetConfigurator');
});

/**
 * @param  array<array-key, mixed>  $attributes
 */
function campaignStudioCoverageModel(array $attributes): Model
{
    return new class($attributes) extends Model
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /**
         * @param  array<array-key, mixed>  $attributes
         */
        public function __construct(array $attributes = [])
        {
            parent::__construct();

            $this->setRawAttributes($attributes, true);
        }
    };
}
