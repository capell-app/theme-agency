<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Filament\Pages;

use BackedEnum;
use Capell\AgentBridge\Actions\BuildAgentBridgePromptAction;
use Capell\AgentBridge\Data\AgentBridgePromptData;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class CapellAgentBridgePromptBuilderPage extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public array $data = [
        'safety' => 'preview_first',
    ];

    public string $preparedPrompt = '';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $slug = 'capell-agent-bridge/prompt-builder';

    protected static ?int $navigationSort = 10;

    protected string $view = 'capell-agent-bridge::filament.pages.prompt-builder';

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-agent-bridge::admin.prompt_builder_navigation');
    }

    /** @return array<string, string> */
    public static function areaOptions(): array
    {
        return [
            'pages' => (string) __('capell-agent-bridge::admin.area_pages'),
            'cache' => (string) __('capell-agent-bridge::admin.area_cache'),
            'packages' => (string) __('capell-agent-bridge::admin.area_packages'),
        ];
    }

    /** @return array<string, string> */
    public static function operationOptions(): array
    {
        return [
            'inspect' => (string) __('capell-agent-bridge::admin.operation_inspect'),
            'create' => (string) __('capell-agent-bridge::admin.operation_create'),
            'update' => (string) __('capell-agent-bridge::admin.operation_update'),
            'disable' => (string) __('capell-agent-bridge::admin.operation_disable'),
            'clear' => (string) __('capell-agent-bridge::admin.operation_clear'),
            'recommend' => (string) __('capell-agent-bridge::admin.operation_recommend'),
        ];
    }

    /** @return array<string, string> */
    public static function safetyOptions(): array
    {
        return [
            'preview_first' => (string) __('capell-agent-bridge::admin.safety_preview_first'),
            'read_only' => (string) __('capell-agent-bridge::admin.safety_read_only'),
            'prepare_confirmation' => (string) __('capell-agent-bridge::admin.safety_prepare_confirmation'),
        ];
    }

    #[Override]
    public function getTitle(): string
    {
        return __('capell-agent-bridge::admin.prompt_builder_title');
    }

    public function mount(): void
    {
        $this->getForm('form')?->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('capell-agent-bridge::admin.intent_section'))
                    ->description(__('capell-agent-bridge::admin.intent_section_description'))
                    ->schema([
                        TextInput::make('goal')
                            ->label(__('capell-agent-bridge::admin.goal'))
                            ->helperText(__('capell-agent-bridge::admin.goal_help'))
                            ->placeholder(__('capell-agent-bridge::admin.goal_placeholder'))
                            ->maxLength(180)
                            ->required(),
                        Grid::make(3)
                            ->schema([
                                Select::make('area')
                                    ->label(__('capell-agent-bridge::admin.area'))
                                    ->options(self::areaOptions())
                                    ->required(),
                                Select::make('operation')
                                    ->label(__('capell-agent-bridge::admin.operation'))
                                    ->options(self::operationOptions())
                                    ->required(),
                                Select::make('safety')
                                    ->label(__('capell-agent-bridge::admin.safety'))
                                    ->options(self::safetyOptions())
                                    ->required(),
                            ]),
                        Textarea::make('target')
                            ->label(__('capell-agent-bridge::admin.target'))
                            ->helperText(__('capell-agent-bridge::admin.target_help'))
                            ->placeholder(__('capell-agent-bridge::admin.target_placeholder'))
                            ->rows(3),
                        Textarea::make('constraints')
                            ->label(__('capell-agent-bridge::admin.constraints'))
                            ->helperText(__('capell-agent-bridge::admin.constraints_help'))
                            ->placeholder(__('capell-agent-bridge::admin.constraints_placeholder'))
                            ->rows(3),
                        Textarea::make('success_criteria')
                            ->label(__('capell-agent-bridge::admin.success_criteria'))
                            ->helperText(__('capell-agent-bridge::admin.success_criteria_help'))
                            ->placeholder(__('capell-agent-bridge::admin.success_criteria_placeholder'))
                            ->rows(3),
                    ]),
            ]);
    }

    public function buildPrompt(): void
    {
        $state = $this->getForm('form')?->getState() ?? $this->data;
        $this->preparedPrompt = BuildAgentBridgePromptAction::run(AgentBridgePromptData::fromArray($state));

        Notification::make('capell_agent-bridge_prompt_ready')
            ->success()
            ->title(__('capell-agent-bridge::admin.prompt_ready'))
            ->send();
    }

    /** @return array<int, Action> */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('buildPrompt')
                ->label(__('capell-agent-bridge::admin.build_prompt'))
                ->icon(Heroicon::OutlinedSparkles)
                ->action('buildPrompt'),
        ];
    }
}
