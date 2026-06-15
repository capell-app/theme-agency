<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\PersistAutomationTriggerResultsAction;
use Capell\AutomationStudio\Actions\ReplayAutomationRunAction;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Support\AutomationActionRegistry;

it('replays a failed automation run as a new persisted attempt', function (): void {
    $actionRegistry = new AutomationActionRegistry;
    $actionRegistry->registerHandler(AutomationActionType::SendEmail, new class implements AutomationActionHandler
    {
        public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
        {
            return new AutomationActionResultData(
                success: true,
                message: 'Replayed ' . (string) ($event->payload['email'] ?? ''),
                context: ['replayed_action' => $action->key],
            );
        }
    });

    $rule = automationStudioReplayRule();
    $run = automationStudioReplayRun($rule, AutomationRunStatus::Failed, [
        'email' => 'person@example.test',
        'form_handle' => 'contact',
    ]);

    $results = (new ReplayAutomationRunAction($actionRegistry, new PersistAutomationTriggerResultsAction))->handle($run);

    $replayedRun = AutomationRun::query()
        ->whereKeyNot($run->getKey())
        ->sole();

    expect($results)->toHaveCount(1)
        ->and($results[0]->success)->toBeTrue()
        ->and($results[0]->message)->toBe('Replayed person@example.test')
        ->and($replayedRun->status)->toBe(AutomationRunStatus::Succeeded)
        ->and($replayedRun->attempt_number)->toBe(2)
        ->and($replayedRun->idempotency_key)->toBe('replay:' . $run->getKey() . ':2:lead-nurture:send-thank-you')
        ->and($replayedRun->payload)->toMatchArray(['email' => 'person@example.test']);
});

it('does not replay successful automation runs', function (): void {
    $rule = automationStudioReplayRule();
    $run = automationStudioReplayRun($rule, AutomationRunStatus::Succeeded);
    $action = new ReplayAutomationRunAction(new AutomationActionRegistry, new PersistAutomationTriggerResultsAction);

    expect($action->canReplay($run))->toBeFalse();

    $action->handle($run);
})->throws(RuntimeException::class, 'Only pending, skipped, or failed automation runs');

it('fails clearly when the original rule action no longer exists', function (): void {
    $rule = automationStudioReplayRule(actions: [
        [
            'key' => 'different-action',
            'type' => AutomationActionType::SendEmail->value,
        ],
    ]);
    $run = automationStudioReplayRun($rule, AutomationRunStatus::Failed);
    $action = new ReplayAutomationRunAction(new AutomationActionRegistry, new PersistAutomationTriggerResultsAction);

    $action->handle($run);
})->throws(RuntimeException::class, 'The original automation action can no longer be found.');

/**
 * @param  list<array<string, mixed>>  $actions
 */
function automationStudioReplayRule(array $actions = []): AutomationRule
{
    /** @var AutomationRule $rule */
    $rule = AutomationRule::query()->create([
        'key' => 'lead-nurture',
        'name' => 'Lead nurture',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Paused,
        'conditions' => ['form_handle' => 'contact'],
        'actions' => $actions === [] ? [
            [
                'key' => 'send-thank-you',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ] : $actions,
    ]);

    return $rule;
}

/**
 * @param  array<string, mixed>  $payload
 */
function automationStudioReplayRun(AutomationRule $rule, AutomationRunStatus $status, array $payload = []): AutomationRun
{
    /** @var AutomationRun $run */
    $run = AutomationRun::query()->create([
        'automation_rule_id' => $rule->getKey(),
        'rule_key' => $rule->key,
        'action_key' => 'send-thank-you',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'action_type' => AutomationActionType::SendEmail,
        'source_type' => 'form-builder.form',
        'source_id' => 'contact',
        'idempotency_key' => 'original:' . $status->value,
        'attempt_number' => 1,
        'max_attempts' => 3,
        'status' => $status,
        'message' => 'Original result',
        'payload' => $payload,
        'context' => [],
        'started_at' => now(),
        'finished_at' => now(),
    ]);

    return $run;
}
