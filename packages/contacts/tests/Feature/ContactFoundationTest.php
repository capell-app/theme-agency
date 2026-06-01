<?php

declare(strict_types=1);

use Capell\Contacts\Actions\FindOrCreateContactAction;
use Capell\Contacts\Actions\RecordContactActivityAction;
use Capell\Contacts\Actions\SyncContactSourceRecordAction;
use Capell\Contacts\Actions\TagContactAction;
use Capell\Contacts\Data\ContactActivityData;
use Capell\Contacts\Data\ContactIdentityData;
use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\LeadStatus;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Capell\Contacts\Tests\ContactsTestCase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;

require_once __DIR__ . '/../autoload.php';

uses(ContactsTestCase::class);

it('loads the contacts foundation tables', function (): void {
    expect(Schema::hasTable('contacts'))->toBeTrue()
        ->and(Schema::hasTable('contact_organisations'))->toBeTrue()
        ->and(Schema::hasTable('contact_organisation_memberships'))->toBeTrue()
        ->and(Schema::hasTable('contact_leads'))->toBeTrue()
        ->and(Schema::hasTable('contact_activities'))->toBeTrue()
        ->and(Schema::hasColumn('contacts', 'source_key'))->toBeTrue()
        ->and(Schema::hasColumn('contacts', 'source_identifier'))->toBeTrue()
        ->and(Schema::hasColumn('contacts', 'source_identifier_hash'))->toBeTrue();
});

it('creates and reuses contacts by normalized email', function (): void {
    $siteId = $this->createContactsSite();

    $firstContact = FindOrCreateContactAction::run(new ContactIdentityData(
        siteId: $siteId,
        email: 'Person@Example.test',
        firstName: 'Ada',
        profile: ['source' => 'form_builder'],
    ));

    $secondContact = FindOrCreateContactAction::run(new ContactIdentityData(
        siteId: $siteId,
        email: ' person@example.test ',
        lastName: 'Lovelace',
        profile: ['newsletter' => true],
    ));

    expect($secondContact->is($firstContact))->toBeTrue()
        ->and(Contact::query()->count())->toBe(1)
        ->and($secondContact->fresh()->email_hash)->toBe(Contact::emailHash('person@example.test'))
        ->and($secondContact->fresh()->profile)->toBe([
            'source' => 'form_builder',
            'newsletter' => true,
        ]);
});

it('reuses contacts by package source identity when email is not available', function (): void {
    $siteId = $this->createContactsSite();

    $firstContact = FindOrCreateContactAction::run(new ContactIdentityData(
        siteId: $siteId,
        displayName: 'Anonymous subscriber',
        sourceKey: 'newsletter',
        sourceIdentifier: 'Subscriber-123',
    ));

    $secondContact = FindOrCreateContactAction::run(new ContactIdentityData(
        siteId: $siteId,
        firstName: 'Ada',
        sourceKey: 'newsletter',
        sourceIdentifier: ' subscriber-123 ',
    ));

    expect($secondContact->is($firstContact))->toBeTrue()
        ->and(Contact::query()->count())->toBe(1)
        ->and($secondContact->fresh()->source_identifier_hash)->toBe(Contact::sourceIdentifierHash('subscriber-123'));
});

it('syncs source records into contacts, tags, leads, and activities', function (): void {
    $siteId = $this->createContactsSite();
    $occurredAt = Date::now()->subMinute();

    $result = SyncContactSourceRecordAction::run(new ContactSourceRecordData(
        siteId: $siteId,
        sourceKey: 'form_builder',
        sourceIdentifier: 'submission-1001',
        email: 'lead@example.test',
        displayName: 'Lead Example',
        profile: ['form' => 'Contact'],
        tags: [' Form Lead ', 'VIP'],
        activityType: ContactActivityType::FormSubmission,
        activitySummary: 'Submitted contact form',
        activityPayload: ['form_id' => 44],
        leadTitle: 'Contact form enquiry',
        leadStatus: LeadStatus::Open,
        occurredAt: $occurredAt,
    ));

    $contact = $result->contact->fresh();

    expect($contact)->not->toBeNull()
        ->and($contact->email_hash)->toBe(Contact::emailHash('lead@example.test'))
        ->and($contact->source_key)->toBe('form_builder')
        ->and($contact->source_identifier_hash)->toBe(Contact::sourceIdentifierHash('submission-1001'))
        ->and($contact->profile)->toMatchArray([
            'form' => 'Contact',
            'tags' => ['form lead', 'vip'],
            'sources' => [
                'form_builder' => [
                    'identifier' => 'submission-1001',
                    'last_seen_at' => $occurredAt->toISOString(),
                ],
            ],
        ])
        ->and($result->lead)->toBeInstanceOf(Lead::class)
        ->and($result->lead?->status)->toBe(LeadStatus::Open)
        ->and($result->lead?->context)->toMatchArray([
            'source_key' => 'form_builder',
            'source_identifier' => 'submission-1001',
            'payload' => ['form_id' => 44],
        ])
        ->and($result->activity?->type)->toBe(ContactActivityType::FormSubmission)
        ->and($result->activity?->payload)->toMatchArray([
            'source_key' => 'form_builder',
            'source_identifier' => 'submission-1001',
            'form_id' => 44,
        ]);
});

it('relates contacts to organisations, leads, and activity records', function (): void {
    $siteId = $this->createContactsSite();
    $contact = FindOrCreateContactAction::run(new ContactIdentityData(
        siteId: $siteId,
        email: 'buyer@example.test',
        displayName: 'Buyer Example',
    ));

    $organisation = Organisation::query()->create([
        'site_id' => $siteId,
        'name' => 'Example Ltd',
        'domain' => 'example.test',
    ]);

    $contact->organisations()->attach($organisation, [
        'role' => 'buyer',
        'is_primary' => true,
    ]);

    $lead = Lead::query()->create([
        'site_id' => $siteId,
        'contact_id' => $contact->getKey(),
        'organisation_id' => $organisation->getKey(),
        'title' => 'Website enquiry',
        'status' => LeadStatus::Open,
        'context' => ['channel' => 'campaign_studio'],
        'captured_at' => now(),
    ]);

    $activity = RecordContactActivityAction::run(
        contact: $contact,
        activityData: new ContactActivityData(
            type: ContactActivityType::CampaignConversion,
            summary: 'Converted on landing page',
            payload: ['goal' => 'demo-request'],
        ),
        lead: $lead,
        organisation: $organisation,
    );

    expect($organisation->name_key)->toBe('example-ltd')
        ->and($contact->organisations()->count())->toBe(1)
        ->and($contact->leads()->count())->toBe(1)
        ->and($contact->activities()->count())->toBe(1)
        ->and($activity->lead->is($lead))->toBeTrue()
        ->and($activity->payload)->toBe(['goal' => 'demo-request']);
});

it('stores normalized unique tags on contact profiles', function (): void {
    $siteId = $this->createContactsSite();
    $contact = FindOrCreateContactAction::run(new ContactIdentityData(
        siteId: $siteId,
        email: 'person@example.test',
        profile: ['tags' => ['Lead']],
    ));

    $taggedContact = TagContactAction::run($contact, [' lead ', 'VIP']);

    expect($taggedContact->fresh()->profile)->toMatchArray([
        'tags' => ['lead', 'vip'],
    ]);
});
