<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Listeners;

use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Illuminate\Database\Eloquent\Model;

final class DispatchAutomationFromCampaignConversion
{
    public function __construct(
        private readonly QueueAutomationTriggerAction $queueAutomationTrigger,
    ) {}

    public function handle(object $event): void
    {
        $conversion = $this->modelProperty($event, 'conversion');

        $this->queueAutomationTrigger->handle(new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::CampaignConverted,
            sourceType: 'campaign-studio.conversion',
            sourceId: $conversion instanceof Model ? (string) $conversion->getKey() : null,
            payload: [
                'conversion_id' => $conversion instanceof Model ? $conversion->getKey() : null,
                'campaign_group_id' => $conversion instanceof Model ? $conversion->getAttribute('campaign_group_id') : null,
                'campaign_conversion_goal_id' => $conversion instanceof Model ? $conversion->getAttribute('campaign_conversion_goal_id') : null,
                'campaign_landing_page_id' => $conversion instanceof Model ? $conversion->getAttribute('campaign_landing_page_id') : null,
                'site_id' => $conversion instanceof Model ? $conversion->getAttribute('site_id') : null,
                'source_type' => $conversion instanceof Model ? $conversion->getAttribute('source_type') : null,
                'source_id' => $conversion instanceof Model ? $conversion->getAttribute('source_id') : null,
            ],
        ), siteId: $this->integerAttribute($conversion, 'site_id'), deduplicationKey: $this->deduplicationKey($conversion));
    }

    private function modelProperty(object $event, string $property): ?Model
    {
        $value = get_object_vars($event)[$property] ?? null;

        return $value instanceof Model ? $value : null;
    }

    private function integerAttribute(?Model $model, string $attribute): ?int
    {
        $value = $model instanceof Model ? $model->getAttribute($attribute) : null;

        return is_int($value) || is_string($value) && is_numeric($value) ? (int) $value : null;
    }

    private function deduplicationKey(?Model $model): ?string
    {
        if (! $model instanceof Model) {
            return null;
        }

        return implode(':', ['campaign-studio.conversion', (string) $model->getKey()]);
    }
}
