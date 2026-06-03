<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Jobs\DispatchQueuedAutomationTriggerJob;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromWorkspaceStateChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;

it('queues published workspace transitions with workspace payload metadata', function (): void {
    Queue::fake();

    $workspace = new class extends Model
    {
        use HasFactory;

        /** @var list<string> */
        protected $guarded = [];
    };
    $workspace->forceFill(['id' => 33, 'site_id' => 5]);
    $workspace->exists = true;

    (new DispatchAutomationFromWorkspaceStateChanged(new QueueAutomationTriggerAction))->handle((object) [
        'workspace' => $workspace,
        'transition' => 'published',
        'newStatus' => 'published',
    ]);

    Queue::assertPushed(DispatchQueuedAutomationTriggerJob::class, function (DispatchQueuedAutomationTriggerJob $job): bool {
        expect($job->event->triggerType)->toBe(AutomationTriggerType::PagePublished)
            ->and($job->event->sourceType)->toBe('publishing-studio.workspace')
            ->and($job->event->sourceId)->toBe('33')
            ->and($job->siteId)->toBe(5)
            ->and($job->event->payload)->toMatchArray([
                'workspace_id' => 33,
                'transition' => 'published',
                'status' => 'published',
            ]);

        return true;
    });
});

it('does not queue workspace transitions that are not published', function (): void {
    Queue::fake();

    (new DispatchAutomationFromWorkspaceStateChanged(new QueueAutomationTriggerAction))->handle((object) [
        'transition' => 'drafted',
    ]);

    Queue::assertNotPushed(DispatchQueuedAutomationTriggerJob::class);
});
