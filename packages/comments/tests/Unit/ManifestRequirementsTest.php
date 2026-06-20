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
use Capell\Comments\Filament\Resources\CommentAuthors\CommentAuthorResource;
use Capell\Comments\Filament\Resources\Comments\CommentResource;
use Capell\Comments\Filament\Widgets\CommentStatsFilamentWidget;
use Capell\Comments\Filament\Widgets\LatestCommentsFilamentWidget;
use Capell\Comments\Manifest\CommentAdminResourcesContribution;
use Capell\Comments\Manifest\CommentDashboardFilamentWidgetsContribution;
use Capell\Comments\Manifest\CommentFrontendComponentsContribution;
use Capell\Comments\Manifest\CommentModelsContribution;
use Capell\Comments\Manifest\CommentRoutesContribution;
use Capell\Comments\Manifest\CommentSettingsContribution;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentModerationEvent;
use Capell\Comments\Models\CommentReaction;
use Capell\Comments\Models\CommentToken;
use Capell\Comments\Providers\CommentsServiceProvider;
use Capell\Comments\Settings\CommentSettings;
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

it('declares implemented comments contribution surfaces', function (): void {
    $manifest = json_decode(File::get(__DIR__ . '/../../capell.json'), true, flags: JSON_THROW_ON_ERROR);

    throw_unless(is_array($manifest), RuntimeException::class, 'Comments manifest must be an array.');

    $contributions = $manifest['contributes'] ?? [];

    throw_unless(is_array($contributions), RuntimeException::class, 'Comments contributions must be an array.');

    $contributions = collect($contributions);

    $widgets = $contributions->firstWhere('class', CommentDashboardFilamentWidgetsContribution::class);
    $resources = $contributions->firstWhere('class', CommentAdminResourcesContribution::class);
    $models = $contributions->firstWhere('class', CommentModelsContribution::class);
    $routes = $contributions->firstWhere('class', CommentRoutesContribution::class);
    $settings = $contributions->firstWhere('class', CommentSettingsContribution::class);
    $components = $contributions->firstWhere('class', CommentFrontendComponentsContribution::class);

    throw_unless(is_array($widgets), RuntimeException::class, 'Comments widget contribution must be an array.');
    throw_unless(is_array($resources), RuntimeException::class, 'Comments resource contribution must be an array.');
    throw_unless(is_array($models), RuntimeException::class, 'Comments model contribution must be an array.');
    throw_unless(is_array($routes), RuntimeException::class, 'Comments route contribution must be an array.');
    throw_unless(is_array($settings), RuntimeException::class, 'Comments settings contribution must be an array.');
    throw_unless(is_array($components), RuntimeException::class, 'Comments component contribution must be an array.');

    $traceability = $manifest['contributionTraceability'] ?? null;
    $security = $manifest['security'] ?? null;

    throw_unless(is_array($traceability), RuntimeException::class, 'Comments traceability metadata must be an array.');
    throw_unless(is_array($security), RuntimeException::class, 'Comments security metadata must be an array.');
    throw_unless(is_array($security['publicSurface'] ?? null), RuntimeException::class, 'Comments public surface metadata must be an array.');

    expect($traceability['deferredContributions'])->toBe([])
        ->and($widgets['widgetClasses'])->toContain(CommentStatsFilamentWidget::class, LatestCommentsFilamentWidget::class)
        ->and($resources['resourceClasses'])->toContain(CommentResource::class, CommentAuthorResource::class)
        ->and($models['modelClasses'])->toContain(
            CommentAuthor::class,
            Comment::class,
            CommentToken::class,
            CommentModerationEvent::class,
            CommentReaction::class,
        )
        ->and($routes['routes'])->toBe($security['publicSurface']['routeNames'])
        ->and($settings['settingsClass'])->toBe(CommentSettings::class)
        ->and($components['componentClasses'])->toContain('Capell\\Comments\\Livewire\\CommentThreadComponent');
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

        expect($path)->toMatch('/^docs\\/(assets\\/marketplace|screenshots)\\//')
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

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required comments screenshot contract entries must have screenshot paths.');

        $requiredMarketplaceAssetPaths[] = str_replace('packages/comments/', '', $screenshotPath);
    }

    expect($marketplaceScreenshotPaths)
        ->toContain('docs/assets/marketplace/extension-card.jpg')
        ->toContain(...$requiredMarketplaceAssetPaths);
});
