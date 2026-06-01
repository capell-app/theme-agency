<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Jobs\DispatchQueuedAutomationTriggerJob;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

it('defines a unique queued trigger dispatch job with an idempotency key', function (): void {
    $event = new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::FormSubmitted,
        sourceType: 'form-builder.form',
        sourceId: 'contact',
        payload: ['email' => 'person@example.test'],
    );
    $job = new DispatchQueuedAutomationTriggerJob(
        event: $event,
        idempotencyKey: 'automation-trigger-123',
        siteId: 42,
    );

    expect($job)->toBeInstanceOf(ShouldQueue::class)
        ->and($job)->toBeInstanceOf(ShouldBeUnique::class)
        ->and($job->uniqueId())->toBe('automation-trigger-123')
        ->and($job->tries)->toBe(3)
        ->and($job->siteId)->toBe(42);
});

it('derives stable idempotency keys for the same trigger payload', function (): void {
    $event = new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::AccessApproved,
        sourceType: 'access-gate.registration',
        sourceId: '99',
        payload: ['email' => 'person@example.test'],
    );
    $action = new QueueAutomationTriggerAction;
    $method = new ReflectionMethod($action, 'idempotencyKey');

    expect($method->invoke($action, $event, 10))
        ->toBe($method->invoke($action, $event, 10))
        ->not->toBe($method->invoke($action, $event, 11));
});
