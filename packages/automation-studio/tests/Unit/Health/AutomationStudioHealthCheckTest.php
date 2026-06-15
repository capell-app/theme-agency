<?php

declare(strict_types=1);

use Capell\AccessGate\Events\RegistrationApproved;
use Capell\AutomationStudio\Actions\RegisterAutomationStudioDefaultsAction;
use Capell\AutomationStudio\Health\AutomationStudioHealthCheck;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromAccessApproval;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromCampaignConversion;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromFormSubmission;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromWorkspaceStateChanged;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationTriggerRegistry;
use Capell\CampaignStudio\Events\CampaignConverted;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\FormBuilder\Events\FormSubmitted;
use Capell\PublishingStudio\Events\WorkspaceStateChanged;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

it('runs real Automation Studio health diagnostics', function (): void {
    $triggers = new AutomationTriggerRegistry;
    $actions = new AutomationActionRegistry;

    app()->instance(AutomationTriggerRegistry::class, $triggers);
    app()->instance(AutomationActionRegistry::class, $actions);

    (new RegisterAutomationStudioDefaultsAction($triggers, $actions))->handle();

    Event::listen(FormSubmitted::class, DispatchAutomationFromFormSubmission::class);
    Event::listen(RegistrationApproved::class, DispatchAutomationFromAccessApproval::class);
    Event::listen(WorkspaceStateChanged::class, DispatchAutomationFromWorkspaceStateChanged::class);
    Event::listen(CampaignConverted::class, DispatchAutomationFromCampaignConversion::class);

    $diagnostics = AutomationStudioHealthCheck::runDiagnostics();

    expect(AutomationStudioHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($diagnostics)->toBeInstanceOf(Collection::class)
        ->and($diagnostics)->toHaveCount(4)
        ->and($diagnostics->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue()
        ->and(AutomationStudioHealthCheck::passed())->toBeTrue();
});

it('reports missing Automation Studio storage tables', function (): void {
    Schema::dropIfExists('automation_runs');

    $result = (new AutomationStudioHealthCheck)->storageTablesCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('automation_runs')
        ->and($result->remediation)->toBe(__('capell-automation-studio::generic.health.storage_tables.remediation'));
});
