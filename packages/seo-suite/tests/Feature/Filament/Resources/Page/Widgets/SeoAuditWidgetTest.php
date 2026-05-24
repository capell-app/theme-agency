<?php

declare(strict_types=1);

use Capell\Admin\Filament\Resources\Pages\PageResource;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Filament\Resources\Pages\Widgets\ListPageSeoAuditWidget;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Livewire\Livewire;

use function Pest\Laravel\get;

uses(CreatesAdminUser::class);

/**
 * @param  Collection<array-key, mixed>  $assignedSiteIds
 */
function createScopedUserForSEOAuditWidgetTest(SupportCollection $assignedSiteIds): Authenticatable
{
    $user = new class extends Authenticatable implements FilamentUser
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var SupportCollection<int, int> */
        public SupportCollection $assignedSiteIds;

        protected $table = 'users';

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
    };

    $user->forceFill([
        'name' => 'Scoped SEO Widget User',
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password'),
    ]);
    $user->assignedSiteIds = $assignedSiteIds;

    return $user;
}

beforeEach(function (): void {
    test()->actingAsAdmin();
});

// --- ListPageSeoAuditWidget ---

test('list: see livewire component', function (): void {
    get(PageResource::getUrl())
        ->assertSeeLivewire(ListPageSeoAuditWidget::class);
});

test('list: totals are zero when no pages exist', function (): void {
    Livewire::test(ListPageSeoAuditWidget::class)
        ->assertSet(
            'totals',
            fn (array $totals): bool => $totals['total'] === 0
            && $totals['missingDescription'] === 0
            && $totals['titleIssues'] === 0
            && $totals['duplicateTitles'] === 0,
        );
});

test('list: counts pages missing meta description', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->recycle($language)->create();

    // Page without description
    Page::factory()
        ->state(['site_id' => $site->id])
        ->has(
            Translation::factory()->state(['language_id' => $language->id, 'meta' => ['slug' => 'test']]),
            'translations',
        )
        ->create();

    // Page with description
    Page::factory()
        ->state(['site_id' => $site->id])
        ->has(
            Translation::factory()->state(['language_id' => $language->id, 'meta' => ['description' => 'A good description here.', 'slug' => 'test2']]),
            'translations',
        )
        ->create();

    Livewire::test(ListPageSeoAuditWidget::class)
        ->assertSet(
            'totals',
            fn (array $totals): bool => $totals['total'] === 2
            && $totals['missingDescription'] === 1,
        );
});

test('list: totals only include pages from assigned sites for non-global users', function (): void {
    $assignedSite = Site::factory()->withTranslations()->create();
    $hiddenSite = Site::factory()->withTranslations()->create();

    Page::factory()
        ->recycle($assignedSite)
        ->withTranslations(data: ['meta' => ['slug' => 'assigned']])
        ->create();

    Page::factory()
        ->recycle($hiddenSite)
        ->withTranslations(data: ['meta' => ['slug' => 'hidden']])
        ->create();

    test()->actingAs(createScopedUserForSEOAuditWidgetTest(collect([$assignedSite->getKey()])));

    Livewire::test(ListPageSeoAuditWidget::class)
        ->assertSet(
            'totals',
            fn (array $totals): bool => $totals['total'] === 1
            && $totals['missingDescription'] === 1,
        );
});

test('list: totals deny pages when non-global user has no assigned sites', function (): void {
    Page::factory()
        ->count(2)
        ->withTranslations(data: ['meta' => ['slug' => 'hidden']])
        ->create();

    test()->actingAs(createScopedUserForSEOAuditWidgetTest(collect()));

    Livewire::test(ListPageSeoAuditWidget::class)
        ->assertSet('totals', fn (array $totals): bool => $totals['total'] === 0);
});

test('list: counts pages with title issues', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->recycle($language)->create();

    // Page with too-short title (< 30 chars)
    Page::factory()
        ->state(['site_id' => $site->id])
        ->has(
            Translation::factory()->state(['language_id' => $language->id, 'meta' => ['title' => 'Short', 'slug' => 'a']]),
            'translations',
        )
        ->create();

    // Page with too-long title (> 60 chars)
    Page::factory()
        ->state(['site_id' => $site->id])
        ->has(
            Translation::factory()->state(['language_id' => $language->id, 'meta' => ['title' => str_repeat('a', 61), 'slug' => 'b']]),
            'translations',
        )
        ->create();

    // Page with good title
    Page::factory()
        ->state(['site_id' => $site->id])
        ->has(
            Translation::factory()->state(['language_id' => $language->id, 'meta' => ['title' => 'A well-written title of correct length here', 'slug' => 'c']]),
            'translations',
        )
        ->create();

    Livewire::test(ListPageSeoAuditWidget::class)
        ->assertSet('totals', fn (array $totals): bool => $totals['titleIssues'] === 2);
});

test('list: counts pages with duplicate titles', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->recycle($language)->create();
    $sharedTitle = 'A well-written title that is the right length';

    Page::factory()
        ->state(['site_id' => $site->id])
        ->has(
            Translation::factory()->state(['language_id' => $language->id, 'meta' => ['title' => $sharedTitle, 'slug' => 'x']]),
            'translations',
        )
        ->create();

    Page::factory()
        ->state(['site_id' => $site->id])
        ->has(
            Translation::factory()->state(['language_id' => $language->id, 'meta' => ['title' => $sharedTitle, 'slug' => 'y']]),
            'translations',
        )
        ->create();

    Livewire::test(ListPageSeoAuditWidget::class)
        ->assertSet('totals', fn (array $totals): bool => $totals['duplicateTitles'] === 2);
});
