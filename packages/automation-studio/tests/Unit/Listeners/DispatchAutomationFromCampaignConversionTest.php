<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Jobs\DispatchQueuedAutomationTriggerJob;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromCampaignConversion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;

it('queues campaign conversion events with conversion payload metadata', function (): void {
    Queue::fake();
    $conversion = new class extends Model
    {
        use HasFactory;

        protected $table = 'campaign_conversions';
    };
    $conversion->forceFill([
        'id' => 12,
        'campaign_group_id' => 3,
        'campaign_conversion_goal_id' => 4,
        'campaign_landing_page_id' => 5,
        'site_id' => 6,
        'source_type' => 'form_submission',
        'source_id' => 7,
    ]);
    $conversion->exists = true;

    (new DispatchAutomationFromCampaignConversion(new QueueAutomationTriggerAction))->handle((object) ['conversion' => $conversion]);

    Queue::assertPushed(DispatchQueuedAutomationTriggerJob::class, function (DispatchQueuedAutomationTriggerJob $job): bool {
        expect($job->event->triggerType)->toBe(AutomationTriggerType::CampaignConverted)
            ->and($job->event->sourceType)->toBe('campaign-studio.conversion')
            ->and($job->event->sourceId)->toBe('12')
            ->and($job->siteId)->toBe(6)
            ->and($job->event->payload)->toMatchArray([
                'conversion_id' => 12,
                'campaign_group_id' => 3,
                'campaign_conversion_goal_id' => 4,
                'campaign_landing_page_id' => 5,
                'site_id' => 6,
                'source_type' => 'form_submission',
                'source_id' => 7,
            ]);

        return true;
    });
});
