<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Listeners;

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Illuminate\Database\Eloquent\Model;

final class DispatchAutomationFromCampaignConversion
{
    public function __construct(
        private readonly DispatchAutomationTriggerAction $dispatchAutomationTrigger,
    ) {}

    public function handle(object $event): void
    {
        $conversion = $this->modelProperty($event, 'conversion');

        $this->dispatchAutomationTrigger->handle(new AutomationTriggerEventData(
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
        ));
    }

    private function modelProperty(object $event, string $property): ?Model
    {
        $value = get_object_vars($event)[$property] ?? null;

        return $value instanceof Model ? $value : null;
    }
}
