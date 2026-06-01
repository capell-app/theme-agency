<?php

declare(strict_types=1);

use Capell\AccessGate\Enums\EventType;
use Capell\AccessGate\Filament\Resources\BrowserTokens\BrowserTokenResource;
use Capell\AccessGate\Filament\Resources\ClaimTokens\ClaimTokenResource;
use Capell\AccessGate\Filament\Resources\Events\AccessGateEventResource;
use Capell\AccessGate\Filament\Resources\Grants\GrantResource;
use Capell\AccessGate\Filament\Resources\Registrations\RegistrationResource;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\Event as AccessGateEvent;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Notifications\AccessExpiredNotification;
use Capell\AccessGate\Notifications\AccessRevokedNotification;
use Capell\AccessGate\Policies\RegistrationPolicy;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection as SupportCollection;

it('covers access gate event relationships and casts', function (): void {
    $event = new AccessGateEvent([
        'type' => EventType::RegistrationCreated,
        'payload' => ['email' => 'ben@example.com'],
        'metadata' => ['source' => 'test'],
    ]);

    expect($event->area()->getRelated())->toBeInstanceOf(Area::class)
        ->and($event->registration()->getForeignKeyName())->toBe('registration_id')
        ->and($event->grant()->getForeignKeyName())->toBe('grant_id')
        ->and($event->claimToken()->getForeignKeyName())->toBe('claim_token_id')
        ->and($event->browserToken()->getForeignKeyName())->toBe('browser_token_id')
        ->and($event->subject()->getMorphType())->toBe('subject_type')
        ->and($event->type)->toBe(EventType::RegistrationCreated)
        ->and($event->payload)->toBe(['email' => 'ben@example.com'])
        ->and($event->metadata)->toBe(['source' => 'test']);
});

it('builds access gate expiry and revocation notifications', function (): void {
    $area = new Area(['name' => 'Partner Portal']);

    $expired = new AccessExpiredNotification($area);
    $revoked = new AccessRevokedNotification($area);

    expect($expired->via(new stdClass))->toBe(['mail'])
        ->and($expired->toMail(new stdClass)->subject)->toContain('Partner Portal')
        ->and($revoked->via(new stdClass))->toBe(['mail'])
        ->and($revoked->toMail(new stdClass)->subject)->toContain('Partner Portal');
});

it('covers access gate policy abilities and defensive user branches', function (): void {
    $policy = new RegistrationPolicy;
    $record = new Registration;
    $record->setRelation('area', new Area(['site_id' => 10]));

    $actor = accessGateResidualPolicyActor([
        'Create:Registration',
        'Delete:Registration',
        'DeleteAny:Registration',
        'Restore:Registration',
        'RestoreAny:Registration',
        'ForceDelete:Registration',
        'ForceDeleteAny:Registration',
        'Reorder:Registration',
    ]);

    expect($policy->create($actor))->toBeTrue()
        ->and($policy->delete($actor, $record))->toBeTrue()
        ->and($policy->deleteAny($actor))->toBeTrue()
        ->and($policy->restore($actor, $record))->toBeTrue()
        ->and($policy->restoreAny($actor))->toBeTrue()
        ->and($policy->forceDelete($actor, $record))->toBeTrue()
        ->and($policy->forceDeleteAny($actor))->toBeTrue()
        ->and($policy->reorder($actor))->toBeTrue()
        ->and($policy->view(new User, $record))->toBeFalse()
        ->and($policy->create(new User))->toBeFalse();
});

it('builds access gate resource table declarations', function (): void {
    expect(RegistrationResource::table(accessGateResidualTable())->getColumns())->not->toBeEmpty()
        ->and(GrantResource::table(accessGateResidualTable())->getColumns())->not->toBeEmpty()
        ->and(BrowserTokenResource::table(accessGateResidualTable())->getColumns())->not->toBeEmpty()
        ->and(ClaimTokenResource::table(accessGateResidualTable())->getColumns())->not->toBeEmpty()
        ->and(AccessGateEventResource::table(accessGateResidualTable())->getColumns())->not->toBeEmpty();
});

/**
 * @param  list<string>  $permissions
 * @param  list<string>  $roles
 */
function accessGateResidualPolicyActor(array $permissions = [], array $roles = []): User
{
    $user = new class extends User
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var list<string> */
        public array $permissions = [];

        /** @var list<string> */
        public array $roles = [];

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
            return collect([10]);
        }
    };

    $user->permissions = $permissions;
    $user->roles = $roles;

    return $user;
}

function accessGateResidualTable(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null);

    return Table::make($livewire);
}
