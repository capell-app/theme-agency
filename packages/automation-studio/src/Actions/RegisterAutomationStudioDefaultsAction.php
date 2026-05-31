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
use Capell\AutomationStudio\Support\Handlers\DispatchPublicActionAutomationActionHandler;
use Capell\PublishingStudio\Events\WorkspaceStateChanged;
use Lorisleiva\Actions\Concerns\AsAction;

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
            label: AutomationTriggerType::FormSubmitted->getLabel(),
            eventClass: implode('\\', ['Capell', 'FormBuilder', 'Events', 'FormSubmitted']),
        ));

        $this->triggers->register(new AutomationTriggerDefinitionData(
            type: AutomationTriggerType::AccessApproved,
            label: AutomationTriggerType::AccessApproved->getLabel(),
            eventClass: RegistrationApproved::class,
        ));

        $this->triggers->register(new AutomationTriggerDefinitionData(
            type: AutomationTriggerType::PagePublished,
            label: AutomationTriggerType::PagePublished->getLabel(),
            eventClass: WorkspaceStateChanged::class,
        ));

        $this->triggers->register(new AutomationTriggerDefinitionData(
            type: AutomationTriggerType::CampaignConverted,
            label: AutomationTriggerType::CampaignConverted->getLabel(),
            eventClass: 'Capell\\CampaignStudio\\Events\\CampaignConverted',
        ));
    }

    private function registerActions(): void
    {
        foreach ([
            AutomationActionType::SendEmail,
            AutomationActionType::Webhook,
            AutomationActionType::TagContact,
            AutomationActionType::CreateNote,
            AutomationActionType::SubscribeUser,
            AutomationActionType::QueueAgentCapability,
        ] as $actionType) {
            $this->actions->registerDefinition(new AutomationActionDefinitionData(
                type: $actionType,
                label: $actionType->getLabel(),
            ));
        }

        $this->actions->registerDefinition(new AutomationActionDefinitionData(
            type: AutomationActionType::PublicAction,
            label: AutomationActionType::PublicAction->getLabel(),
            handler: DispatchPublicActionAutomationActionHandler::class,
        ));
    }
}
