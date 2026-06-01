<?php

declare(strict_types=1);

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\Handlers\SubscribeUserAutomationActionHandler;
use Capell\Newsletter\Actions\ApplyNewsletterTagsAction;
use Capell\Newsletter\Actions\UpsertSubscriberAction;
use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Data\SubscriberData;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\Subscriber;

it('provides a native newsletter subscription handler', function (): void {
    expect(new SubscribeUserAutomationActionHandler)->toBeInstanceOf(AutomationActionHandler::class);
});

it('reports missing site ids before subscribing users', function (): void {
    $result = (new SubscribeUserAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['email' => 'person@example.test'],
        ),
        action: new AutomationRuleActionData(
            key: 'subscribe-user',
            type: AutomationActionType::SubscribeUser,
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('A site id is required to subscribe a user.');
});

it('reports missing email addresses before subscribing users', function (): void {
    $result = (new SubscribeUserAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['site_id' => 1],
        ),
        action: new AutomationRuleActionData(
            key: 'subscribe-user',
            type: AutomationActionType::SubscribeUser,
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('An email address is required to subscribe a user.');
});

it('subscribes users with profile consent source metadata and newsletter tags', function (): void {
    $subscription = new SubscribeUserAutomationActionProbe;

    app()->instance(UpsertSubscriberAction::class, new class($subscription) extends UpsertSubscriberAction
    {
        public function __construct(private readonly SubscribeUserAutomationActionProbe $subscription) {}

        public function handle(
            SubscriberData $data,
            ?ConsentEvidenceData $evidence = null,
            ConsentEventType $eventType = ConsentEventType::FormCapture,
            bool $recordConsentEvent = true,
        ): Subscriber {
            $this->subscription->subscriberData = $data;
            $this->subscription->evidenceData = $evidence;
            $this->subscription->eventType = $eventType;

            $subscriber = new Subscriber;
            $subscriber->forceFill([
                'id' => 77,
                'status' => SubscriberStatus::Subscribed,
            ]);
            $subscriber->exists = true;

            return $subscriber;
        }
    });

    app()->instance(ApplyNewsletterTagsAction::class, new class($subscription) extends ApplyNewsletterTagsAction
    {
        public function __construct(private readonly SubscribeUserAutomationActionProbe $subscription) {}

        /** @param  array<int, int|string>  $tagIds */
        public function handle(Subscriber $subscriber, array $tagIds, bool $replace = false): Subscriber
        {
            $this->subscription->tagIds = $tagIds;
            $this->subscription->replaceTags = $replace;

            return $subscriber;
        }
    });

    $result = (new SubscribeUserAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.submission',
            sourceId: 'abc-123',
            payload: [
                'site_id' => '12',
                'email' => 'person@example.test',
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'form_id' => '45',
                'form_handle' => 'signup',
                'utm_source' => 'newsletter',
                'utm_medium' => 'email',
                'utm_campaign' => 'launch',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Pest',
                'url' => 'https://example.test/signup',
                'referer' => 'https://example.test/',
            ],
        ),
        action: new AutomationRuleActionData(
            key: 'subscribe-user',
            type: AutomationActionType::SubscribeUser,
            settings: [
                'profile' => ['segment' => 'founders'],
                'consent_text' => 'Please send updates.',
                'consent_version' => 'v2',
                'tag_ids' => ['3', 4],
                'replace_tags' => true,
            ],
        ),
    );

    expect($result->success)->toBeTrue()
        ->and($result->context['subscriber_id'])->toBe(77)
        ->and($result->context['subscriber_status'])->toBe('subscribed')
        ->and($subscription->subscriberData)->toBeInstanceOf(SubscriberData::class)
        ->and($subscription->subscriberData?->siteId)->toBe(12)
        ->and($subscription->subscriberData?->email)->toBe('person@example.test')
        ->and($subscription->subscriberData?->status)->toBe(SubscriberStatus::Subscribed)
        ->and($subscription->subscriberData?->firstName)->toBe('Ada')
        ->and($subscription->subscriberData?->sourceFormId)->toBe(45)
        ->and($subscription->subscriberData?->sourceFormHandle)->toBe('signup')
        ->and($subscription->subscriberData?->profile)->toMatchArray([
            'segment' => 'founders',
            'utm_source' => 'newsletter',
            'utm_campaign' => 'launch',
        ])
        ->and($subscription->evidenceData?->sourceType)->toBe('form-builder.submission')
        ->and($subscription->evidenceData?->sourceId)->toBe('abc-123')
        ->and($subscription->evidenceData?->consentText)->toBe('Please send updates.')
        ->and($subscription->evidenceData?->extra)->toBe([
            'automation_action_key' => 'subscribe-user',
            'trigger_type' => 'form_submitted',
        ])
        ->and($subscription->eventType)->toBe(ConsentEventType::FormCapture)
        ->and($subscription->tagIds)->toBe(['3', 4])
        ->and($subscription->replaceTags)->toBeTrue();
});

it('uses action settings over event payload for subscriber identity fields', function (): void {
    $subscription = new SubscribeUserAutomationActionProbe;

    app()->instance(UpsertSubscriberAction::class, new class($subscription) extends UpsertSubscriberAction
    {
        public function __construct(private readonly SubscribeUserAutomationActionProbe $subscription) {}

        public function handle(
            SubscriberData $data,
            ?ConsentEvidenceData $evidence = null,
            ConsentEventType $eventType = ConsentEventType::FormCapture,
            bool $recordConsentEvent = true,
        ): Subscriber {
            $this->subscription->subscriberData = $data;

            $subscriber = new Subscriber;
            $subscriber->forceFill([
                'id' => 78,
                'status' => 'subscribed',
            ]);
            $subscriber->exists = true;

            return $subscriber;
        }
    });

    $result = (new SubscribeUserAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.submission',
            payload: [
                'site_id' => 1,
                'email' => 'payload@example.test',
                'form_id' => 'ignored',
            ],
        ),
        action: new AutomationRuleActionData(
            key: 'subscribe-user',
            type: AutomationActionType::SubscribeUser,
            settings: [
                'site_id' => '2',
                'email' => 'settings@example.test',
                'source_form_id' => '9',
            ],
        ),
    );

    expect($result->success)->toBeTrue()
        ->and($subscription->subscriberData?->siteId)->toBe(2)
        ->and($subscription->subscriberData?->email)->toBe('settings@example.test')
        ->and($subscription->subscriberData?->sourceFormId)->toBe(9);
});

final class SubscribeUserAutomationActionProbe
{
    public ?SubscriberData $subscriberData = null;

    public ?ConsentEvidenceData $evidenceData = null;

    public ?ConsentEventType $eventType = null;

    /** @var array<int, int|string> */
    public array $tagIds = [];

    public bool $replaceTags = false;
}
