<?php

declare(strict_types=1);

use Capell\AccessGate\Events\RegistrationApproved;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\Registration;
use Capell\CampaignStudio\Events\CampaignConverted;
use Capell\CampaignStudio\Models\CampaignConversion;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Events\CommentCreated;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Contacts\Actions\AnonymizeContactAction;
use Capell\Contacts\Actions\BuildContactPrivacyExportAction;
use Capell\Contacts\Actions\FindOrCreateContactAction;
use Capell\Contacts\Actions\RecordContactActivityAction;
use Capell\Contacts\Actions\SyncAccessGateRegistrationContactAction;
use Capell\Contacts\Actions\SyncCampaignConversionContactAction;
use Capell\Contacts\Actions\SyncCommentContactAction;
use Capell\Contacts\Actions\SyncContactSourceRecordAction;
use Capell\Contacts\Actions\SyncEventRegistrationContactAction;
use Capell\Contacts\Actions\SyncFormSubmissionContactAction;
use Capell\Contacts\Actions\SyncShopifyCustomerContactAction;
use Capell\Contacts\Actions\TagContactAction;
use Capell\Contacts\Data\ContactActivityData;
use Capell\Contacts\Data\ContactIdentityData;
use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\ContactStatus;
use Capell\Contacts\Enums\LeadStatus;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Capell\Contacts\Tests\ContactsTestCase;
use Capell\Events\Events\EventRegistrationCreated;
use Capell\Events\Models\Event as EventModel;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Capell\FormBuilder\Events\FormSubmitted;
use Capell\FormBuilder\Models\Form;
use Capell\FormBuilder\Models\Submission;
use Capell\Newsletter\Actions\SyncNewsletterSubscriberContactAction;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\Subscriber;
use Capell\ShopifyCommerce\Events\ShopifyCustomerSynced;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

require_once __DIR__ . '/../autoload.php';

uses(ContactsTestCase::class);

beforeEach(function (): void {
    Relation::morphMap([
        'access_gate_registration' => Registration::class,
        'campaign_conversion' => CampaignConversion::class,
        'comment' => Comment::class,
        'event_registration' => EventRegistration::class,
        'form_builder_submission' => Submission::class,
        'newsletter_subscriber' => Subscriber::class,
        'shopify_customer' => ShopifyCustomer::class,
    ], merge: true);
});

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

it('syncs form builder submissions into contacts through the source adapter', function (): void {
    $siteId = $this->createContactsSite();
    $submittedAt = Date::now()->subMinutes(5);
    $form = new Form;
    $form->exists = true;
    $form->forceFill([
        'id' => 44,
        'site_id' => $siteId,
        'name' => 'Contact',
        'handle' => 'contact',
    ]);
    $submission = new Submission;
    $submission->exists = true;
    $submission->forceFill([
        'id' => 1001,
        'site_id' => $siteId,
        'submitted_at' => $submittedAt,
    ]);

    $result = SyncFormSubmissionContactAction::run(new FormSubmitted(
        form: $form,
        submission: $submission,
        payload: [
            'email' => ' Lead@Example.test ',
            'first_name' => 'Lead',
            'last_name' => 'Example',
            'message' => 'Please call me.',
        ],
    ));

    expect($result)->not->toBeNull()
        ->and($result?->contact->fresh()->email_hash)->toBe(Contact::emailHash('lead@example.test'))
        ->and($result?->contact->fresh()->profile)->toMatchArray([
            'form_builder' => [
                'form_id' => 44,
                'form_handle' => 'contact',
                'form_name' => 'Contact',
                'submission_id' => 1001,
            ],
        ])
        ->and($result?->lead)->toBeInstanceOf(Lead::class)
        ->and($result?->lead?->title)->toBe('Contact form submission')
        ->and($result?->activity?->type)->toBe(ContactActivityType::FormSubmission)
        ->and($result?->activity?->summary)->toBe('Submitted Contact form')
        ->and($result?->activity?->payload)->toMatchArray([
            'form_id' => 44,
            'form_handle' => 'contact',
            'submission_id' => 1001,
            'fields' => ['email', 'first_name', 'last_name', 'message'],
        ]);
});

it('registers the form builder submission listener when form builder is available', function (): void {
    expect(Event::hasListeners(FormSubmitted::class))->toBeTrue();
});

it('syncs newsletter subscribers into contacts through the source adapter', function (): void {
    $siteId = $this->createContactsSite();
    $updatedAt = Date::now()->subMinutes(8);

    $subscriber = new Subscriber;
    $subscriber->exists = true;
    $subscriber->forceFill([
        'id' => 3001,
        'site_id' => $siteId,
        'email' => 'Subscriber@Example.test',
        'first_name' => 'Newsletter',
        'last_name' => 'Reader',
        'status' => SubscriberStatus::Subscribed,
        'source_form_id' => 44,
        'source_form_handle' => 'newsletter-footer',
        'updated_at' => $updatedAt,
    ]);

    $result = SyncNewsletterSubscriberContactAction::run($subscriber);

    expect($result)->not->toBeNull()
        ->and($result?->contact->fresh()->email_hash)->toBe(Contact::emailHash('subscriber@example.test'))
        ->and($result?->contact->fresh()->profile)->toMatchArray([
            'newsletter' => [
                'subscriber_id' => 3001,
                'status' => 'subscribed',
                'source_form_id' => 44,
                'source_form_handle' => 'newsletter-footer',
            ],
        ])
        ->and($result?->lead)->toBeNull()
        ->and($result?->activity?->type)->toBe(ContactActivityType::NewsletterSubscription)
        ->and($result?->activity?->payload)->toMatchArray([
            'subscriber_id' => 3001,
            'status' => 'subscribed',
            'source_form_id' => 44,
            'source_form_handle' => 'newsletter-footer',
        ]);
});

it('syncs approved access gate registrations into contacts through the source adapter', function (): void {
    $siteId = $this->createContactsSite();
    $approvedAt = Date::now()->subMinutes(2);

    $area = new Area;
    $area->exists = true;
    $area->forceFill([
        'id' => 50,
        'site_id' => $siteId,
        'key' => 'vip-library',
        'name' => 'VIP Library',
    ]);

    $registration = new Registration;
    $registration->exists = true;
    $registration->forceFill([
        'id' => 501,
        'access_area_id' => 50,
        'email' => 'VIP@Example.test',
        'email_normalized' => 'vip@example.test',
        'requested_url' => 'https://example.test/library',
        'requested_host' => 'example.test',
        'field_values' => [
            'first_name' => ['value' => 'Vip', 'metadata' => []],
            'last_name' => ['value' => 'Reader', 'metadata' => []],
            'phone' => ['value' => '+44 20 0000 0000', 'metadata' => []],
        ],
        'requested_at' => $approvedAt->copy()->subHour(),
        'approved_at' => $approvedAt,
    ]);
    $registration->setRelation('area', $area);

    $result = SyncAccessGateRegistrationContactAction::run(new RegistrationApproved($registration));

    expect($result)->not->toBeNull()
        ->and($result?->contact->fresh()->email_hash)->toBe(Contact::emailHash('vip@example.test'))
        ->and($result?->contact->fresh()->profile)->toMatchArray([
            'access_gate' => [
                'area_id' => 50,
                'area_key' => 'vip-library',
                'area_name' => 'VIP Library',
                'registration_id' => 501,
                'requested_url' => 'https://example.test/library',
                'requested_host' => 'example.test',
            ],
        ])
        ->and($result?->lead)->toBeInstanceOf(Lead::class)
        ->and($result?->lead?->title)->toBe('VIP Library access request')
        ->and($result?->activity?->type)->toBe(ContactActivityType::AccessRegistration)
        ->and($result?->activity?->summary)->toBe('Approved access request for VIP Library')
        ->and($result?->activity?->payload)->toMatchArray([
            'area_id' => 50,
            'area_key' => 'vip-library',
            'registration_id' => 501,
            'requested_url' => 'https://example.test/library',
            'requested_host' => 'example.test',
            'fields' => ['first_name', 'last_name', 'phone'],
        ]);
});

it('registers the access gate approved registration listener when access gate is available', function (): void {
    expect(Event::hasListeners(RegistrationApproved::class))->toBeTrue();
});

it('syncs event registrations into contacts through the source adapter', function (): void {
    $siteId = $this->createContactsSite();
    $registeredAt = Date::now()->subMinutes(3);

    $eventModel = new EventModel;
    $eventModel->exists = true;
    $eventModel->forceFill([
        'id' => 70,
        'site_id' => $siteId,
        'uuid' => 'event-uuid-70',
        'name' => 'Spring Briefing',
    ]);

    $occurrence = new EventOccurrence;
    $occurrence->exists = true;
    $occurrence->forceFill([
        'id' => 701,
        'event_id' => 70,
    ]);
    $occurrence->setRelation('event', $eventModel);

    $registration = new EventRegistration;
    $registration->exists = true;
    $registration->forceFill([
        'id' => 7001,
        'event_occurrence_id' => 701,
        'name' => 'Attendee Example',
        'email' => 'Attendee@Example.test',
        'phone' => '+44 20 0000 0001',
        'quantity' => 2,
        'status' => 'confirmed',
        'payload' => [
            'dietary_requirements' => 'Vegetarian',
            'company' => 'Example Ltd',
        ],
        'registered_at' => $registeredAt,
    ]);
    $registration->setRelation('occurrence', $occurrence);

    $result = SyncEventRegistrationContactAction::run(new EventRegistrationCreated($registration));

    expect($result)->not->toBeNull()
        ->and($result?->contact->fresh()->email_hash)->toBe(Contact::emailHash('attendee@example.test'))
        ->and($result?->contact->fresh()->profile)->toMatchArray([
            'events' => [
                'event_id' => 70,
                'event_name' => 'Spring Briefing',
                'occurrence_id' => 701,
                'registration_id' => 7001,
                'quantity' => 2,
                'status' => 'confirmed',
            ],
        ])
        ->and($result?->lead)->toBeNull()
        ->and($result?->activity?->type)->toBe(ContactActivityType::EventRegistration)
        ->and($result?->activity?->summary)->toBe('Registered for Spring Briefing')
        ->and($result?->activity?->payload)->toMatchArray([
            'event_id' => 70,
            'event_name' => 'Spring Briefing',
            'occurrence_id' => 701,
            'registration_id' => 7001,
            'quantity' => 2,
            'status' => 'confirmed',
            'payload_fields' => ['dietary_requirements', 'company'],
        ]);
});

it('registers the event registration listener when events is available', function (): void {
    expect(Event::hasListeners(EventRegistrationCreated::class))->toBeTrue();
});

it('syncs campaign conversions into contacts through the source adapter', function (): void {
    $siteId = $this->createContactsSite();
    $convertedAt = Date::now()->subMinutes(4);

    $campaignGroup = new CampaignGroup;
    $campaignGroup->exists = true;
    $campaignGroup->forceFill([
        'id' => 80,
        'site_id' => $siteId,
        'name' => 'Spring Launch',
        'slug' => 'spring-launch',
    ]);

    $landingPage = new CampaignLandingPage;
    $landingPage->exists = true;
    $landingPage->forceFill([
        'id' => 801,
        'campaign_group_id' => 80,
    ]);
    $landingPage->setRelation('campaignGroup', $campaignGroup);

    $goal = new CampaignConversionGoal;
    $goal->exists = true;
    $goal->forceFill([
        'id' => 802,
        'campaign_group_id' => 80,
        'site_id' => $siteId,
        'name' => 'Lead form',
    ]);
    $goal->setRelation('campaignGroup', $campaignGroup);

    $source = new class extends Model
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        protected $guarded = [];
    };
    $source->exists = true;
    $source->forceFill([
        'id' => 9001,
        'payload' => [
            'values' => [
                'email' => 'Campaign@Example.test',
                'first_name' => 'Campaign',
                'last_name' => 'Lead',
                'phone' => '+44 20 0000 0002',
            ],
        ],
    ]);

    $conversion = new CampaignConversion;
    $conversion->exists = true;
    $conversion->forceFill([
        'id' => 8001,
        'campaign_group_id' => 80,
        'campaign_landing_page_id' => 801,
        'campaign_conversion_goal_id' => 802,
        'site_id' => $siteId,
        'source_type' => 'form_submission',
        'source_id' => 9001,
        'converted_at' => $convertedAt,
    ]);
    $conversion->setRelation('campaignGroup', $campaignGroup);
    $conversion->setRelation('landingPage', $landingPage);
    $conversion->setRelation('goal', $goal);
    $conversion->setRelation('source', $source);

    $result = SyncCampaignConversionContactAction::run(new CampaignConverted($conversion));

    expect($result)->not->toBeNull()
        ->and($result?->contact->fresh()->email_hash)->toBe(Contact::emailHash('campaign@example.test'))
        ->and($result?->contact->fresh()->profile)->toMatchArray([
            'campaign_studio' => [
                'campaign_group_id' => 80,
                'campaign_group_name' => 'Spring Launch',
                'landing_page_id' => 801,
                'goal_id' => 802,
                'goal_name' => 'Lead form',
                'conversion_id' => 8001,
            ],
        ])
        ->and($result?->lead)->toBeNull()
        ->and($result?->activity?->type)->toBe(ContactActivityType::CampaignConversion)
        ->and($result?->activity?->summary)->toBe('Converted on Lead form')
        ->and($result?->activity?->payload)->toMatchArray([
            'campaign_group_id' => 80,
            'campaign_group_name' => 'Spring Launch',
            'landing_page_id' => 801,
            'goal_id' => 802,
            'goal_name' => 'Lead form',
            'conversion_id' => 8001,
            'source_type' => 'form_submission',
            'source_id' => 9001,
        ]);
});

it('registers the campaign conversion listener when campaign studio is available', function (): void {
    expect(Event::hasListeners(CampaignConverted::class))->toBeTrue();
});

it('syncs comment authors into contacts through the source adapter', function (): void {
    $siteId = $this->createContactsSite();
    $submittedAt = Date::now()->subMinutes(6);

    $author = new CommentAuthor;
    $author->exists = true;
    $author->forceFill([
        'id' => 91,
        'site_id' => $siteId,
        'name' => 'Comment Reader',
        'email' => 'Comment@Example.test',
    ]);

    $comment = new Comment;
    $comment->exists = true;
    $comment->forceFill([
        'id' => 9101,
        'public_id' => 'comment-public-id',
        'site_id' => $siteId,
        'comment_author_id' => 91,
        'commentable_type' => 'page',
        'commentable_id' => 42,
        'status' => CommentStatus::PendingApproval,
        'submitted_at' => $submittedAt,
    ]);
    $comment->setRelation('author', $author);

    $result = SyncCommentContactAction::run(new CommentCreated($comment));

    expect($result)->not->toBeNull()
        ->and($result?->contact->fresh()->email_hash)->toBe(Contact::emailHash('comment@example.test'))
        ->and($result?->contact->fresh()->profile)->toMatchArray([
            'comments' => [
                'author_id' => 91,
                'comment_id' => 9101,
                'commentable_type' => 'page',
                'commentable_id' => 42,
                'status' => 'pending_approval',
            ],
        ])
        ->and($result?->lead)->toBeNull()
        ->and($result?->activity?->type)->toBe(ContactActivityType::Comment)
        ->and($result?->activity?->summary)->toBe('Submitted a comment')
        ->and($result?->activity?->payload)->toMatchArray([
            'author_id' => 91,
            'comment_id' => 9101,
            'commentable_type' => 'page',
            'commentable_id' => 42,
            'status' => 'pending_approval',
            'public_id' => 'comment-public-id',
        ]);
});

it('registers the comment created listener when comments is available', function (): void {
    expect(Event::hasListeners(CommentCreated::class))->toBeTrue();
});

it('syncs shopify customers into contacts through the source adapter', function (): void {
    $siteId = $this->createContactsSite();
    $syncedAt = Date::now()->subMinutes(7);

    $connection = new ShopifyConnection;
    $connection->exists = true;
    $connection->forceFill([
        'id' => 101,
        'site_id' => $siteId,
        'shop_domain' => 'example.myshopify.com',
    ]);

    $customer = new ShopifyCustomer;
    $customer->exists = true;
    $customer->forceFill([
        'id' => 1001,
        'connection_id' => 101,
        'shopify_gid' => 'gid://shopify/Customer/1001',
        'email' => 'Buyer@Example.test',
        'first_name' => 'Buyer',
        'last_name' => 'Example',
        'phone' => '+44 20 0000 0003',
        'accepts_marketing' => true,
        'marketing_state' => 'SUBSCRIBED',
        'orders_count' => 3,
        'synced_at' => $syncedAt,
    ]);
    $customer->setRelation('connection', $connection);

    $result = SyncShopifyCustomerContactAction::run(new ShopifyCustomerSynced($customer));

    expect($result)->not->toBeNull()
        ->and($result?->contact->fresh()->email_hash)->toBe(Contact::emailHash('buyer@example.test'))
        ->and($result?->contact->fresh()->profile)->toMatchArray([
            'shopify_commerce' => [
                'connection_id' => 101,
                'shop_domain' => 'example.myshopify.com',
                'customer_id' => 1001,
                'shopify_gid' => 'gid://shopify/Customer/1001',
                'accepts_marketing' => true,
                'marketing_state' => 'SUBSCRIBED',
                'orders_count' => 3,
            ],
        ])
        ->and($result?->lead)->toBeNull()
        ->and($result?->activity?->type)->toBe(ContactActivityType::ShopifyCustomer)
        ->and($result?->activity?->summary)->toBe('Synced Shopify customer')
        ->and($result?->activity?->payload)->toMatchArray([
            'connection_id' => 101,
            'shop_domain' => 'example.myshopify.com',
            'customer_id' => 1001,
            'shopify_gid' => 'gid://shopify/Customer/1001',
            'accepts_marketing' => true,
            'marketing_state' => 'SUBSCRIBED',
            'orders_count' => 3,
        ]);
});

it('registers the shopify customer listener when shopify commerce is available', function (): void {
    expect(Event::hasListeners(ShopifyCustomerSynced::class))->toBeTrue();
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

it('exports and anonymizes contact privacy data', function (): void {
    $siteId = $this->createContactsSite();
    $contact = FindOrCreateContactAction::run(new ContactIdentityData(
        siteId: $siteId,
        email: 'privacy@example.test',
        phone: '+44 7700 900123',
        firstName: 'Privacy',
        lastName: 'Subject',
        displayName: 'Privacy Subject',
        sourceKey: 'form_builder',
        sourceIdentifier: 'submission-privacy-1',
        profile: ['tags' => ['vip']],
    ));
    $organisation = Organisation::query()->create([
        'site_id' => $siteId,
        'name' => 'Privacy Org',
        'domain' => 'privacy.example',
    ]);
    $contact->organisations()->attach($organisation->getKey(), [
        'role' => 'Buyer',
        'is_primary' => true,
    ]);
    $lead = Lead::query()->create([
        'site_id' => $siteId,
        'contact_id' => $contact->getKey(),
        'organisation_id' => $organisation->getKey(),
        'title' => 'Sensitive lead',
        'status' => LeadStatus::Open,
        'context' => ['message' => 'Contains personal data'],
    ]);
    RecordContactActivityAction::run(
        contact: $contact,
        activityData: new ContactActivityData(
            type: ContactActivityType::Note,
            summary: 'Sensitive note',
            payload: ['source' => 'privacy-test'],
            occurredAt: Date::now(),
        ),
        lead: $lead,
        organisation: $organisation,
    );

    $export = BuildContactPrivacyExportAction::run($contact);
    $anonymizedContact = AnonymizeContactAction::run($contact);

    expect($export['contact'])->toMatchArray([
        'email' => 'privacy@example.test',
        'phone' => '+44 7700 900123',
        'first_name' => 'Privacy',
        'last_name' => 'Subject',
        'source_identifier' => 'submission-privacy-1',
    ])
        ->and($export['contact'])->not->toHaveKeys(['email_hash', 'phone_hash', 'source_identifier_hash'])
        ->and($export['organisations'][0])->toMatchArray([
            'name' => 'Privacy Org',
            'role' => 'Buyer',
            'is_primary' => true,
        ])
        ->and($export['leads'][0]['context'])->toBe(['message' => 'Contains personal data'])
        ->and($export['activities'][0]['summary'])->toBe('Sensitive note')
        ->and($anonymizedContact->email)->toBeNull()
        ->and($anonymizedContact->email_hash)->toBeNull()
        ->and($anonymizedContact->source_identifier)->toBeNull()
        ->and($anonymizedContact->source_identifier_hash)->toBeNull()
        ->and($anonymizedContact->profile)->toBeNull()
        ->and($anonymizedContact->status)->toBe(ContactStatus::Archived)
        ->and($lead->refresh()->title)->toBeNull()
        ->and($lead->context)->toBeNull()
        ->and($contact->activities()->first()?->summary)->toBeNull()
        ->and($contact->activities()->first()?->payload)->toBeNull();
});
