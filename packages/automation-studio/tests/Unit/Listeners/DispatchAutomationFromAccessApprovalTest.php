<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Jobs\DispatchQueuedAutomationTriggerJob;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromAccessApproval;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;

it('queues access approval events with registration payload metadata', function (): void {
    Queue::fake();

    $registration = new class extends Model
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var list<string> */
        protected $guarded = [];
    };
    $registration->forceFill([
        'id' => 77,
        'site_id' => 4,
        'email' => 'member@example.test',
        'area_id' => 9,
    ]);
    $registration->exists = true;

    (new DispatchAutomationFromAccessApproval(new QueueAutomationTriggerAction))->handle((object) [
        'registration' => $registration,
    ]);

    Queue::assertPushed(DispatchQueuedAutomationTriggerJob::class, function (DispatchQueuedAutomationTriggerJob $job): bool {
        expect($job->event->triggerType)->toBe(AutomationTriggerType::AccessApproved)
            ->and($job->event->sourceType)->toBe('access-gate.registration')
            ->and($job->event->sourceId)->toBe('77')
            ->and($job->siteId)->toBe(4)
            ->and($job->event->payload)->toMatchArray([
                'registration_id' => 77,
                'email' => 'member@example.test',
                'area_id' => 9,
            ]);

        return true;
    });
});
