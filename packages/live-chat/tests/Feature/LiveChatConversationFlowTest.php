<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\LiveChat\Actions\RequestLiveChatHandoffAction;
use Capell\LiveChat\Actions\ResolveLiveChatAvailabilityAction;
use Capell\LiveChat\Actions\StartLiveChatConversationAction;
use Capell\LiveChat\Contracts\LiveChatResponder;
use Capell\LiveChat\Data\IncomingLiveChatMessageData;
use Capell\LiveChat\Data\LiveChatVisitorData;
use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\EscalationReason;
use Capell\LiveChat\Enums\LiveChatIntent;
use Capell\LiveChat\Models\LiveChatAvailabilityException;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatEscalationRule;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Capell\LiveChat\Tests\Fixtures\FailingLiveChatResponder;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-15 10:00:00', 'Europe/London'));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('handles message-first pricing enquiries and syncs captured details to contacts', function (): void {
    $siteId = $this->createLiveChatSite();
    LiveChatKnowledgeSource::query()->create([
        'site_id' => $siteId,
        'type' => 'website',
        'source_key' => 'pricing',
        'title' => 'Pricing page',
        'status' => 'active',
    ]);

    $result = (new StartLiveChatConversationAction)->handle(new IncomingLiveChatMessageData(
        body: 'Can you help with pricing?',
        visitorToken: 'visitor-token',
        visitor: new LiveChatVisitorData(
            name: 'Ava Morgan',
            email: 'ava@example.test',
            company: 'Northstar Studio',
            processingConsent: true,
        ),
        flow: 'message_first',
        timezone: 'Europe/London',
        page: ['url' => 'https://example.test/pricing'],
    ), $siteId);

    $conversation = $result['conversation'];
    $assistantMessage = $result['assistant_message'];

    expect($conversation)->toBeInstanceOf(LiveChatConversation::class)
        ->and($conversation->intent)->toBe(LiveChatIntent::Sales)
        ->and($conversation->visitor_email)->toBe('ava@example.test')
        ->and($conversation->contact_id)->not->toBeNull()
        ->and($assistantMessage->requires_contact)->toBeTrue()
        ->and($assistantMessage->body)->toContain('quick overview')
        ->and(Contact::query()->count())->toBe(1)
        ->and(Lead::query()->count())->toBe(1)
        ->and(ContactActivity::query()->count())->toBe(1);

    $contact = Contact::query()->firstOrFail();
    $lead = Lead::query()->firstOrFail();

    expect($contact->email)->toBe('ava@example.test')
        ->and($contact->display_name)->toBe('Ava Morgan')
        ->and($lead->title)->toContain('Live chat sales enquiry');
});

it('keeps anonymous message-first chats out of contacts until details are useful', function (): void {
    $siteId = $this->createLiveChatSite();

    StartLiveChatConversationAction::run(new IncomingLiveChatMessageData(
        body: 'What are your opening hours?',
        visitorToken: 'anonymous-token',
        flow: 'message_first',
        timezone: 'Europe/London',
    ), $siteId);

    expect(LiveChatConversation::query()->count())->toBe(1)
        ->and(Contact::query()->count())->toBe(0)
        ->and(Lead::query()->count())->toBe(0);
});

it('routes conversations to a human when the assistant responder fails', function (): void {
    app()->bind(LiveChatResponder::class, FailingLiveChatResponder::class);

    $siteId = $this->createLiveChatSite();

    $result = StartLiveChatConversationAction::run(new IncomingLiveChatMessageData(
        body: 'Can someone help with a quote?',
        visitorToken: 'fallback-token',
        visitor: new LiveChatVisitorData(
            name: 'Ava Morgan',
            email: 'ava@example.test',
            processingConsent: true,
        ),
        flow: 'message_first',
        timezone: 'Europe/London',
    ), $siteId);

    $conversation = $result['conversation'];
    $assistantMessage = $result['assistant_message'];

    expect($conversation->status)->toBe(ConversationStatus::WaitingForHuman)
        ->and($conversation->priority->value)->toBe('high')
        ->and($conversation->assignment_queue)->toBe('support')
        ->and($conversation->escalation_reason)->toBe(EscalationReason::RepeatedFailure)
        ->and($conversation->messages()->count())->toBe(2)
        ->and($conversation->contact_id)->not->toBeNull()
        ->and($assistantMessage->requires_contact)->toBeTrue()
        ->and($assistantMessage->body)->toContain('passed this conversation to a person');
});

it('applies after-hours handling with a custom message', function (): void {
    $siteId = $this->createLiveChatSite();
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-13 21:00:00', 'Europe/London'));

    LiveChatAvailabilityException::query()->create([
        'site_id' => $siteId,
        'date' => '2026-06-13',
        'is_available' => false,
        'timezone' => 'Europe/London',
        'message' => 'We are out of the office between 9 and 10.',
    ]);

    $availability = (new ResolveLiveChatAvailabilityAction)->handle($siteId, CarbonImmutable::now('Europe/London'), 'Europe/London');

    expect($availability->available)->toBeFalse()
        ->and($availability->message)->toBe('We are out of the office between 9 and 10.');
});

it('escalates keyword-triggered sensitive conversations and preserves handoff context', function (): void {
    $siteId = $this->createLiveChatSite();
    LiveChatEscalationRule::query()->create([
        'site_id' => $siteId,
        'name' => 'Refund complaints',
        'trigger_type' => 'keyword',
        'trigger_value' => 'refund',
        'route_to' => 'accounts',
        'priority' => 'urgent',
        'is_active' => true,
    ]);

    $result = (new StartLiveChatConversationAction)->handle(new IncomingLiveChatMessageData(
        body: 'I need a refund urgently',
        visitorToken: 'urgent-token',
        visitor: new LiveChatVisitorData(
            name: 'Morgan Customer',
            email: 'morgan@example.test',
            processingConsent: true,
        ),
        flow: 'details_first',
    ), $siteId);

    $conversation = (new RequestLiveChatHandoffAction)->handle($result['conversation']);
    $activityPayload = ContactActivity::query()->latest('id')->firstOrFail()->payload ?? [];

    expect($conversation->status)->toBe(ConversationStatus::WaitingForHuman)
        ->and($conversation->escalation_reason)->toBe(EscalationReason::Keyword)
        ->and($conversation->assignment_queue)->toBe('accounts')
        ->and($conversation->messages()->count())->toBeGreaterThanOrEqual(3)
        ->and($activityPayload['transcript'] ?? [])->toHaveCount(3);
});
