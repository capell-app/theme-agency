<?php

declare(strict_types=1);

use Capell\CampaignStudio\Filament\Resources\CampaignGroups\CampaignGroupResource;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Policies\CampaignGroupPolicy;
use Capell\Core\Models\Site;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @param  Collection<array-key, mixed>  $assignedSiteIds
 * @param  array<array-key, mixed>  $permissions
 */
function campaignStudioScopedUser(SupportCollection $assignedSiteIds, array $permissions = []): Authenticatable
{
    $user = new class extends Authenticatable implements FilamentUser
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var SupportCollection<int, int> */
        public SupportCollection $assignedSiteIds;

        /** @var list<string> */
        public array $permissions = [];

        public function canAccessPanel(Panel $panel): bool
        {
            return true;
        }

        /** @return SupportCollection<int, int> */
        public function getAssignedSiteIds(): SupportCollection
        {
            return $this->assignedSiteIds;
        }

        public function isGlobalAdmin(): bool
        {
            return false;
        }

        public function checkPermissionTo(mixed $permission, mixed $guardName = null): bool
        {
            return in_array((string) $permission, $this->permissions, true);
        }
    };

    $user->forceFill([
        'name' => 'Scoped Campaign User',
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password'),
    ]);
    $user->assignedSiteIds = $assignedSiteIds;
    $user->permissions = $permissions;

    return $user;
}

test('campaign group resource queries are scoped to the current actor sites', function (): void {
    $assignedSite = Site::factory()->create();
    $otherSite = Site::factory()->create();
    $assignedGroup = CampaignGroup::factory()->create(['site_id' => $assignedSite->getKey()]);
    CampaignGroup::factory()->create(['site_id' => $otherSite->getKey()]);

    auth()->setUser(campaignStudioScopedUser(collect([$assignedSite->getKey()])));

    expect(CampaignGroupResource::getEloquentQuery()->pluck('id')->all())
        ->toEqualCanonicalizing([$assignedGroup->getKey()]);
});

test('campaign group policy denies records outside the actor site assignments', function (): void {
    $assignedSite = Site::factory()->create();
    $otherSite = Site::factory()->create();
    $assignedGroup = CampaignGroup::factory()->create(['site_id' => $assignedSite->getKey()]);
    $otherGroup = CampaignGroup::factory()->create(['site_id' => $otherSite->getKey()]);
    $user = campaignStudioScopedUser(
        collect([$assignedSite->getKey()]),
        ['Update:CampaignGroup'],
    );

    $policy = new CampaignGroupPolicy;

    expect($policy->update($user, $assignedGroup))->toBeTrue()
        ->and($policy->update($user, $otherGroup))->toBeFalse();
});
