<?php

declare(strict_types=1);

use Capell\Comments\Console\Commands\InstallCommentsCommand;
use Capell\Comments\Filament\Widgets\CommentStatsWidget;
use Capell\Comments\Filament\Widgets\LatestCommentsWidget;
use Capell\Comments\Providers\CommentsServiceProvider;
use Illuminate\Support\Facades\File;

it('declares comments as optional for supported companion packages', function (): void {
    $manifest = json_decode(File::get(__DIR__ . '/../../capell.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['name'])->toBe('capell-app/comments')
        ->and($manifest['dependencies']['requires'])->not->toContain('capell-app/blog')
        ->and($manifest['dependencies']['requires'])->not->toContain('capell-app/layout-builder')
        ->and($manifest['dependencies']['supports'])->toContain('capell-app/blog')
        ->and($manifest['providers']['runtime'])->toContain(CommentsServiceProvider::class)
        ->and($manifest['commands']['install'])->toBe('capell-comments:install')
        ->and(class_exists(InstallCommentsCommand::class))->toBeTrue();
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
