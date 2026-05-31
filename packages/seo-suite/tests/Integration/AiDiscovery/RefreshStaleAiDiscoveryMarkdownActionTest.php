<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\RefreshAiDiscoveryPageProfileMarkdownAction;
use Capell\SeoSuite\Actions\RefreshStaleAiDiscoveryMarkdownAction;
use Capell\SeoSuite\Actions\ResolveAiDiscoveryProfileAction;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    Date::setTestNow(now());
});

afterEach(function (): void {
    Date::setTestNow();
});

it('refreshes generated markdown for a single ai discovery page profile', function (): void {
    [$profile] = createRefreshableAiDiscoveryProfile('Single Refresh');

    $refreshed = RefreshAiDiscoveryPageProfileMarkdownAction::run($profile);
    $profile->refresh();

    expect($refreshed)->toBeTrue()
        ->and($profile->generated_markdown)->toContain('# Single Refresh')
        ->and($profile->markdown_hash)->toBe(hash('sha256', (string) $profile->generated_markdown))
        ->and($profile->last_generated_at)->toBeInstanceOf(CarbonInterface::class);
});

it('refreshes stale included ai discovery markdown profiles with an optional limit', function (): void {
    [$firstProfile] = createRefreshableAiDiscoveryProfile('First Stale');
    [$secondProfile] = createRefreshableAiDiscoveryProfile('Second Stale');
    [$excludedProfile] = createRefreshableAiDiscoveryProfile('Excluded Stale');
    $excludedProfile->update(['include_in_ai_index' => false]);

    $refreshed = RefreshStaleAiDiscoveryMarkdownAction::run(limit: 1);

    expect($refreshed)->toBe(1)
        ->and($firstProfile->refresh()->generated_markdown)->toContain('# First Stale')
        ->and($secondProfile->refresh()->generated_markdown)->toBeNull()
        ->and($excludedProfile->refresh()->generated_markdown)->toBeNull();
});

it('exposes stale ai discovery markdown regeneration through a console command', function (): void {
    [$profile] = createRefreshableAiDiscoveryProfile('Command Stale');

    $this->artisan('capell:seo-suite:refresh-ai-discovery-markdown', ['--limit' => 5])
        ->expectsOutput('Refreshed 1 stale AI Discovery page Markdown profile.')
        ->assertSuccessful();

    expect($profile->refresh()->generated_markdown)->toContain('# Command Stale');
});

/**
 * @return array{0: AiDiscoveryPageProfile, 1: Page, 2: Site, 3: Language}
 */
function createRefreshableAiDiscoveryProfile(string $title): array
{
    $language = Language::query()->create([
        'name' => 'English',
        'locale' => 'en',
        'code' => 'en',
        'flag' => 'gb-eng',
        'status' => true,
        'default' => true,
        'order' => 1,
    ]);
    $site = Site::factory()->language($language)->withTranslations($language)->create();
    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => $title,
            'summary' => $title . ' summary',
            'content' => '<p>' . $title . ' body</p>',
        ])
        ->create();

    $profile = ResolveAiDiscoveryProfileAction::run($site, $language, $page);

    if (! $profile instanceof AiDiscoveryPageProfile) {
        throw new RuntimeException('Expected a page profile when resolving AI Discovery for a page.');
    }

    $profile->update([
        'include_in_ai_index' => true,
        'summary' => $title . ' AI summary',
        'generated_markdown' => null,
        'markdown_hash' => null,
        'last_generated_at' => null,
    ]);

    $profile->refresh();

    return [$profile, $page, $site, $language];
}
