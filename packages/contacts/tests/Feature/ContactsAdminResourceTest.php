<?php

declare(strict_types=1);

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Contacts\Actions\BuildContactsOverviewStatsAction;
use Capell\Contacts\Actions\SyncAccessGateRegistrationContactAction;
use Capell\Contacts\Actions\SyncCampaignConversionContactAction;
use Capell\Contacts\Actions\SyncCommentContactAction;
use Capell\Contacts\Actions\SyncContactSourceRecordAction;
use Capell\Contacts\Actions\SyncEventRegistrationContactAction;
use Capell\Contacts\Actions\SyncFormSubmissionContactAction;
use Capell\Contacts\Actions\SyncShopifyCustomerContactAction;
use Capell\Contacts\Actions\UpdateLeadStatusAction;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\LeadStatus;
use Capell\Contacts\Enums\ResourceEnum;
use Capell\Contacts\Filament\Resources\Activities\ContactActivityResource;
use Capell\Contacts\Filament\Resources\Contacts\ContactResource;
use Capell\Contacts\Filament\Resources\Contacts\Pages\ViewContact;
use Capell\Contacts\Filament\Resources\Leads\LeadResource;
use Capell\Contacts\Filament\Resources\Organisations\OrganisationResource;
use Capell\Contacts\Filament\Widgets\ContactsOverviewStatsWidget;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\ContactTag;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Capell\Contacts\Policies\ContactActivityPolicy;
use Capell\Contacts\Policies\ContactPolicy;
use Capell\Contacts\Policies\LeadPolicy;
use Capell\Contacts\Policies\OrganisationPolicy;
use Capell\Contacts\Tests\ContactsTestCase;
use Filament\Actions\ActionGroup;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

require_once __DIR__ . '/../autoload.php';

uses(ContactsTestCase::class);

function contactsResourceTestTable(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null);

    return Table::make($livewire);
}

it('does not mark encrypted contact columns as searchable', function (): void {
    $columns = ContactResource::table(contactsResourceTestTable())->getColumns();

    expect($columns['display_name']->isSearchable())->toBeFalse()
        ->and($columns['email']->isSearchable())->toBeFalse();
});

it('exposes operator privacy actions for contact records', function (): void {
    $actions = ContactResource::table(contactsResourceTestTable())->getRecordActions();
    $user = new User;
    $contact = new Contact;

    expect(contactsResourceTestActionNames($actions))->toContain('view', 'merge', 'privacy_export', 'privacy_anonymize')
        ->and((new ContactPolicy)->exportPrivacy($user, $contact))->toBeTrue()
        ->and((new ContactPolicy)->anonymizePrivacy($user, $contact))->toBeTrue();
});

it('declares read only admin resources for crm records', function (): void {
    expect(ResourceEnum::Contact->value)->toBe(ContactResource::class)
        ->and(ResourceEnum::Organisation->value)->toBe(OrganisationResource::class)
        ->and(ResourceEnum::Lead->value)->toBe(LeadResource::class)
        ->and(ResourceEnum::ContactActivity->value)->toBe(ContactActivityResource::class)
        ->and(ContactResource::getModel())->toBe(Contact::class)
        ->and(OrganisationResource::getModel())->toBe(Organisation::class)
        ->and(LeadResource::getModel())->toBe(Lead::class)
        ->and(ContactActivityResource::getModel())->toBe(ContactActivity::class)
        ->and(array_keys(ContactResource::getPages()))->toBe(['index', 'view'])
        ->and(ViewContact::getResource())->toBe(ContactResource::class)
        ->and(array_keys(OrganisationResource::getPages()))->toBe(['index'])
        ->and(array_keys(LeadResource::getPages()))->toBe(['index'])
        ->and(array_keys(ContactActivityResource::getPages()))->toBe(['index']);
});

it('exposes a lead status transition action', function (): void {
    $actions = LeadResource::table(contactsResourceTestTable())->getRecordActions();

    expect(contactsResourceTestActionNames($actions))->toContain('change_status');
});

it('updates lead status transition timestamps through the action', function (): void {
    $siteId = $this->createContactsSite();
    $contact = Contact::query()->create([
        'site_id' => $siteId,
        'email' => 'lead@example.test',
        'display_name' => 'Lead Example',
    ]);
    $lead = Lead::query()->create([
        'site_id' => $siteId,
        'contact_id' => $contact->getKey(),
        'title' => 'Example lead',
        'status' => LeadStatus::New,
        'captured_at' => now(),
    ]);

    UpdateLeadStatusAction::run($lead, LeadStatus::Qualified);
    $lead->refresh();

    expect($lead->status)->toBe(LeadStatus::Qualified)
        ->and($lead->qualified_at)->not->toBeNull()
        ->and($lead->closed_at)->toBeNull();

    UpdateLeadStatusAction::run($lead, LeadStatus::Won);
    $lead->refresh();

    expect($lead->status)->toBe(LeadStatus::Won)
        ->and($lead->closed_at)->not->toBeNull();
});

it('keeps contacts admin policies read only by default', function (): void {
    $user = new User;

    expect((new ContactPolicy)->viewAny($user))->toBeTrue()
        ->and((new OrganisationPolicy)->viewAny($user))->toBeTrue()
        ->and((new LeadPolicy)->viewAny($user))->toBeTrue()
        ->and((new ContactActivityPolicy)->viewAny($user))->toBeTrue()
        ->and((new ContactPolicy)->create($user))->toBeFalse()
        ->and((new OrganisationPolicy)->create($user))->toBeFalse()
        ->and((new LeadPolicy)->create($user))->toBeFalse()
        ->and((new ContactActivityPolicy)->create($user))->toBeFalse();
});

it('keeps source sync entrypoints as typed actions', function (): void {
    $actions = [
        SyncAccessGateRegistrationContactAction::class,
        SyncCampaignConversionContactAction::class,
        SyncCommentContactAction::class,
        SyncContactSourceRecordAction::class,
        SyncEventRegistrationContactAction::class,
        SyncFormSubmissionContactAction::class,
        SyncShopifyCustomerContactAction::class,
    ];

    foreach ($actions as $action) {
        $returnType = (new ReflectionMethod($action, 'handle'))->getReturnType();

        expect(class_uses_recursive($action))->toContain(AsAction::class)
            ->and((string) $returnType)->toContain(ContactSourceSyncResultData::class);
    }
});

it('keeps the contact resource list query under the declared admin query budget', function (): void {
    if (! Schema::hasColumn('sites', 'deleted_at')) {
        Schema::table('sites', function (Blueprint $table): void {
            $table->timestamp('deleted_at')->nullable();
        });
    }

    $siteId = $this->createContactsSite();
    $tag = ContactTag::query()->create([
        'site_id' => $siteId,
        'name' => 'VIP',
        'slug' => 'vip',
    ]);

    foreach (range(1, 12) as $contactNumber) {
        $contact = Contact::query()->create([
            'site_id' => $siteId,
            'email' => 'person-' . $contactNumber . '@example.test',
            'display_name' => 'Person ' . $contactNumber,
            'last_seen_at' => now()->subMinutes($contactNumber),
        ]);

        $contact->tags()->attach($tag);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $contacts = ContactResource::getEloquentQuery()
        ->orderByDesc('last_seen_at')
        ->limit(12)
        ->get();

    $contacts->each(function (Contact $contact): void {
        $contact->site?->getKey();
        $contact->tags->pluck('slug')->all();
    });

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($contacts)->toHaveCount(12)
        ->and($queryCount)->toBeLessThanOrEqual(5);
});

it('builds contacts overview widget stats from crm records', function (): void {
    $siteId = $this->createContactsSite();
    $contact = Contact::query()->create([
        'site_id' => $siteId,
        'email' => 'person@example.test',
        'display_name' => 'Person Example',
    ]);
    Organisation::query()->create([
        'site_id' => $siteId,
        'name' => 'Example Ltd',
    ]);
    Lead::query()->create([
        'site_id' => $siteId,
        'contact_id' => $contact->getKey(),
        'title' => 'Example lead',
        'status' => 'open',
    ]);
    Lead::query()->create([
        'site_id' => $siteId,
        'contact_id' => $contact->getKey(),
        'title' => 'Won lead',
        'status' => 'won',
    ]);
    ContactActivity::query()->create([
        'site_id' => $siteId,
        'contact_id' => $contact->getKey(),
        'type' => ContactActivityType::Note,
        'summary' => 'Called contact',
        'occurred_at' => now(),
    ]);

    expect(BuildContactsOverviewStatsAction::run())->toBe([
        'contacts' => 1,
        'organisations' => 1,
        'open_leads' => 1,
        'activities' => 1,
    ])->and(class_implements(ContactsOverviewStatsWidget::class))->toContain(CapellWidgetContract::class);
});

it('can scope contacts overview widget stats to a site', function (): void {
    $primarySiteId = $this->createContactsSite();
    $secondarySiteId = $this->createContactsSite();

    $primaryContact = Contact::query()->create([
        'site_id' => $primarySiteId,
        'email' => 'primary@example.test',
        'display_name' => 'Primary Example',
    ]);
    $secondaryContact = Contact::query()->create([
        'site_id' => $secondarySiteId,
        'email' => 'secondary@example.test',
        'display_name' => 'Secondary Example',
    ]);

    Organisation::query()->create([
        'site_id' => $primarySiteId,
        'name' => 'Primary Ltd',
    ]);
    Organisation::query()->create([
        'site_id' => $secondarySiteId,
        'name' => 'Secondary Ltd',
    ]);

    Lead::query()->create([
        'site_id' => $primarySiteId,
        'contact_id' => $primaryContact->getKey(),
        'title' => 'Primary lead',
        'status' => 'open',
    ]);
    Lead::query()->create([
        'site_id' => $secondarySiteId,
        'contact_id' => $secondaryContact->getKey(),
        'title' => 'Secondary qualified lead',
        'status' => 'qualified',
    ]);
    Lead::query()->create([
        'site_id' => $secondarySiteId,
        'contact_id' => $secondaryContact->getKey(),
        'title' => 'Secondary closed lead',
        'status' => 'lost',
    ]);

    ContactActivity::query()->create([
        'site_id' => $primarySiteId,
        'contact_id' => $primaryContact->getKey(),
        'type' => ContactActivityType::Note,
        'summary' => 'Primary note',
        'occurred_at' => now(),
    ]);
    ContactActivity::query()->create([
        'site_id' => $secondarySiteId,
        'contact_id' => $secondaryContact->getKey(),
        'type' => ContactActivityType::Note,
        'summary' => 'Secondary email',
        'occurred_at' => now(),
    ]);

    expect(BuildContactsOverviewStatsAction::run($primarySiteId))->toBe([
        'contacts' => 1,
        'organisations' => 1,
        'open_leads' => 1,
        'activities' => 1,
    ])->and(BuildContactsOverviewStatsAction::run($secondarySiteId))->toBe([
        'contacts' => 1,
        'organisations' => 1,
        'open_leads' => 1,
        'activities' => 1,
    ])->and(BuildContactsOverviewStatsAction::run())->toBe([
        'contacts' => 2,
        'organisations' => 2,
        'open_leads' => 2,
        'activities' => 2,
    ]);
});

/**
 * @param  array<array-key, mixed>  $actions
 * @return list<string>
 */
function contactsResourceTestActionNames(array $actions): array
{
    return array_values(collect($actions)
        ->flatMap(fn (mixed $action): array => contactsResourceTestFlattenActionNames($action))
        ->values()
        ->all());
}

/**
 * @return list<string>
 */
function contactsResourceTestFlattenActionNames(mixed $action): array
{
    if ($action instanceof ActionGroup) {
        return contactsResourceTestActionNames($action->getActions());
    }

    if (is_object($action) && method_exists($action, 'getName')) {
        return [(string) $action->getName()];
    }

    return [];
}
