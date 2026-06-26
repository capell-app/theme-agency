<?php

declare(strict_types=1);

use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\AiCreator\Actions\ApplyAiCreatorSessionAction;
use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Capell\AiCreator\Models\AiCreatorSession;
use Capell\AiCreator\Tests\Fixtures\SiteScopedAgentBridgeUser;
use Capell\Core\Actions\CreateDefaultLanguagesAction;
use Capell\Core\Models\Site;
use Capell\FoundationTheme\Actions\InstallFoundationThemeLayoutDefaultsAction;

beforeEach(function (): void {
    InstallFoundationThemeLayoutDefaultsAction::run();
    CreateDefaultLanguagesAction::run(['en']);
});

function previewCapability(): CapabilityData
{
    return resolve(CapellAgentBridgeCapabilityRegistry::class)->get('capell.ai-creator.build_preview');
}

function previewInvocation(int $sessionId, int $userId): CapabilityInvocationData
{
    return new CapabilityInvocationData(
        capability: previewCapability(),
        payload: [
            'session_id' => $sessionId,
            'spec' => [
                'site' => ['name' => 'Bluefin', 'businessName' => 'Bluefin Roasters'],
                'theme' => ['key' => 'bluefin', 'colors' => ['primary' => '#0b3d4f']],
                'pages' => [
                    ['name' => 'Home', 'slug' => 'home', 'title' => 'Welcome', 'pageType' => 'default', 'sections' => [
                        ['type' => 'content', 'content' => '<p>Hi.</p>', 'title' => 'Intro'],
                    ]],
                    ['name' => 'About', 'slug' => 'about', 'title' => 'About', 'pageType' => 'default'],
                ],
            ],
        ],
        user: new SiteScopedAgentBridgeUser(identifier: $userId, assignedSiteIds: []),
    );
}

it('previews the build without mutating the session', function (): void {
    $session = AiCreatorSession::query()->create(['intent' => 'cafe site', 'user_id' => 11, 'status' => AiCreatorSessionStatus::Draft]);

    $result = resolve(previewCapability()->actionClass)->preview(previewInvocation($session->id, 11));
    $session->refresh();

    expect($result->ok)->toBeTrue()
        ->and($result->data['would_build'])->toBeTrue()
        ->and($session->status)->toBe(AiCreatorSessionStatus::Draft)
        ->and($session->site_id)->toBeNull()
        ->and($session->preview_output)->toBeNull();
});

it('builds a flagged preview site and stores the spec on the session', function (): void {
    $session = AiCreatorSession::query()->create(['intent' => 'cafe site', 'user_id' => 11, 'status' => AiCreatorSessionStatus::Draft]);

    $result = resolve(previewCapability()->actionClass)->execute(previewInvocation($session->id, 11));
    $session->refresh();

    expect($result->ok)->toBeTrue()
        ->and($session->status)->toBe(AiCreatorSessionStatus::Previewed)
        ->and($session->site_id)->not->toBeNull()
        ->and($session->theme_plan)->toHaveKey('site')
        ->and($session->theme_plan)->toHaveKey('theme')
        ->and($session->page_plan)->toHaveCount(2)
        ->and($session->preview_output['site_id'] ?? null)->toBe($session->site_id);

    $site = Site::query()->findOrFail($session->site_id);
    expect($site->meta['is_preview'] ?? null)->toBeTrue()
        ->and($site->meta['preview_session_id'] ?? null)->toBe($session->id);
});

it('lets apply rebuild a confirmed site from the previewed spec', function (): void {
    $session = AiCreatorSession::query()->create(['intent' => 'cafe site', 'user_id' => 11, 'status' => AiCreatorSessionStatus::Draft]);

    resolve(previewCapability()->actionClass)->execute(previewInvocation($session->id, 11));
    $session->refresh();
    $previewSiteId = $session->site_id;

    $applied = ApplyAiCreatorSessionAction::run($session);

    // Apply promotes the previewed site in place (builder is create-or-update
    // by name) and clears the preview flags.
    expect($applied->status)->toBe(AiCreatorSessionStatus::Applied)
        ->and($applied->site_id)->toBe($previewSiteId);

    $appliedSite = Site::query()->findOrFail($applied->site_id);
    expect($appliedSite->meta['is_preview'] ?? null)->toBeNull()
        ->and($appliedSite->meta['preview_session_id'] ?? null)->toBeNull();
});
