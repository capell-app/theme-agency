<?php

declare(strict_types=1);

use Capell\AccessGate\Filament\Resources\AccessAreas\AccessAreaResource;
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
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @param  array<array-key, mixed>  $permissions
 * @param  array<array-key, mixed>  $roles
 */
function accessGatePolicyActor(array $permissions = [], array $roles = []): User
{
    return new class($permissions, $roles) extends User
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /**
         * @param  list<string>  $permissions
         * @param  list<string>  $roles
         */
        public function __construct(
            private readonly array $permissions = [],
            private readonly array $roles = [],
        ) {
            parent::__construct();
        }

        public function checkPermissionTo(string $permission): bool
        {
            return in_array($permission, $this->permissions, true);
        }

        public function hasRole(string $role): bool
        {
            return in_array($role, $this->roles, true);
        }
    };
}

it('requires explicit permissions for access gate admin resources', function (string $policyClass, string $modelClass, string $subject): void {
    /** @var AbstractAccessGateResourcePolicy $policy */
    $policy = new $policyClass;
    /** @var Model $record */
    $record = new $modelClass;

    expect($policy->viewAny(accessGatePolicyActor()))->toBeFalse()
        ->and($policy->viewAny(accessGatePolicyActor(['ViewAny:' . $subject])))->toBeTrue()
        ->and($policy->viewAny(accessGatePolicyActor(['View:' . $subject])))->toBeTrue()
        ->and($policy->update(accessGatePolicyActor(['ViewAny:' . $subject]), $record))->toBeFalse()
        ->and($policy->update(accessGatePolicyActor(['Update:' . $subject]), $record))->toBeTrue();
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
