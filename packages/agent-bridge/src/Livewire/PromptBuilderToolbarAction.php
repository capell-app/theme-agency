<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Livewire;

use Capell\AgentBridge\Actions\BuildAgentBridgePromptAction;
use Capell\AgentBridge\Actions\DeleteAgentBridgePromptAction;
use Capell\AgentBridge\Actions\SaveAgentBridgePromptAction;
use Capell\AgentBridge\Data\AgentBridgePromptData;
use Capell\AgentBridge\Filament\Pages\CapellAgentBridgePromptBuilderPage;
use Capell\AgentBridge\Models\CapellAgentBridgeSavedPrompt;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;

final class PromptBuilderToolbarAction extends Component
{
    /** @var array<string, string> */
    public array $data = [
        'goal' => '',
        'area' => 'pages',
        'operation' => 'inspect',
        'safety' => 'preview_first',
        'target' => '',
        'constraints' => '',
        'success_criteria' => '',
    ];

    public string $preparedPrompt = '';

    public string $templateName = '';

    public string $templateDescription = '';

    public bool $isOpen = false;

    public ?int $selectedSavedPromptId = null;

    public function render(): View
    {
        return view('capell-agent-bridge::livewire.prompt-builder-toolbar-action', [
            'deepLinkUrl' => $this->deepLinkUrl(),
        ]);
    }

    public function openBuilder(): void
    {
        $this->isOpen = true;
        $this->refreshPreparedPrompt();
    }

    public function closeBuilder(): void
    {
        $this->isOpen = false;
    }

    public function buildPrompt(): void
    {
        $this->refreshPreparedPrompt();

        Notification::make('capell_agent-bridge_prompt_ready')
            ->success()
            ->title(__('capell-agent-bridge::admin.prompt_ready'))
            ->send();
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'data.')) {
            $this->refreshPreparedPrompt();
        }
    }

    public function refreshPromptForCopy(): void
    {
        $this->refreshPreparedPrompt();
    }

    public function savePrompt(): void
    {
        $user = $this->user();

        if (! $user instanceof Authenticatable) {
            return;
        }

        $this->validate([
            'templateName' => ['required', 'string', 'max:120'],
            'templateDescription' => ['nullable', 'string', 'max:500'],
            'data.goal' => ['required', 'string', 'max:180'],
        ]);

        $savedPrompt = $this->selectedSavedPrompt();
        $savedPrompt = SaveAgentBridgePromptAction::run(
            user: $user,
            name: $this->templateName,
            description: $this->templateDescription !== '' ? $this->templateDescription : null,
            data: $this->promptData(),
            savedPrompt: $savedPrompt,
        );

        $this->selectedSavedPromptId = $savedPrompt->id;
        $this->preparedPrompt = $savedPrompt->prompt;

        Notification::make('capell_agent-bridge_prompt_saved')
            ->success()
            ->title(__('capell-agent-bridge::admin.saved_prompt_saved'))
            ->send();
    }

    public function loadSavedPrompt(): void
    {
        $savedPrompt = $this->selectedSavedPrompt();

        if (! $savedPrompt instanceof CapellAgentBridgeSavedPrompt) {
            return;
        }

        /** @var array<string, string> $formState */
        $formState = $savedPrompt->form_state;
        $this->data = array_merge($this->data, $formState);
        $this->preparedPrompt = $savedPrompt->prompt;
        $this->templateName = $savedPrompt->name;
        $this->templateDescription = $savedPrompt->description ?? '';
    }

    public function deleteSavedPrompt(): void
    {
        $user = $this->user();
        $savedPrompt = $this->selectedSavedPrompt();

        if (! $user instanceof Authenticatable || ! $savedPrompt instanceof CapellAgentBridgeSavedPrompt) {
            return;
        }

        DeleteAgentBridgePromptAction::run($user, $savedPrompt);

        $this->selectedSavedPromptId = null;
        $this->templateName = '';
        $this->templateDescription = '';

        Notification::make('capell_agent-bridge_prompt_deleted')
            ->success()
            ->title(__('capell-agent-bridge::admin.saved_prompt_deleted'))
            ->send();
    }

    public function applyStarter(string $starter): void
    {
        $state = match ($starter) {
            'create_disabled_draft_page' => [
                'goal' => __('capell-agent-bridge::admin.starter_create_disabled_draft_goal'),
                'area' => 'pages',
                'operation' => 'create',
                'safety' => 'preview_first',
                'target' => __('capell-agent-bridge::admin.starter_create_disabled_draft_target'),
                'constraints' => __('capell-agent-bridge::admin.starter_create_disabled_draft_constraints'),
                'success_criteria' => __('capell-agent-bridge::admin.starter_create_disabled_draft_done'),
            ],
            'update_draft_content' => [
                'goal' => __('capell-agent-bridge::admin.starter_update_draft_goal'),
                'area' => 'pages',
                'operation' => 'update',
                'safety' => 'preview_first',
                'target' => __('capell-agent-bridge::admin.starter_update_draft_target'),
                'constraints' => __('capell-agent-bridge::admin.starter_update_draft_constraints'),
                'success_criteria' => __('capell-agent-bridge::admin.starter_update_draft_done'),
            ],
            'clear_cache' => [
                'goal' => __('capell-agent-bridge::admin.starter_clear_cache_goal'),
                'area' => 'cache',
                'operation' => 'clear',
                'safety' => 'prepare_confirmation',
                'target' => __('capell-agent-bridge::admin.starter_clear_cache_target'),
                'constraints' => __('capell-agent-bridge::admin.starter_clear_cache_constraints'),
                'success_criteria' => __('capell-agent-bridge::admin.starter_clear_cache_done'),
            ],
            'recommend_packages' => [
                'goal' => __('capell-agent-bridge::admin.starter_recommend_packages_goal'),
                'area' => 'packages',
                'operation' => 'recommend',
                'safety' => 'read_only',
                'target' => __('capell-agent-bridge::admin.starter_recommend_packages_target'),
                'constraints' => __('capell-agent-bridge::admin.starter_recommend_packages_constraints'),
                'success_criteria' => __('capell-agent-bridge::admin.starter_recommend_packages_done'),
            ],
            default => [
                'goal' => __('capell-agent-bridge::admin.starter_inspect_readiness_goal'),
                'area' => 'pages',
                'operation' => 'inspect',
                'safety' => 'read_only',
                'target' => __('capell-agent-bridge::admin.starter_inspect_readiness_target'),
                'constraints' => __('capell-agent-bridge::admin.starter_inspect_readiness_constraints'),
                'success_criteria' => __('capell-agent-bridge::admin.starter_inspect_readiness_done'),
            ],
        };

        $this->data = array_merge($this->data, array_map(static fn (mixed $value): string => (string) $value, $state));
        $this->buildPrompt();
    }

    /** @return array<string, string> */
    public function savedPromptOptions(): array
    {
        $user = $this->user();

        if (! $user instanceof Authenticatable) {
            return [];
        }

        return CapellAgentBridgeSavedPrompt::query()
            ->forUser($user)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->mapWithKeys(static fn (string $name, int|string $id): array => [(string) $id => $name])
            ->all();
    }

    /** @return array<string, string> */
    public function areaOptions(): array
    {
        return CapellAgentBridgePromptBuilderPage::areaOptions();
    }

    /** @return array<string, string> */
    public function operationOptions(): array
    {
        return CapellAgentBridgePromptBuilderPage::operationOptions();
    }

    /** @return array<string, string> */
    public function safetyOptions(): array
    {
        return CapellAgentBridgePromptBuilderPage::safetyOptions();
    }

    private function promptData(): AgentBridgePromptData
    {
        return AgentBridgePromptData::fromArray($this->data);
    }

    private function refreshPreparedPrompt(): void
    {
        $this->preparedPrompt = BuildAgentBridgePromptAction::run($this->promptData());
    }

    private function selectedSavedPrompt(): ?CapellAgentBridgeSavedPrompt
    {
        $user = $this->user();

        if (! $user instanceof Authenticatable || $this->selectedSavedPromptId === null) {
            return null;
        }

        return CapellAgentBridgeSavedPrompt::query()
            ->forUser($user)
            ->find($this->selectedSavedPromptId);
    }

    private function user(): ?Authenticatable
    {
        if (app()->bound('filament')) {
            try {
                return Filament::auth()->user();
            } catch (Throwable) {
                // Package tests can load Filament support without a configured panel.
            }
        }

        $user = auth()->user();

        return $user instanceof Authenticatable ? $user : null;
    }

    private function deepLinkUrl(): string
    {
        try {
            return CapellAgentBridgePromptBuilderPage::getUrl();
        } catch (Throwable) {
            return url('/admin/capell-agent-bridge/prompt-builder');
        }
    }
}
