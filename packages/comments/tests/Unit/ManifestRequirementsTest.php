<?php

declare(strict_types=1);

use Capell\Comments\Actions\ApplyCommentPrivacyRetentionAction;
use Capell\Comments\Actions\DisableCommentAuthorReplyNotificationsAction;
use Capell\Comments\Actions\RequestCommentReplyNotificationAction;
use Capell\Comments\Actions\ScoreCommentSpamAction;
use Capell\Comments\Actions\ToggleCommentReactionAction;
use Capell\Comments\Console\Commands\InstallCommentsCommand;
use Capell\Comments\Console\Commands\PruneCommentPrivacyDataCommand;
use Capell\Comments\Contracts\CommentSpamProvider;
use Capell\Comments\Filament\Widgets\CommentStatsWidget;
use Capell\Comments\Filament\Widgets\LatestCommentsWidget;
use Capell\Comments\Providers\CommentsServiceProvider;
use Capell\Comments\Support\Spam\ConfiguredCommentSpamProvider;
use Capell\Comments\Support\Spam\LocalCommentSpamProvider;
use Illuminate\Support\Facades\File;

it('declares comments as optional for supported companion packages', function (): void {
    $manifest = json_decode(File::get(__DIR__ . '/../../capell.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['name'])->toBe('capell-app/comments')
        ->and($manifest['dependencies']['requires'])->not->toContain('capell-app/blog')
        ->and($manifest['dependencies']['requires'])->not->toContain('capell-app/layout-builder')
        ->and($manifest['dependencies']['supports'])->toContain('capell-app/blog')
        ->and($manifest['providers']['runtime'])->toContain(CommentsServiceProvider::class)
        ->and($manifest['commands']['install'])->toBe('capell-comments:install')
        ->and($manifest['commands']['retention'])->toBe('capell-comments:privacy-retention')
        ->and($manifest['actions']['applyPrivacyRetention'])->toBe(ApplyCommentPrivacyRetentionAction::class)
        ->and($manifest['actions']['scoreSpam'])->toBe(ScoreCommentSpamAction::class)
        ->and($manifest['actions']['toggleReaction'])->toBe(ToggleCommentReactionAction::class)
        ->and($manifest['actions']['requestReplyNotification'])->toBe(RequestCommentReplyNotificationAction::class)
        ->and($manifest['actions']['disableReplyNotifications'])->toBe(DisableCommentAuthorReplyNotificationsAction::class)
        ->and($manifest['capabilities'])->toContain('comments-privacy-retention')
        ->and($manifest['capabilities'])->toContain('comments-spam-provider-contract')
        ->and($manifest['capabilities'])->toContain('comments-reactions')
        ->and($manifest['capabilities'])->toContain('comments-reply-notifications')
        ->and($manifest['database']['requiredTables'])->toContain('comment_reactions')
        ->and(resolve(CommentSpamProvider::class))->toBeInstanceOf(ConfiguredCommentSpamProvider::class)
        ->and(config('capell-comments.spam.providers'))->toContain(LocalCommentSpamProvider::class)
        ->and(class_exists(InstallCommentsCommand::class))->toBeTrue()
        ->and(class_exists(PruneCommentPrivacyDataCommand::class))->toBeTrue();
});

it('declares every registered dashboard widget in the manifest contributes list', function (): void {
    $manifest = json_decode(File::get(__DIR__ . '/../../capell.json'), true, flags: JSON_THROW_ON_ERROR);

    $contributions = $manifest['contributes'] ?? [];

    throw_unless(is_array($contributions), RuntimeException::class, 'Comments contributions must be an array.');

    $contributedWidgetClasses = collect($contributions)
        ->where('type', 'dashboard-widget')
        ->pluck('class')
        ->all();

    expect($contributedWidgetClasses)
        ->toContain(CommentStatsWidget::class)
        ->toContain(LatestCommentsWidget::class);
});

it('declares committed marketplace gallery assets for every required screenshot capture target', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = json_decode(File::get($packagePath . '/capell.json'), true, flags: JSON_THROW_ON_ERROR);
    $screenshotContract = json_decode(File::get($packagePath . '/docs/screenshots.json'), true, flags: JSON_THROW_ON_ERROR);

    $marketplace = $manifest['marketplace'] ?? [];
    $marketplaceScreenshots = $marketplace['screenshots'] ?? [];
    $contractEntries = $screenshotContract['entries'] ?? [];

    throw_unless(is_array($marketplace), RuntimeException::class, 'Comments marketplace metadata must be an array.');
    throw_unless(is_array($marketplaceScreenshots), RuntimeException::class, 'Comments marketplace screenshots must be an array.');
    throw_unless(is_array($contractEntries), RuntimeException::class, 'Comments screenshot contract entries must be an array.');

    $marketplaceScreenshotPaths = [];

    foreach ($marketplaceScreenshots as $marketplaceScreenshot) {
        throw_unless(is_array($marketplaceScreenshot), RuntimeException::class, 'Comments marketplace screenshot entries must be arrays.');

        $path = $marketplaceScreenshot['path'] ?? null;
        $alt = $marketplaceScreenshot['alt'] ?? null;
        $caption = $marketplaceScreenshot['caption'] ?? null;

        throw_unless(is_string($path), RuntimeException::class, 'Comments marketplace screenshot paths must be strings.');
        throw_unless(is_string($alt), RuntimeException::class, 'Comments marketplace screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'Comments marketplace screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $path;

        expect($path)->toStartWith('docs/assets/marketplace/')
            ->and(File::exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    $requiredMarketplaceAssetPaths = [];

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry)) {
            continue;
        }

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $id = $contractEntry['id'] ?? null;

        throw_unless(is_string($id), RuntimeException::class, 'Required comments screenshot contract entries must have string ids.');

        $requiredMarketplaceAssetPaths[] = 'docs/assets/marketplace/' . $id . '.svg';
    }

    expect($marketplaceScreenshotPaths)
        ->toContain('docs/assets/marketplace/extension-card.jpg')
        ->toContain(...$requiredMarketplaceAssetPaths);
});
