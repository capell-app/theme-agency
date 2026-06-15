<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Health;

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Actions\LoadPersistedAutomationRulesAction;
use Capell\AutomationStudio\Actions\PersistAutomationTriggerResultsAction;
use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Actions\RecordAutomationRunAction;
use Capell\AutomationStudio\Actions\RegisterAutomationStudioDefaultsAction;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Filament\Resources\AutomationRules\AutomationRuleResource;
use Capell\AutomationStudio\Filament\Resources\AutomationRuns\AutomationRunResource;
use Capell\AutomationStudio\Jobs\DispatchQueuedAutomationTriggerJob;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromAccessApproval;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromCampaignConversion;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromFormSubmission;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromWorkspaceStateChanged;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationTriggerRegistry;
use Capell\AutomationStudio\Support\Handlers\CreateContactNoteAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\DispatchPublicActionAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\DispatchWebhookAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\QueueAgentCapabilityAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\SendEmailAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\SubscribeUserAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\TagContactAutomationActionHandler;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class AutomationStudioHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_TABLES = [
        'automation_rules',
        'automation_runs',
    ];

    /**
     * @var list<class-string>
     */
    private const array REQUIRED_ADMIN_RESOURCES = [
        AutomationRuleResource::class,
        AutomationRunResource::class,
    ];

    /**
     * @var list<class-string>
     */
    private const array REQUIRED_ACTIONS = [
        DispatchAutomationTriggerAction::class,
        LoadPersistedAutomationRulesAction::class,
        PersistAutomationTriggerResultsAction::class,
        QueueAutomationTriggerAction::class,
        RecordAutomationRunAction::class,
        RegisterAutomationStudioDefaultsAction::class,
    ];

    /**
     * @var array<string, class-string<AutomationActionHandler>>
     */
    private const array DEFAULT_ACTION_HANDLERS = [
        AutomationActionType::SendEmail->value => SendEmailAutomationActionHandler::class,
        AutomationActionType::Webhook->value => DispatchWebhookAutomationActionHandler::class,
        AutomationActionType::TagContact->value => TagContactAutomationActionHandler::class,
        AutomationActionType::CreateNote->value => CreateContactNoteAutomationActionHandler::class,
        AutomationActionType::SubscribeUser->value => SubscribeUserAutomationActionHandler::class,
        AutomationActionType::QueueAgentCapability->value => QueueAgentCapabilityAutomationActionHandler::class,
        AutomationActionType::PublicAction->value => DispatchPublicActionAutomationActionHandler::class,
    ];

    /**
     * @var array<string, class-string>
     */
    private const array OPTIONAL_EVENT_LISTENERS = [
        'Capell\\FormBuilder\\Events\\FormSubmitted' => DispatchAutomationFromFormSubmission::class,
        'Capell\\AccessGate\\Events\\RegistrationApproved' => DispatchAutomationFromAccessApproval::class,
        'Capell\\PublishingStudio\\Events\\WorkspaceStateChanged' => DispatchAutomationFromWorkspaceStateChanged::class,
        'Capell\\CampaignStudio\\Events\\CampaignConverted' => DispatchAutomationFromCampaignConversion::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(?string $key = null): Collection
    {
        $check = new self;

        if (is_string($key) && trim($key) !== '') {
            return collect($check->diagnosticsForKey($key));
        }

        return collect($check->allDiagnostics());
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: (string) __('capell-automation-studio::generic.health.storage_tables.label'),
            passed: $missingTables === [],
            message: $missingTables === []
                ? (string) __('capell-automation-studio::generic.health.storage_tables.passed')
                : (string) __('capell-automation-studio::generic.health.storage_tables.failed', ['tables' => implode(', ', $missingTables)]),
            remediation: $missingTables === []
                ? null
                : (string) __('capell-automation-studio::generic.health.storage_tables.remediation'),
        );
    }

    public function registryDefaultsCheck(): DoctorCheckResultData
    {
        $missingTriggers = $this->missingTriggerDefinitions();
        $missingActions = $this->missingActionDefinitions();
        $unresolvableHandlers = $this->unresolvableActionHandlers();
        $missing = array_merge($missingTriggers, $missingActions, $unresolvableHandlers);

        return new DoctorCheckResultData(
            label: (string) __('capell-automation-studio::generic.health.registry_defaults.label'),
            passed: $missing === [],
            message: $missing === []
                ? (string) __('capell-automation-studio::generic.health.registry_defaults.passed')
                : (string) __('capell-automation-studio::generic.health.registry_defaults.failed', ['items' => implode(', ', $missing)]),
            remediation: $missing === []
                ? null
                : (string) __('capell-automation-studio::generic.health.registry_defaults.remediation'),
        );
    }

    public function runtimeSurfacesCheck(): DoctorCheckResultData
    {
        $missing = array_merge(
            $this->missingAdminResources(),
            $this->unresolvableActions(),
            $this->queuedJobFailures(),
        );

        return new DoctorCheckResultData(
            label: (string) __('capell-automation-studio::generic.health.runtime_surfaces.label'),
            passed: $missing === [],
            message: $missing === []
                ? (string) __('capell-automation-studio::generic.health.runtime_surfaces.passed')
                : (string) __('capell-automation-studio::generic.health.runtime_surfaces.failed', ['items' => implode(', ', $missing)]),
            remediation: $missing === []
                ? null
                : (string) __('capell-automation-studio::generic.health.runtime_surfaces.remediation'),
        );
    }

    public function optionalBridgeListenersCheck(): DoctorCheckResultData
    {
        $missingListeners = $this->missingOptionalBridgeListeners();

        return new DoctorCheckResultData(
            label: (string) __('capell-automation-studio::generic.health.optional_bridge_listeners.label'),
            passed: $missingListeners === [],
            message: $missingListeners === []
                ? (string) __('capell-automation-studio::generic.health.optional_bridge_listeners.passed')
                : (string) __('capell-automation-studio::generic.health.optional_bridge_listeners.failed', ['listeners' => implode(', ', $missingListeners)]),
            remediation: $missingListeners === []
                ? null
                : (string) __('capell-automation-studio::generic.health.optional_bridge_listeners.remediation'),
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect(self::REQUIRED_TABLES)
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingTriggerDefinitions(): array
    {
        try {
            /** @var AutomationTriggerRegistry $triggers */
            $triggers = resolve(AutomationTriggerRegistry::class);
        } catch (Throwable) {
            return array_map(
                static fn (AutomationTriggerType $triggerType): string => $triggerType->value,
                AutomationTriggerType::cases(),
            );
        }

        return array_values(collect(AutomationTriggerType::cases())
            ->reject(static fn (AutomationTriggerType $triggerType): bool => $triggers->get($triggerType) !== null)
            ->map(static fn (AutomationTriggerType $triggerType): string => $triggerType->value)
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingActionDefinitions(): array
    {
        try {
            /** @var AutomationActionRegistry $actions */
            $actions = resolve(AutomationActionRegistry::class);
        } catch (Throwable) {
            return array_keys(self::DEFAULT_ACTION_HANDLERS);
        }

        return array_values(collect(AutomationActionType::cases())
            ->reject(static fn (AutomationActionType $actionType): bool => $actions->definition($actionType) !== null)
            ->map(static fn (AutomationActionType $actionType): string => $actionType->value)
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function unresolvableActionHandlers(): array
    {
        try {
            /** @var AutomationActionRegistry $actions */
            $actions = resolve(AutomationActionRegistry::class);
        } catch (Throwable) {
            return array_keys(self::DEFAULT_ACTION_HANDLERS);
        }

        $unresolvable = [];

        foreach (self::DEFAULT_ACTION_HANDLERS as $actionTypeValue => $handlerClass) {
            try {
                $handler = $actions->handler(AutomationActionType::from($actionTypeValue));
            } catch (Throwable) {
                $handler = null;
            }

            if (! $handler instanceof $handlerClass) {
                $unresolvable[] = $actionTypeValue;
            }
        }

        return $unresolvable;
    }

    /**
     * @return list<string>
     */
    public function missingAdminResources(): array
    {
        return array_values(collect(self::REQUIRED_ADMIN_RESOURCES)
            ->reject(static fn (string $resourceClass): bool => class_exists($resourceClass))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function unresolvableActions(): array
    {
        $unresolvable = [];

        foreach (self::REQUIRED_ACTIONS as $actionClass) {
            try {
                if (! resolve($actionClass) instanceof $actionClass) {
                    $unresolvable[] = $actionClass;
                }
            } catch (Throwable) {
                $unresolvable[] = $actionClass;
            }
        }

        return $unresolvable;
    }

    /**
     * @return list<string>
     */
    public function queuedJobFailures(): array
    {
        $failures = [];

        if (! class_exists(DispatchQueuedAutomationTriggerJob::class)) {
            return [DispatchQueuedAutomationTriggerJob::class];
        }

        if (! is_subclass_of(DispatchQueuedAutomationTriggerJob::class, ShouldQueue::class)) {
            $failures[] = DispatchQueuedAutomationTriggerJob::class . ' should queue';
        }

        if (! is_subclass_of(DispatchQueuedAutomationTriggerJob::class, ShouldBeUnique::class)) {
            $failures[] = DispatchQueuedAutomationTriggerJob::class . ' should be unique';
        }

        return $failures;
    }

    /**
     * @return list<string>
     */
    public function missingOptionalBridgeListeners(): array
    {
        $missing = [];

        foreach (self::OPTIONAL_EVENT_LISTENERS as $eventClass => $listenerClass) {
            if (! class_exists($eventClass)) {
                continue;
            }

            if (! $this->eventHasListener($eventClass, $listenerClass)) {
                $missing[] = $eventClass;
            }
        }

        return $missing;
    }

    /**
     * @return list<DoctorCheckResultData>
     */
    private function allDiagnostics(): array
    {
        return [
            $this->storageTablesCheck(),
            $this->registryDefaultsCheck(),
            $this->runtimeSurfacesCheck(),
            $this->optionalBridgeListenersCheck(),
        ];
    }

    /**
     * @return list<DoctorCheckResultData>
     */
    private function diagnosticsForKey(string $key): array
    {
        return match ($key) {
            'automation-studio.storage' => [$this->storageTablesCheck()],
            'automation-studio.registries' => [$this->registryDefaultsCheck()],
            'automation-studio.runtime' => [$this->runtimeSurfacesCheck()],
            'automation-studio.optional-bridges' => [$this->optionalBridgeListenersCheck()],
            default => $this->allDiagnostics(),
        };
    }

    /**
     * @param  class-string  $eventClass
     * @param  class-string  $listenerClass
     */
    private function eventHasListener(string $eventClass, string $listenerClass): bool
    {
        $rawListeners = Event::getRawListeners();
        $listeners = $rawListeners[$eventClass] ?? [];

        foreach ($listeners as $listener) {
            if (is_array($listener) && ($listener[0] ?? null) instanceof $listenerClass) {
                return true;
            }

            if (is_array($listener) && ($listener[0] ?? null) === $listenerClass) {
                return true;
            }

            if (is_string($listener) && ($listener === $listenerClass || str_starts_with($listener, $listenerClass . '@'))) {
                return true;
            }

            if ($listener instanceof $listenerClass) {
                return true;
            }
        }

        return false;
    }
}
