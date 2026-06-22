<?php

declare(strict_types=1);

use Capell\AiCreator\Actions\ApplyAiCreatorSessionAction;
use Capell\AiCreator\Actions\PreviewAiCreatorSessionAction;
use Capell\AiCreator\Actions\StartAiCreatorSessionAction;
use Capell\AiCreator\Data\AiCreatorStartSessionData;
use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Capell\AiCreator\Models\AiCreatorSession;

it('starts a creator session with deterministic package recommendations', function (): void {
    $session = StartAiCreatorSessionAction::run(new AiCreatorStartSessionData(
        intent: 'Create a resource library landing page with search, articles, media, and SEO metadata.',
        siteId: 10,
        workspaceId: 20,
        userId: 30,
        answers: ['tone' => 'practical'],
    ));

    expect($session)->toBeInstanceOf(AiCreatorSession::class)
        ->and($session->status)->toBe(AiCreatorSessionStatus::Draft)
        ->and($session->site_id)->toBe(10)
        ->and($session->workspace_id)->toBe(20)
        ->and($session->user_id)->toBe(30)
        ->and($session->package_requirements)->toContain('capell-app/layout-builder')
        ->and(collect($session->package_recommendations)->pluck('package')->all())->toContain(
            'capell-app/blog',
            'capell-app/search',
            'capell-app/media-library',
            'capell-app/seo-suite',
        );
});

it('previews then applies a creator session without applying generated code', function (): void {
    $session = StartAiCreatorSessionAction::run(new AiCreatorStartSessionData(
        intent: 'Create a campaign landing page with analytics reporting.',
    ));

    $preview = PreviewAiCreatorSessionAction::run($session);
    $session->refresh();

    expect($preview->sessionId)->toBe((int) $session->getKey())
        ->and($session->status)->toBe(AiCreatorSessionStatus::Previewed)
        ->and($session->preview_output)->toHaveKey('recommendations');

    $appliedSession = ApplyAiCreatorSessionAction::run($session);

    expect($appliedSession->status)->toBe(AiCreatorSessionStatus::Applied)
        ->and($appliedSession->applied_at)->not->toBeNull();
});
