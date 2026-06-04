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
