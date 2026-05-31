<?php

declare(strict_types=1);

use Capell\Contacts\Actions\FindOrCreateContactAction;
use Capell\Contacts\Actions\RecordContactActivityAction;
use Capell\Contacts\Data\ContactActivityData;
use Capell\Contacts\Data\ContactIdentityData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\LeadStatus;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Capell\Contacts\Tests\ContactsTestCase;
use Illuminate\Support\Facades\Schema;

require_once __DIR__ . '/../autoload.php';

uses(ContactsTestCase::class);

it('loads the contacts foundation tables', function (): void {
    expect(Schema::hasTable('contacts'))->toBeTrue()
        ->and(Schema::hasTable('contact_organisations'))->toBeTrue()
        ->and(Schema::hasTable('contact_organisation_memberships'))->toBeTrue()
        ->and(Schema::hasTable('contact_leads'))->toBeTrue()
        ->and(Schema::hasTable('contact_activities'))->toBeTrue();
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
