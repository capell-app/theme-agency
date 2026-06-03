<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Jobs\DispatchQueuedAutomationTriggerJob;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromFormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;

it('queues form submission events with normalized automation trigger payloads', function (): void {
    Queue::fake();
    $form = new class extends Model
    {
        /** @use HasFactory<Factory<self>> */
        use HasFactory;

        /** @var list<string> */
        protected $guarded = [];
    };
    $form->forceFill(['id' => 12, 'handle' => 'contact', 'site_id' => 3]);
    $form->exists = true;

    $submission = new class extends Model
    {
        /** @use HasFactory<Factory<self>> */
        use HasFactory;

        /** @var list<string> */
        protected $guarded = [];
    };
    $submission->forceFill(['id' => 55, 'site_id' => 3]);
    $submission->exists = true;

    (new DispatchAutomationFromFormSubmission(new QueueAutomationTriggerAction))->handle((object) [
        'form' => $form,
        'submission' => $submission,
        'payload' => ['email' => 'person@example.test'],
    ]);

    Queue::assertPushed(DispatchQueuedAutomationTriggerJob::class, function (DispatchQueuedAutomationTriggerJob $job): bool {
        expect($job->event->triggerType)->toBe(AutomationTriggerType::FormSubmitted)
            ->and($job->event->sourceType)->toBe('form-builder.form')
            ->and($job->event->sourceId)->toBe('contact')
            ->and($job->siteId)->toBe(3)
            ->and($job->event->payload)->toMatchArray([
                'email' => 'person@example.test',
                'form_id' => 12,
                'form_handle' => 'contact',
                'submission_id' => 55,
            ]);

        return true;
    });
});

it('falls back to stored submission payloads when the event has no payload', function (): void {
    Queue::fake();
    $submission = new class extends Model
    {
        /** @use HasFactory<Factory<self>> */
        use HasFactory;

        /** @var list<string> */
        protected $guarded = [];
    };
    $submission->forceFill(['id' => 56, 'payload' => ['email' => 'stored@example.test']]);
    $submission->exists = true;

    (new DispatchAutomationFromFormSubmission(new QueueAutomationTriggerAction))->handle((object) [
        'submission' => $submission,
    ]);

    Queue::assertPushed(DispatchQueuedAutomationTriggerJob::class, function (DispatchQueuedAutomationTriggerJob $job): bool {
        expect($job->event->payload)->toMatchArray([
            'email' => 'stored@example.test',
            'submission_id' => 56,
        ]);

        return true;
    });
});
