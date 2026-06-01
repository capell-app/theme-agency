<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support\Handlers;

use BackedEnum;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Support\Handlers\Concerns\ResolvesContacts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Override;

final class SubscribeUserAutomationActionHandler implements AutomationActionHandler
{
    use ResolvesContacts;

    #[Override]
    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
    {
        $subscriberDataClass = 'Capell\\Newsletter\\Data\\SubscriberData';
        $consentEvidenceDataClass = 'Capell\\Newsletter\\Data\\ConsentEvidenceData';
        $subscriberStatusClass = 'Capell\\Newsletter\\Enums\\SubscriberStatus';
        $consentEventTypeClass = 'Capell\\Newsletter\\Enums\\ConsentEventType';
        $upsertSubscriberActionClass = 'Capell\\Newsletter\\Actions\\UpsertSubscriberAction';

        if (
            ! class_exists($subscriberDataClass)
            || ! class_exists($consentEvidenceDataClass)
            || ! class_exists($subscriberStatusClass)
            || ! class_exists($consentEventTypeClass)
            || ! class_exists($upsertSubscriberActionClass)
        ) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.newsletter_unavailable', 'Newsletter is not available.'),
            );
        }

        $siteId = $this->siteId($event, $action);
        $email = $this->stringSetting($event, $action, 'email');

        if ($siteId === null) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.newsletter_site_required', 'A site id is required to subscribe a user.'),
            );
        }

        if ($email === null) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.newsletter_email_required', 'An email address is required to subscribe a user.'),
            );
        }

        /** @var Model $subscriber */
        $subscriber = $upsertSubscriberActionClass::run(
            new $subscriberDataClass(
                siteId: $siteId,
                email: $email,
                status: constant($subscriberStatusClass . '::Subscribed'),
                firstName: $this->stringSetting($event, $action, 'first_name'),
                lastName: $this->stringSetting($event, $action, 'last_name'),
                profile: $this->profile($event, $action),
                sourceFormId: $this->sourceFormId($event, $action),
                sourceFormHandle: $this->stringSetting($event, $action, 'form_handle'),
            ),
            new $consentEvidenceDataClass(
                sourceType: $event->sourceType,
                sourceId: $event->sourceId,
                consentText: $this->stringSetting($event, $action, 'consent_text'),
                consentVersion: $this->stringSetting($event, $action, 'consent_version'),
                ipAddress: $this->stringSetting($event, $action, 'ip_address'),
                userAgent: $this->stringSetting($event, $action, 'user_agent'),
                url: $this->stringSetting($event, $action, 'url'),
                referer: $this->stringSetting($event, $action, 'referer'),
                extra: [
                    'automation_action_key' => $action->key,
                    'trigger_type' => $event->triggerType->value,
                ],
            ),
            constant($consentEventTypeClass . '::FormCapture'),
        );

        $subscriber = $this->applyTags($subscriber, $action);

        return new AutomationActionResultData(
            success: true,
            context: [
                'subscriber_id' => $subscriber->getKey(),
                'subscriber_status' => $this->subscriberStatus($subscriber),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function profile(AutomationTriggerEventData $event, AutomationRuleActionData $action): array
    {
        return [
            ...($this->arraySetting($action, 'profile') ?? []),
            ...Arr::only($event->payload, ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id']),
        ];
    }

    private function sourceFormId(AutomationTriggerEventData $event, AutomationRuleActionData $action): ?int
    {
        $formId = $action->settings['source_form_id'] ?? $event->payload['form_id'] ?? null;

        return is_numeric($formId) && (int) $formId > 0 ? (int) $formId : null;
    }

    private function applyTags(Model $subscriber, AutomationRuleActionData $action): Model
    {
        $applyTagsActionClass = 'Capell\\Newsletter\\Actions\\ApplyNewsletterTagsAction';

        if (! class_exists($applyTagsActionClass)) {
            return $subscriber;
        }

        $tagIds = Arr::wrap($action->settings['tag_ids'] ?? []);

        if ($tagIds === []) {
            return $subscriber;
        }

        /** @var Model $taggedSubscriber */
        $taggedSubscriber = $applyTagsActionClass::run(
            subscriber: $subscriber,
            tagIds: $tagIds,
            replace: (bool) ($action->settings['replace_tags'] ?? false),
        );

        return $taggedSubscriber;
    }

    private function subscriberStatus(Model $subscriber): ?string
    {
        $status = $subscriber->getAttribute('status');

        if ($status instanceof BackedEnum) {
            return is_string($status->value) ? $status->value : null;
        }

        return is_string($status) ? $status : null;
    }
}
