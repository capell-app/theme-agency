<?php

declare(strict_types=1);

use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\CampaignConversionGoalResource;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\CampaignCtaWidgetResource;
use Capell\CampaignStudio\Filament\Resources\CampaignGroups\CampaignGroupResource;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignCtaWidget;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Policies\CampaignConversionGoalPolicy;
use Capell\CampaignStudio\Policies\CampaignCtaWidgetPolicy;
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
 * @param  list<string>  $permissions
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
    $globalGroup = CampaignGroup::factory()->create(['site_id' => null]);
    $user = campaignStudioScopedUser(
        collect([$assignedSite->getKey()]),
        ['Update:CampaignGroup'],
    );

    $policy = new CampaignGroupPolicy;

    expect($policy->update($user, $assignedGroup))->toBeTrue()
        ->and($policy->update($user, $otherGroup))->toBeFalse()
        ->and($policy->update($user, $globalGroup))->toBeFalse();
});

test('campaign CTA and goal resources scope by their campaign group site', function (): void {
    $assignedSite = Site::factory()->create();
    $otherSite = Site::factory()->create();
    $assignedGroup = CampaignGroup::factory()->create(['site_id' => $assignedSite->getKey()]);
    $otherGroup = CampaignGroup::factory()->create(['site_id' => $otherSite->getKey()]);
    $assignedCtaWidget = CampaignCtaWidget::factory()->create([
        'campaign_group_id' => $assignedGroup->getKey(),
        'site_id' => null,
    ]);
    CampaignCtaWidget::factory()->create([
        'campaign_group_id' => $otherGroup->getKey(),
        'site_id' => null,
    ]);
    $assignedGoal = CampaignConversionGoal::factory()->create([
        'campaign_group_id' => $assignedGroup->getKey(),
        'site_id' => null,
    ]);
    CampaignConversionGoal::factory()->create([
        'campaign_group_id' => $otherGroup->getKey(),
        'site_id' => null,
    ]);

    auth()->setUser(campaignStudioScopedUser(collect([$assignedSite->getKey()])));

    expect(CampaignCtaWidgetResource::getEloquentQuery()->pluck('id')->all())
        ->toEqualCanonicalizing([$assignedCtaWidget->getKey()])
        ->and(CampaignConversionGoalResource::getEloquentQuery()->pluck('id')->all())
        ->toEqualCanonicalizing([$assignedGoal->getKey()]);
});

test('campaign CTA and goal policies deny group-owned records outside actor sites', function (): void {
    $assignedSite = Site::factory()->create();
    $otherSite = Site::factory()->create();
    $assignedGroup = CampaignGroup::factory()->create(['site_id' => $assignedSite->getKey()]);
    $otherGroup = CampaignGroup::factory()->create(['site_id' => $otherSite->getKey()]);
    $assignedCtaWidget = CampaignCtaWidget::factory()->create([
        'campaign_group_id' => $assignedGroup->getKey(),
        'site_id' => null,
    ]);
    $otherCtaWidget = CampaignCtaWidget::factory()->create([
        'campaign_group_id' => $otherGroup->getKey(),
        'site_id' => null,
    ]);
    $globalGroupCtaWidget = CampaignCtaWidget::factory()->create([
        'campaign_group_id' => CampaignGroup::factory()->create(['site_id' => null])->getKey(),
        'site_id' => null,
    ]);
    $assignedGoal = CampaignConversionGoal::factory()->create([
        'campaign_group_id' => $assignedGroup->getKey(),
        'site_id' => null,
    ]);
    $otherGoal = CampaignConversionGoal::factory()->create([
        'campaign_group_id' => $otherGroup->getKey(),
        'site_id' => null,
    ]);
    $globalGroupGoal = CampaignConversionGoal::factory()->create([
        'campaign_group_id' => CampaignGroup::factory()->create(['site_id' => null])->getKey(),
        'site_id' => null,
    ]);
    $user = campaignStudioScopedUser(
        collect([$assignedSite->getKey()]),
        ['Update:CampaignCtaWidget', 'Update:CampaignConversionGoal'],
    );

    expect((new CampaignCtaWidgetPolicy)->update($user, $assignedCtaWidget))->toBeTrue()
        ->and((new CampaignCtaWidgetPolicy)->update($user, $otherCtaWidget))->toBeFalse()
        ->and((new CampaignCtaWidgetPolicy)->update($user, $globalGroupCtaWidget))->toBeFalse()
        ->and((new CampaignConversionGoalPolicy)->update($user, $assignedGoal))->toBeTrue()
        ->and((new CampaignConversionGoalPolicy)->update($user, $otherGoal))->toBeFalse()
        ->and((new CampaignConversionGoalPolicy)->update($user, $globalGroupGoal))->toBeFalse();
});
