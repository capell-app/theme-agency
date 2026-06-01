<?php

declare(strict_types=1);

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\Handlers\SendEmailAutomationActionHandler;
use Capell\EmailStudio\Actions\SendEmailAction;
use Capell\EmailStudio\Data\SendEmailData;
use Capell\EmailStudio\Models\EmailMessage;

it('provides a native email studio automation handler', function (): void {
    expect(new SendEmailAutomationActionHandler)->toBeInstanceOf(AutomationActionHandler::class);
});

it('reports missing email template keys before invoking email studio', function (): void {
    $result = (new SendEmailAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['email' => 'person@example.test'],
        ),
        action: new AutomationRuleActionData(
            key: 'send-email',
            type: AutomationActionType::SendEmail,
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('An Email Studio template key is required.');
});

it('reports missing email recipients before invoking email studio', function (): void {
    $result = (new SendEmailAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['site_id' => 1],
        ),
        action: new AutomationRuleActionData(
            key: 'send-email',
            type: AutomationActionType::SendEmail,
            settings: ['template_key' => 'welcome'],
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('At least one email recipient is required.');
});

it('sends email studio payloads with resolved recipients context headers and trigger metadata', function (): void {
    $sentEmail = new class
    {
        public ?SendEmailData $data = null;
    };

    app()->instance(SendEmailAction::class, new class($sentEmail) extends SendEmailAction
    {
        public function __construct(private readonly object $sentEmail) {}

        public function handle(SendEmailData $data): EmailMessage
        {
            $this->sentEmail->data = $data;

            $message = new EmailMessage;
            $message->forceFill(['id' => 123]);
            $message->exists = true;

            return $message;
        }
    });

    $result = (new SendEmailAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.submission',
            sourceId: '456',
            payload: [
                'email' => 'fallback@example.test',
                'site_id' => '7',
                'site_scope_key' => 'site:7',
                'first_name' => 'Ada',
                'locale' => 'en',
            ],
        ),
        action: new AutomationRuleActionData(
            key: 'send-email',
            type: AutomationActionType::SendEmail,
            settings: [
                'template_key' => 'welcome',
                'to' => [
                    'editor@example.test',
                    ['email' => 'member@example.test', 'name' => 'Member'],
                    ['address' => 'named@example.test', 'name' => 'Named'],
                    ['email' => ''],
                    123,
                ],
                'cc' => ['cc@example.test'],
                'bcc' => [['address' => 'bcc@example.test']],
                'email_profile_id' => '9',
                'variables' => ['plan' => 'pro'],
                'headers' => [
                    'X-Campaign' => 'Launch',
                    ['name' => 'X-Source', 'value' => 'Automation'],
                    ['name' => '', 'value' => 'Ignored'],
                ],
                'queue' => false,
            ],
        ),
    );

    expect($result->success)->toBeTrue()
        ->and($result->context['email_message_id'])->toBe(123)
        ->and($result->context['template_key'])->toBe('welcome')
        ->and($sentEmail->data)->toBeInstanceOf(SendEmailData::class)
        ->and($sentEmail->data?->templateKey)->toBe('welcome')
        ->and($sentEmail->data?->siteId)->toBe(7)
        ->and($sentEmail->data?->siteScopeKey)->toBe('site:7')
        ->and($sentEmail->data?->emailProfileId)->toBe(9)
        ->and($sentEmail->data?->triggeredByType)->toBe('form-builder.submission')
        ->and($sentEmail->data?->triggeredById)->toBe(456)
        ->and($sentEmail->data?->queue)->toBeFalse()
        ->and($sentEmail->data?->locale)->toBe('en')
        ->and($sentEmail->data?->variables)->toMatchArray(['email' => 'fallback@example.test', 'first_name' => 'Ada', 'plan' => 'pro']);

    $to = $sentEmail->data?->to->items() ?? [];
    $headers = $sentEmail->data?->headers->items() ?? [];

    expect($to)->toHaveCount(3)
        ->and($to[0]->email)->toBe('editor@example.test')
        ->and($to[1]->name)->toBe('Member')
        ->and($to[2]->email)->toBe('named@example.test')
        ->and($sentEmail->data?->cc->items()[0]->email)->toBe('cc@example.test')
        ->and($sentEmail->data?->bcc->items()[0]->email)->toBe('bcc@example.test')
        ->and($headers)->toHaveCount(2)
        ->and($headers[0]->name)->toBe('X-Campaign')
        ->and($headers[1]->value)->toBe('Automation');
});

it('falls back to event email and site scope when optional email settings are omitted', function (): void {
    $sentEmail = new class
    {
        public ?SendEmailData $data = null;
    };

    app()->instance(SendEmailAction::class, new class($sentEmail) extends SendEmailAction
    {
        public function __construct(private readonly object $sentEmail) {}

        public function handle(SendEmailData $data): EmailMessage
        {
            $this->sentEmail->data = $data;

            $message = new EmailMessage;
            $message->forceFill(['id' => 321]);
            $message->exists = true;

            return $message;
        }
    });

    $result = (new SendEmailAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.submission',
            sourceId: 'not-numeric',
            payload: [
                'email' => 'subscriber@example.test',
                'site_id' => 12,
            ],
        ),
        action: new AutomationRuleActionData(
            key: 'send-email',
            type: AutomationActionType::SendEmail,
            settings: [
                'email_template_key' => 'receipt',
                'variables' => 'ignored',
                'headers' => 'ignored',
            ],
        ),
    );

    expect($result->success)->toBeTrue()
        ->and($sentEmail->data?->templateKey)->toBe('receipt')
        ->and($sentEmail->data?->siteScopeKey)->toBe('site:12')
        ->and($sentEmail->data?->triggeredById)->toBeNull()
        ->and($sentEmail->data?->to->items()[0]->email)->toBe('subscriber@example.test')
        ->and($sentEmail->data?->headers->items())->toBe([])
        ->and($sentEmail->data?->variables)->toMatchArray(['email' => 'subscriber@example.test', 'site_id' => 12]);
});
