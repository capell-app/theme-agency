<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromFormSubmission;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

it('normalizes form submission events into automation trigger payloads', function (): void {
    $actions = new AutomationActionRegistry;
    $rules = new AutomationRuleRegistry;
    $handler = new class implements AutomationActionHandler
    {
        /** @var list<array<string, mixed>> */
        public array $handledPayloads = [];

        public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
        {
            $this->handledPayloads[] = $event->payload;

            return new AutomationActionResultData(success: true);
        }
    };

    $actions->registerHandler(
        AutomationActionType::SendEmail,
        $handler,
    );

    $rules->register(new AutomationRuleData(
        key: 'contact-form',
        name: 'Contact form',
        triggerType: AutomationTriggerType::FormSubmitted,
        actions: [
            new AutomationRuleActionData(
                key: 'email-contact',
                type: AutomationActionType::SendEmail,
            ),
        ],
        conditions: ['form_handle' => 'contact'],
    ));

    $form = new class extends Model
    {
        /** @use HasFactory<Factory<self>> */
        use HasFactory;

        /** @var list<string> */
        protected $guarded = [];
    };
    $form->forceFill(['id' => 12, 'handle' => 'contact']);
    $form->exists = true;

    (new DispatchAutomationFromFormSubmission(new DispatchAutomationTriggerAction($rules, $actions)))->handle((object) [
        'form' => $form,
        'payload' => ['email' => 'person@example.test'],
    ]);

    expect($handler->handledPayloads)->toHaveCount(1)
        ->and($handler->handledPayloads[0])->toMatchArray([
            'email' => 'person@example.test',
            'form_id' => 12,
            'form_handle' => 'contact',
        ]);
});
