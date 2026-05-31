<?php

declare(strict_types=1);

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionDefinitionData;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerDefinitionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Capell\AutomationStudio\Support\AutomationTriggerRegistry;
use Capell\FormBuilder\Events\FormSubmitted;

it('registers trigger and action definitions', function (): void {
    $triggers = new AutomationTriggerRegistry;
    $actions = new AutomationActionRegistry;

    $triggers->register(new AutomationTriggerDefinitionData(
        type: AutomationTriggerType::FormSubmitted,
        label: 'Form submitted',
        eventClass: FormSubmitted::class,
    ));

    $actions->registerDefinition(new AutomationActionDefinitionData(
        type: AutomationActionType::SendEmail,
        label: 'Send email',
    ));

    expect(new AutomationRuleRegistry)->toBeInstanceOf(AutomationRuleRegistry::class)
        ->and($triggers->get(AutomationTriggerType::FormSubmitted)?->eventClass)->toBe(FormSubmitted::class)
        ->and($actions->definition(AutomationActionType::SendEmail))->not->toBeNull();
});

it('rejects invalid action handlers', function (): void {
    $actions = new AutomationActionRegistry;

    expect(fn (): mixed => (new ReflectionMethod($actions, 'registerHandler'))->invoke($actions, AutomationActionType::SendEmail, stdClass::class))
        ->toThrow(InvalidArgumentException::class);
});

it('resolves registered object action handlers', function (): void {
    $actions = new AutomationActionRegistry;
    $handler = new class implements AutomationActionHandler
    {
        public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
        {
            return new AutomationActionResultData(success: true);
        }
    };

    $actions->registerHandler(AutomationActionType::SendEmail, $handler);

    expect($actions->handler(AutomationActionType::SendEmail))->toBe($handler);
});
