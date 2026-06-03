<?php

declare(strict_types=1);

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Contacts\Actions\BuildContactsOverviewStatsAction;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\ResourceEnum;
use Capell\Contacts\Filament\Resources\Activities\ContactActivityResource;
use Capell\Contacts\Filament\Resources\Contacts\ContactResource;
use Capell\Contacts\Filament\Resources\Leads\LeadResource;
use Capell\Contacts\Filament\Resources\Organisations\OrganisationResource;
use Capell\Contacts\Filament\Widgets\ContactsOverviewStatsWidget;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Capell\Contacts\Policies\ContactActivityPolicy;
use Capell\Contacts\Policies\ContactPolicy;
use Capell\Contacts\Policies\LeadPolicy;
use Capell\Contacts\Policies\OrganisationPolicy;
use Capell\Contacts\Tests\ContactsTestCase;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Foundation\Auth\User;

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

it('declares read only admin resources for crm records', function (): void {
    expect(ResourceEnum::Contact->value)->toBe(ContactResource::class)
        ->and(ResourceEnum::Organisation->value)->toBe(OrganisationResource::class)
        ->and(ResourceEnum::Lead->value)->toBe(LeadResource::class)
        ->and(ResourceEnum::ContactActivity->value)->toBe(ContactActivityResource::class)
        ->and(ContactResource::getModel())->toBe(Contact::class)
        ->and(OrganisationResource::getModel())->toBe(Organisation::class)
        ->and(LeadResource::getModel())->toBe(Lead::class)
        ->and(ContactActivityResource::getModel())->toBe(ContactActivity::class)
        ->and(array_keys(ContactResource::getPages()))->toBe(['index'])
        ->and(array_keys(OrganisationResource::getPages()))->toBe(['index'])
        ->and(array_keys(LeadResource::getPages()))->toBe(['index'])
        ->and(array_keys(ContactActivityResource::getPages()))->toBe(['index']);
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
