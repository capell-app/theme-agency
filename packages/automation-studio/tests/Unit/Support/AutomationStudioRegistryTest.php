<?php

declare(strict_types=1);

use Capell\AccessGate\Events\RegistrationApproved;
use Capell\AutomationStudio\Actions\RegisterAutomationStudioDefaultsAction;
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
use Capell\AutomationStudio\Support\Handlers\CreateContactNoteAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\QueueAgentCapabilityAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\SendEmailAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\SubscribeUserAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\TagContactAutomationActionHandler;
use Capell\CampaignStudio\Events\CampaignConverted;
use Capell\FormBuilder\Events\FormSubmitted;
use Capell\PublishingStudio\Events\WorkspaceStateChanged;

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

it('registers native automation handlers by default', function (): void {
    $triggers = new AutomationTriggerRegistry;
    $actions = new AutomationActionRegistry;

    (new RegisterAutomationStudioDefaultsAction($triggers, $actions))->handle();

    expect($actions->definition(AutomationActionType::SendEmail)?->handler)->toBe(SendEmailAutomationActionHandler::class)
        ->and($triggers->get(AutomationTriggerType::FormSubmitted)?->eventClass)->toBe(FormSubmitted::class)
        ->and($triggers->get(AutomationTriggerType::AccessApproved)?->eventClass)->toBe(RegistrationApproved::class)
        ->and($triggers->get(AutomationTriggerType::PagePublished)?->eventClass)->toBe(WorkspaceStateChanged::class)
        ->and($triggers->get(AutomationTriggerType::CampaignConverted)?->eventClass)->toBe(CampaignConverted::class)
        ->and($actions->definition(AutomationActionType::QueueAgentCapability)?->handler)->toBe(QueueAgentCapabilityAutomationActionHandler::class)
        ->and($actions->definition(AutomationActionType::TagContact)?->handler)->toBe(TagContactAutomationActionHandler::class)
        ->and($actions->definition(AutomationActionType::CreateNote)?->handler)->toBe(CreateContactNoteAutomationActionHandler::class)
        ->and($actions->definition(AutomationActionType::SubscribeUser)?->handler)->toBe(SubscribeUserAutomationActionHandler::class)
        ->and($actions->handler(AutomationActionType::SendEmail))->toBeInstanceOf(SendEmailAutomationActionHandler::class)
        ->and($actions->handler(AutomationActionType::QueueAgentCapability))->toBeInstanceOf(QueueAgentCapabilityAutomationActionHandler::class)
        ->and($actions->handler(AutomationActionType::TagContact))->toBeInstanceOf(TagContactAutomationActionHandler::class)
        ->and($actions->handler(AutomationActionType::CreateNote))->toBeInstanceOf(CreateContactNoteAutomationActionHandler::class)
        ->and($actions->handler(AutomationActionType::SubscribeUser))->toBeInstanceOf(SubscribeUserAutomationActionHandler::class);
});

it('keeps native triggers and action handlers visible in the package manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['dependencies']['supports'])->toContain(
        'capell-app/access-gate',
        'capell-app/agent-bridge',
        'capell-app/campaign-studio',
        'capell-app/contacts',
        'capell-app/email-studio',
        'capell-app/form-builder',
        'capell-app/newsletter',
        'capell-app/public-actions',
        'capell-app/publishing-studio',
    )
        ->and($manifest['actions'])->toHaveKey('registerAutomationStudioDefaults', RegisterAutomationStudioDefaultsAction::class)
        ->and($manifest['actions'])->toHaveKey('loadPersistedAutomationRules')
        ->and($manifest['capabilities'])->toContain(
            'trigger-form-submitted',
            'trigger-access-approved',
            'trigger-page-published',
            'trigger-campaign-converted',
            'native-action-send-email',
            'native-action-webhook',
            'native-action-tag-contact',
            'native-action-create-note',
            'native-action-subscribe-user',
            'native-action-queue-agent-capability',
            'public-actions-bridge',
            'contacts-bridge',
            'newsletter-bridge',
            'email-studio-bridge',
            'agent-capability-queue',
        );
});
