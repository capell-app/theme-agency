<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Filament\Resources\AutomationRules\Pages;

use Capell\AutomationStudio\Actions\DryRunAutomationRulesAction;
use Capell\AutomationStudio\Data\AutomationRuleDryRunResultData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Filament\Resources\AutomationRules\AutomationRuleResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListAutomationRules extends ListRecords
{
    protected static string $resource = AutomationRuleResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('dryRun')
                ->label(__('capell-automation-studio::generic.dry_run.action'))
                ->icon('heroicon-o-beaker')
                ->form([
                    Select::make('trigger_type')
                        ->label(__('capell-automation-studio::generic.fields.trigger'))
                        ->options(collect(AutomationTriggerType::cases())
                            ->mapWithKeys(static fn (AutomationTriggerType $type): array => [$type->value => $type->getLabel()])
                            ->all())
                        ->required(),
                    KeyValue::make('payload')
                        ->label(__('capell-automation-studio::generic.dry_run.payload'))
                        ->keyLabel(__('capell-automation-studio::generic.dry_run.payload_key'))
                        ->valueLabel(__('capell-automation-studio::generic.dry_run.payload_value')),
                ])
                ->action(function (array $data): void {
                    $results = DryRunAutomationRulesAction::make()->handle(new AutomationTriggerEventData(
                        triggerType: AutomationTriggerType::from((string) $data['trigger_type']),
                        sourceType: 'automation-studio.dry-run',
                        payload: $this->payloadFromFormData($data['payload'] ?? []),
                    ));
                    $actionCount = collect($results)
                        ->sum(static fn (AutomationRuleDryRunResultData $result): int => $result->actionCount());

                    Notification::make('automation-studio-dry-run-completed')
                        ->title(__('capell-automation-studio::generic.dry_run.completed'))
                        ->body(__('capell-automation-studio::generic.dry_run.summary', [
                            'rules' => count($results),
                            'actions' => $actionCount,
                        ]))
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromFormData(mixed $payload): array
    {
        return is_array($payload) ? $payload : [];
    }
}
