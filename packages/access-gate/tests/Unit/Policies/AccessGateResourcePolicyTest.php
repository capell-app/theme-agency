<?php

declare(strict_types=1);

use Capell\AccessGate\Filament\Resources\AccessAreas\AccessAreaResource;
use Capell\AccessGate\Filament\Resources\BrowserTokens\BrowserTokenResource;
use Capell\AccessGate\Filament\Resources\ClaimTokens\ClaimTokenResource;
use Capell\AccessGate\Filament\Resources\Events\AccessGateEventResource;
use Capell\AccessGate\Filament\Resources\Grants\GrantResource;
use Capell\AccessGate\Filament\Resources\Registrations\RegistrationResource;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Models\Event as AccessGateEvent;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Policies\AbstractAccessGateResourcePolicy;
use Capell\AccessGate\Policies\AccessAreaPolicy;
use Capell\AccessGate\Policies\AccessGateEventPolicy;
use Capell\AccessGate\Policies\BrowserTokenPolicy;
use Capell\AccessGate\Policies\ClaimTokenPolicy;
use Capell\AccessGate\Policies\GrantPolicy;
use Capell\AccessGate\Policies\RegistrationPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @param  list<string>  $permissions
 * @param  list<string>  $roles
 */
function accessGatePolicyActor(array $permissions = [], array $roles = [], ?SupportCollection $assignedSiteIds = null): User
{
    $user = new class extends User
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var list<string> */
        public array $permissions = [];

        /** @var list<string> */
        public array $roles = [];

        /** @var SupportCollection<int, int> */
        public SupportCollection $assignedSiteIds;

        public function checkPermissionTo(string $permission): bool
        {
            return in_array($permission, $this->permissions, true);
        }

        public function hasRole(string $role): bool
        {
            return in_array($role, $this->roles, true);
        }

        /** @return SupportCollection<int, int> */
        public function getAssignedSiteIds(): SupportCollection
        {
            return $this->assignedSiteIds;
        }
    };

    $user->permissions = $permissions;
    $user->roles = $roles;
    $user->assignedSiteIds = $assignedSiteIds ?? collect([10]);

    return $user;
}

/**
 * @param  class-string<Model>  $modelClass
 */
function accessGatePolicyRecord(string $modelClass, int $siteId): Model
{
    $area = new Area(['site_id' => $siteId]);

    if ($modelClass === Area::class) {
        return $area;
    }

    /** @var Model $record */
    $record = new $modelClass;
    $record->setRelation('area', $area);

    return $record;
}

function defineAccessGatePolicySiteTables(): void
{
    if (Schema::hasTable('sites')) {
        return;
    }

    Schema::create('sites', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->softDeletes();
        $table->timestamps();
    });
}

it('requires explicit permissions for access gate admin resources', function (string $policyClass, string $modelClass, string $subject): void {
    /** @var AbstractAccessGateResourcePolicy $policy */
    $policy = new $policyClass;
    $record = accessGatePolicyRecord($modelClass, 10);

    expect($policy->viewAny(accessGatePolicyActor()))->toBeFalse()
        ->and($policy->viewAny(accessGatePolicyActor(['ViewAny:' . $subject])))->toBeTrue()
        ->and($policy->viewAny(accessGatePolicyActor(['View:' . $subject])))->toBeTrue()
        ->and($policy->update(accessGatePolicyActor(['ViewAny:' . $subject]), $record))->toBeFalse()
        ->and($policy->update(accessGatePolicyActor(['Update:' . $subject])))->toBeTrue()
        ->and($policy->update(accessGatePolicyActor(['Update:' . $subject]), $record))->toBeTrue();
})->with([
    'access areas' => [AccessAreaPolicy::class, Area::class, 'AccessArea'],
    'registrations' => [RegistrationPolicy::class, Registration::class, 'Registration'],
    'grants' => [GrantPolicy::class, Grant::class, 'Grant'],
    'claim tokens' => [ClaimTokenPolicy::class, ClaimToken::class, 'ClaimToken'],
    'browser tokens' => [BrowserTokenPolicy::class, BrowserToken::class, 'BrowserToken'],
    'events' => [AccessGateEventPolicy::class, AccessGateEvent::class, 'AccessGateEvent'],
]);

it('denies access gate record mutations outside the actor assigned sites', function (string $policyClass, string $modelClass, string $subject): void {
    /** @var AbstractAccessGateResourcePolicy $policy */
    $policy = new $policyClass;
    $hiddenRecord = accessGatePolicyRecord($modelClass, 20);

    expect($policy->view(accessGatePolicyActor(['ViewAny:' . $subject], assignedSiteIds: collect([10])), $hiddenRecord))->toBeFalse()
        ->and($policy->update(accessGatePolicyActor(['Update:' . $subject], assignedSiteIds: collect([10])), $hiddenRecord))->toBeFalse()
        ->and($policy->delete(accessGatePolicyActor(['Delete:' . $subject], assignedSiteIds: collect([10])), $hiddenRecord))->toBeFalse()
        ->and($policy->update(accessGatePolicyActor(roles: ['super_admin'], assignedSiteIds: collect([])), $hiddenRecord))->toBeTrue();
})->with([
    'access areas' => [AccessAreaPolicy::class, Area::class, 'AccessArea'],
    'registrations' => [RegistrationPolicy::class, Registration::class, 'Registration'],
    'grants' => [GrantPolicy::class, Grant::class, 'Grant'],
    'claim tokens' => [ClaimTokenPolicy::class, ClaimToken::class, 'ClaimToken'],
    'browser tokens' => [BrowserTokenPolicy::class, BrowserToken::class, 'BrowserToken'],
    'events' => [AccessGateEventPolicy::class, AccessGateEvent::class, 'AccessGateEvent'],
]);

it('allows the configured super admin role to manage access gate resources', function (): void {
    $policy = new RegistrationPolicy;
    $registration = new Registration;

    expect($policy->update(accessGatePolicyActor(roles: ['super_admin']), $registration))->toBeTrue();
});

it('scopes access area resource queries to the current actor sites', function (): void {
    $assignedArea = Area::factory()->create(['site_id' => 10]);
    Area::factory()->create(['site_id' => 20]);

    $user = new class extends User
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var SupportCollection<int, int> */
        public SupportCollection $assignedSiteIds;

        public function isGlobalAdmin(): bool
        {
            return false;
        }

        /** @return SupportCollection<int, int> */
        public function getAssignedSiteIds(): SupportCollection
        {
            return $this->assignedSiteIds;
        }
    };
    $user->assignedSiteIds = collect([10]);

    auth()->setUser($user);

    expect(AccessAreaResource::getEloquentQuery()->pluck('id')->all())
        ->toEqualCanonicalizing([$assignedArea->getKey()]);
});

it('normalizes and authorizes access area site form data', function (): void {
    defineAccessGatePolicySiteTables();

    DB::table('sites')->insert([
        ['id' => 10, 'name' => 'Assigned', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 20, 'name' => 'Hidden', 'created_at' => now(), 'updated_at' => now()],
    ]);

    auth()->setUser(accessGatePolicyActor(assignedSiteIds: collect([10])));

    expect(AccessAreaResource::prepareFormDataForPersistence(['site_id' => 10]))->toMatchArray([
        'site_id' => 10,
    ]);

    expect(fn (): array => AccessAreaResource::prepareFormDataForPersistence(['site_id' => 20]))
        ->toThrow(AuthorizationException::class);

    expect(fn (): array => AccessAreaResource::prepareFormDataForPersistence(['site_id' => null]))
        ->toThrow(AuthorizationException::class);
});

it('scopes access gate child resource queries to the current actor sites', function (): void {
    $assignedArea = Area::factory()->create(['site_id' => 10]);
    $hiddenArea = Area::factory()->create(['site_id' => 20]);

    $assignedRegistration = Registration::factory()->for($assignedArea, 'area')->create();
    Registration::factory()->for($hiddenArea, 'area')->create();

    $assignedGrant = Grant::factory()->for($assignedArea, 'area')->create();
    $hiddenGrant = Grant::factory()->for($hiddenArea, 'area')->create();

    $assignedClaimToken = ClaimToken::factory()->for($assignedArea, 'area')->create();
    ClaimToken::factory()->for($hiddenArea, 'area')->create();

    $assignedBrowserToken = BrowserToken::factory()->create([
        'access_area_id' => $assignedArea->getKey(),
        'grant_id' => $assignedGrant->getKey(),
    ]);
    BrowserToken::factory()->create([
        'access_area_id' => $hiddenArea->getKey(),
        'grant_id' => $hiddenGrant->getKey(),
    ]);

    $assignedEvent = AccessGateEvent::factory()->for($assignedArea, 'area')->create();
    AccessGateEvent::factory()->for($hiddenArea, 'area')->create();

    $user = new class extends User
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var SupportCollection<int, int> */
        public SupportCollection $assignedSiteIds;

        public function isGlobalAdmin(): bool
        {
            return false;
        }

        /** @return SupportCollection<int, int> */
        public function getAssignedSiteIds(): SupportCollection
        {
            return $this->assignedSiteIds;
        }
    };
    $user->assignedSiteIds = collect([10]);

    auth()->setUser($user);

    expect(RegistrationResource::getEloquentQuery()->pluck('id')->all())->toEqualCanonicalizing([$assignedRegistration->getKey()])
        ->and(GrantResource::getEloquentQuery()->pluck('id')->all())->toEqualCanonicalizing([$assignedGrant->getKey()])
        ->and(ClaimTokenResource::getEloquentQuery()->pluck('id')->all())->toEqualCanonicalizing([$assignedClaimToken->getKey()])
        ->and(BrowserTokenResource::getEloquentQuery()->pluck('id')->all())->toEqualCanonicalizing([$assignedBrowserToken->getKey()])
        ->and(AccessGateEventResource::getEloquentQuery()->pluck('id')->all())->toEqualCanonicalizing([$assignedEvent->getKey()]);
});
