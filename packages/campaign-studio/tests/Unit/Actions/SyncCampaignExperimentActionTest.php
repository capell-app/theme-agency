<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Tests\Unit\Actions;

use Capell\CampaignStudio\Actions\BuildCampaignExperimentResultsAction;
use Capell\CampaignStudio\Actions\SyncCampaignExperimentAction;
use Capell\CampaignStudio\Enums\CampaignStatus;
use Capell\CampaignStudio\Enums\ConversionGoalType;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\CampaignStudio\Tests\CampaignStudioExperimentsTestCase;
use Capell\Experiments\Enums\AllocationStrategy;
use Capell\Experiments\Enums\AudienceOperator;
use Capell\Experiments\Enums\AudienceRuleType;
use Capell\Experiments\Enums\ExperimentGoalType;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Enums\ExperimentSubjectType;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentAllocation;
use Capell\Experiments\Models\ExperimentGoalEvent;
use Carbon\CarbonImmutable;

final class SyncCampaignExperimentActionTest extends CampaignStudioExperimentsTestCase
{
    public function test_it_syncs_a_campaign_group_into_a_campaign_scoped_experiment(): void
    {
        $campaign = CampaignGroup::factory()->create([
            'name' => 'Spring acquisition',
            'slug' => 'spring-acquisition',
            'status' => CampaignStatus::Active,
            'utm_campaign' => 'spring-acquisition',
        ]);

        CampaignLandingPage::factory()->for($campaign)->create([
            'headline' => 'Control hero',
            'utm_content' => 'control',
            'is_primary' => true,
        ]);
        CampaignLandingPage::factory()->for($campaign)->create([
            'headline' => 'Benefit hero',
            'utm_content' => 'benefit',
            'is_primary' => false,
        ]);

        CampaignConversionGoal::factory()->for($campaign)->create([
            'name' => 'Book demo',
            'key' => 'book-demo',
            'type' => ConversionGoalType::FormSubmission,
            'target' => 'demo-form',
            'is_primary' => true,
        ]);
        CampaignConversionGoal::factory()->for($campaign)->create([
            'name' => 'Hero CTA',
            'key' => 'hero-cta',
            'type' => ConversionGoalType::CtaClick,
            'target' => 'primary-cta',
        ]);

        $experiment = SyncCampaignExperimentAction::run($campaign);

        $this->assertInstanceOf(Experiment::class, $experiment);
        $this->assertSame('Spring acquisition experiment', $experiment->name);
        $this->assertSame('campaign-' . $campaign->getKey() . '-spring-acquisition', $experiment->key);
        $this->assertSame(ExperimentStatus::Active, $experiment->status);
        $this->assertSame(ExperimentSubjectType::Campaign, $experiment->subject_type);
        $this->assertSame(CampaignGroup::class, $experiment->subject_class);
        $this->assertSame($campaign->getKey(), $experiment->subject_id);
        $this->assertCount(2, $experiment->variants);
        $this->assertCount(2, $experiment->goals);
        $this->assertCount(1, $experiment->audienceRules);

        $controlVariant = $experiment->variants()->where('key', 'control')->firstOrFail();
        $bookDemoGoal = $experiment->goals()->where('key', 'book-demo')->firstOrFail();
        $ctaGoal = $experiment->goals()->where('key', 'hero-cta')->firstOrFail();
        $audienceRule = $experiment->audienceRules()->firstOrFail();

        $this->assertTrue($controlVariant->is_control);
        $this->assertSame($campaign->getKey(), $controlVariant->payload['campaign_group_id']);
        $this->assertSame(ExperimentGoalType::FormSubmission, $bookDemoGoal->type);
        $this->assertTrue($bookDemoGoal->is_primary);
        $this->assertSame(ExperimentGoalType::Click, $ctaGoal->type);
        $this->assertSame(AudienceRuleType::Utm, $audienceRule->type);
        $this->assertSame('campaign', $audienceRule->key);
        $this->assertSame(AudienceOperator::Equals, $audienceRule->operator);
        $this->assertSame('spring-acquisition', $audienceRule->value);
    }

    public function test_it_updates_an_existing_campaign_experiment_without_creating_duplicates(): void
    {
        $campaign = CampaignGroup::factory()->create([
            'status' => CampaignStatus::Draft,
            'utm_campaign' => 'original-campaign',
        ]);
        CampaignLandingPage::factory()->for($campaign)->create([
            'headline' => 'Original',
            'utm_content' => 'original',
            'is_primary' => true,
        ]);

        $firstExperiment = SyncCampaignExperimentAction::run($campaign);

        $campaign->forceFill([
            'status' => CampaignStatus::Paused,
            'utm_campaign' => 'updated-campaign',
        ])->save();
        CampaignLandingPage::factory()->for($campaign)->create([
            'headline' => 'Updated',
            'utm_content' => 'updated',
        ]);

        $secondExperiment = SyncCampaignExperimentAction::run($campaign->refresh());

        $this->assertInstanceOf(Experiment::class, $firstExperiment);
        $this->assertInstanceOf(Experiment::class, $secondExperiment);
        $this->assertSame($firstExperiment->getKey(), $secondExperiment->getKey());
        $this->assertSame(1, Experiment::query()->count());
        $this->assertSame(ExperimentStatus::Paused, $secondExperiment->status);
        $this->assertCount(2, $secondExperiment->variants);
        $this->assertSame('updated-campaign', $secondExperiment->audienceRules()->where('key', 'campaign')->firstOrFail()->value);
    }

    public function test_it_builds_campaign_experiment_results_with_variant_lift(): void
    {
        $campaign = CampaignGroup::factory()->create([
            'status' => CampaignStatus::Active,
            'utm_campaign' => 'results-campaign',
        ]);
        CampaignLandingPage::factory()->for($campaign)->create([
            'headline' => 'Control',
            'utm_content' => 'control',
            'is_primary' => true,
        ]);
        CampaignLandingPage::factory()->for($campaign)->create([
            'headline' => 'Benefit',
            'utm_content' => 'benefit',
        ]);
        CampaignConversionGoal::factory()->for($campaign)->create([
            'name' => 'Book demo',
            'key' => 'book-demo',
            'type' => ConversionGoalType::FormSubmission,
            'is_primary' => true,
        ]);

        $experiment = SyncCampaignExperimentAction::run($campaign);
        $this->assertInstanceOf(Experiment::class, $experiment);
        $experiment->forceFill(['allocation_strategy' => AllocationStrategy::StickyWeighted])->save();
        $controlVariant = $experiment->variants()->where('key', 'control')->firstOrFail();
        $benefitVariant = $experiment->variants()->where('key', 'benefit')->firstOrFail();
        $goal = $experiment->goals()->where('key', 'book-demo')->firstOrFail();

        for ($allocationIndex = 1; $allocationIndex <= 10; $allocationIndex++) {
            ExperimentAllocation::query()->create([
                'experiment_id' => $experiment->getKey(),
                'experiment_variant_id' => $controlVariant->getKey(),
                'allocation_key' => 'control-' . $allocationIndex,
                'allocation_hash' => hash('sha256', 'control-' . $allocationIndex),
                'allocated_at' => CarbonImmutable::parse('2026-05-01 12:00:00'),
            ]);
            ExperimentAllocation::query()->create([
                'experiment_id' => $experiment->getKey(),
                'experiment_variant_id' => $benefitVariant->getKey(),
                'allocation_key' => 'benefit-' . $allocationIndex,
                'allocation_hash' => hash('sha256', 'benefit-' . $allocationIndex),
                'allocated_at' => CarbonImmutable::parse('2026-05-01 12:00:00'),
            ]);
        }

        for ($conversionIndex = 1; $conversionIndex <= 2; $conversionIndex++) {
            ExperimentGoalEvent::query()->create([
                'experiment_id' => $experiment->getKey(),
                'experiment_variant_id' => $controlVariant->getKey(),
                'experiment_goal_id' => $goal->getKey(),
                'event_key' => 'control-conversion-' . $conversionIndex,
                'occurred_at' => CarbonImmutable::parse('2026-05-02 12:00:00'),
            ]);
        }

        for ($conversionIndex = 1; $conversionIndex <= 4; $conversionIndex++) {
            ExperimentGoalEvent::query()->create([
                'experiment_id' => $experiment->getKey(),
                'experiment_variant_id' => $benefitVariant->getKey(),
                'experiment_goal_id' => $goal->getKey(),
                'event_key' => 'benefit-conversion-' . $conversionIndex,
                'occurred_at' => CarbonImmutable::parse('2026-05-02 12:00:00'),
            ]);
        }

        $results = BuildCampaignExperimentResultsAction::run($campaign);

        $this->assertNotNull($results);
        $this->assertSame($experiment->getKey(), $results->experimentId);
        $this->assertSame(20, $results->totalAllocations);
        $this->assertSame(6, $results->totalConversions);
        $this->assertSame('benefit', $results->winningVariantKey);
        $this->assertCount(2, $results->variants);
        $this->assertSame(20.0, $results->variants[0]->conversionRate);
        $this->assertNull($results->variants[0]->liftPercent);
        $this->assertSame(40.0, $results->variants[1]->conversionRate);
        $this->assertSame(100.0, $results->variants[1]->liftPercent);
        $this->assertTrue($results->variants[1]->isWinner);
    }

    public function test_it_declares_campaign_experiment_sync_in_package_metadata(): void
    {
        $manifest = json_decode(
            (string) file_get_contents(__DIR__ . '/../../../capell.json'),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );
        $composer = json_decode(
            (string) file_get_contents(__DIR__ . '/../../../composer.json'),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertContains('capell-app/experiments', $manifest['dependencies']['supports']);
        $this->assertSame(BuildCampaignExperimentResultsAction::class, $manifest['actions']['buildCampaignExperimentResults']);
        $this->assertSame(SyncCampaignExperimentAction::class, $manifest['actions']['syncCampaignExperiment']);
        $this->assertContains('campaign-experiment-sync', $manifest['capabilities']);
        $this->assertContains('landing-page-variant-experiments', $manifest['capabilities']);
        $this->assertArrayHasKey('capell-app/experiments', $composer['suggest']);
    }
}
