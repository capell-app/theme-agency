<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromCampaignConversion;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Illuminate\Database\Eloquent\Model;

it('dispatches campaign conversion events with conversion payload metadata', function (): void {
    $actions = new AutomationActionRegistry;
    $rules = new AutomationRuleRegistry;
    $handledPayload = new ArrayObject;

    $actions->registerHandler(AutomationActionType::SendEmail, new class($handledPayload) implements AutomationActionHandler
    {
        /**
         * @param  ArrayObject<string, mixed>  $handledPayload
         */
        public function __construct(private readonly ArrayObject $handledPayload) {}

        public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
        {
            $this->handledPayload->exchangeArray($event->payload);

            return new AutomationActionResultData(
                success: true,
                context: $event->payload,
            );
        }
    });

    $rules->register(new AutomationRuleData(
        key: 'campaign-converted',
        name: 'Campaign converted',
        triggerType: AutomationTriggerType::CampaignConverted,
        actions: [
            new AutomationRuleActionData(
                key: 'notify',
                type: AutomationActionType::SendEmail,
            ),
        ],
    ));

    $conversion = new class extends Model
    {
        protected $table = 'campaign_conversions';
    };
    $conversion->forceFill([
        'id' => 12,
        'campaign_group_id' => 3,
        'campaign_conversion_goal_id' => 4,
        'campaign_landing_page_id' => 5,
        'site_id' => 6,
        'source_type' => 'form_submission',
        'source_id' => 7,
    ]);
    $conversion->exists = true;

    $results = (new DispatchAutomationFromCampaignConversion(
        new DispatchAutomationTriggerAction($rules, $actions),
    ))->handle((object) ['conversion' => $conversion]);

    expect($results)->toBeNull()
        ->and($handledPayload->getArrayCopy())->toMatchArray([
            'conversion_id' => 12,
            'campaign_group_id' => 3,
            'campaign_conversion_goal_id' => 4,
            'campaign_landing_page_id' => 5,
            'site_id' => 6,
            'source_type' => 'form_submission',
            'source_id' => 7,
        ]);
});
