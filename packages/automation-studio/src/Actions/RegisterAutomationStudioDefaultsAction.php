<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\AccessGate\Events\RegistrationApproved;
use Capell\AutomationStudio\Data\AutomationActionDefinitionData;
use Capell\AutomationStudio\Data\AutomationTriggerDefinitionData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationTriggerRegistry;
use Capell\AutomationStudio\Support\Handlers\CreateContactNoteAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\DispatchPublicActionAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\DispatchWebhookAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\QueueAgentCapabilityAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\SendEmailAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\SubscribeUserAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\TagContactAutomationActionHandler;
use Capell\PublishingStudio\Events\WorkspaceStateChanged;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class RegisterAutomationStudioDefaultsAction
{
    use AsAction;

    public function __construct(
        private readonly AutomationTriggerRegistry $triggers,
        private readonly AutomationActionRegistry $actions,
    ) {}

    public function handle(): void
    {
        $this->registerTriggers();
        $this->registerActions();
    }

    private function registerTriggers(): void
    {
        $this->triggers->register(new AutomationTriggerDefinitionData(
            type: AutomationTriggerType::FormSubmitted,
            label: $this->triggerLabel(AutomationTriggerType::FormSubmitted),
            eventClass: implode('\\', ['Capell', 'FormBuilder', 'Events', 'FormSubmitted']),
        ));

        $this->triggers->register(new AutomationTriggerDefinitionData(
            type: AutomationTriggerType::AccessApproved,
            label: $this->triggerLabel(AutomationTriggerType::AccessApproved),
            eventClass: RegistrationApproved::class,
        ));

        $this->triggers->register(new AutomationTriggerDefinitionData(
            type: AutomationTriggerType::PagePublished,
            label: $this->triggerLabel(AutomationTriggerType::PagePublished),
            eventClass: WorkspaceStateChanged::class,
        ));

        $this->triggers->register(new AutomationTriggerDefinitionData(
            type: AutomationTriggerType::CampaignConverted,
            label: $this->triggerLabel(AutomationTriggerType::CampaignConverted),
            eventClass: 'Capell\\CampaignStudio\\Events\\CampaignConverted',
        ));
    }

    private function registerActions(): void
    {
        $this->actions->registerDefinition(new AutomationActionDefinitionData(
            type: AutomationActionType::SendEmail,
            label: $this->actionLabel(AutomationActionType::SendEmail),
            handler: SendEmailAutomationActionHandler::class,
        ));

        $this->actions->registerDefinition(new AutomationActionDefinitionData(
            type: AutomationActionType::QueueAgentCapability,
            label: $this->actionLabel(AutomationActionType::QueueAgentCapability),
            handler: QueueAgentCapabilityAutomationActionHandler::class,
        ));

        $this->actions->registerDefinition(new AutomationActionDefinitionData(
            type: AutomationActionType::Webhook,
            label: $this->actionLabel(AutomationActionType::Webhook),
            handler: DispatchWebhookAutomationActionHandler::class,
        ));

        $this->actions->registerDefinition(new AutomationActionDefinitionData(
            type: AutomationActionType::TagContact,
            label: $this->actionLabel(AutomationActionType::TagContact),
            handler: TagContactAutomationActionHandler::class,
        ));

        $this->actions->registerDefinition(new AutomationActionDefinitionData(
            type: AutomationActionType::CreateNote,
            label: $this->actionLabel(AutomationActionType::CreateNote),
            handler: CreateContactNoteAutomationActionHandler::class,
        ));

        $this->actions->registerDefinition(new AutomationActionDefinitionData(
            type: AutomationActionType::SubscribeUser,
            label: $this->actionLabel(AutomationActionType::SubscribeUser),
            handler: SubscribeUserAutomationActionHandler::class,
        ));

        $this->actions->registerDefinition(new AutomationActionDefinitionData(
            type: AutomationActionType::PublicAction,
            label: $this->actionLabel(AutomationActionType::PublicAction),
            handler: DispatchPublicActionAutomationActionHandler::class,
        ));
    }

    private function triggerLabel(AutomationTriggerType $type): string
    {
        try {
            if (function_exists('app') && app()->bound('translator')) {
                return $type->getLabel();
            }
        } catch (Throwable) {
            //
        }

        return ucwords(str_replace('_', ' ', $type->value));
    }

    private function actionLabel(AutomationActionType $type): string
    {
        try {
            if (function_exists('app') && app()->bound('translator')) {
                return $type->getLabel();
            }
        } catch (Throwable) {
            //
        }

        return ucwords(str_replace('_', ' ', $type->value));
    }
}
