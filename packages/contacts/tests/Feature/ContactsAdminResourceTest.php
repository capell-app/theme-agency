<?php

declare(strict_types=1);

use Capell\Contacts\Enums\ResourceEnum;
use Capell\Contacts\Filament\Resources\Activities\ContactActivityResource;
use Capell\Contacts\Filament\Resources\Contacts\ContactResource;
use Capell\Contacts\Filament\Resources\Leads\LeadResource;
use Capell\Contacts\Filament\Resources\Organisations\OrganisationResource;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Capell\Contacts\Policies\ContactActivityPolicy;
use Capell\Contacts\Policies\ContactPolicy;
use Capell\Contacts\Policies\LeadPolicy;
use Capell\Contacts\Policies\OrganisationPolicy;
use Capell\Contacts\Tests\ContactsTestCase;
use Illuminate\Foundation\Auth\User;

require_once __DIR__ . '/../autoload.php';

uses(ContactsTestCase::class);

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
