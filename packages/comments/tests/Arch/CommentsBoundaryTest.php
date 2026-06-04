<?php

declare(strict_types=1);

use Capell\Comments\Actions\BuildPublicThreadAction;
use Capell\Comments\Actions\CreateCommentAction;
use Capell\Comments\Actions\ResolvePublicCommentableThreadAction;
use Capell\Comments\Data\CreateCommentData;
use Capell\Comments\Data\PublicCommentableThreadData;
use Capell\Comments\Data\PublicCommentData;
use Capell\Comments\Http\Controllers\RenderCommentThreadController;
use Capell\Comments\Livewire\CommentThreadComponent;
use Capell\Comments\Providers\FrontendServiceProvider;
use Symfony\Component\Finder\Finder;

arch()
    ->expect('Capell\Comments')
    ->classes()
    ->toUseStrictEquality();

arch('comments public runtime does not depend on admin or authoring surfaces')
    ->expect([
        BuildPublicThreadAction::class,
        CreateCommentAction::class,
        CreateCommentData::class,
        PublicCommentableThreadData::class,
        PublicCommentData::class,
        RenderCommentThreadController::class,
        ResolvePublicCommentableThreadAction::class,
        CommentThreadComponent::class,
        FrontendServiceProvider::class,
    ])
    ->not->toUse([
        'Capell\Admin',
        'Capell\FrontendAuthoring',
        'Filament',
    ]);

it('keeps public comments blade views free of database access and authoring markers', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $publicViewDirectory = $packagePath . '/resources/views/livewire';
    $publicViewFiles = [
        $packagePath . '/resources/views/verify-email.blade.php',
    ];
    $forbiddenFragments = [
        '::query(',
        'DB::',
        'loadMissing(',
        '->comments()',
        '->media',
        'commentable_id',
        'commentable_type',
        'moderation',
        'filament',
        '/admin',
        'signed_editor',
        'editable',
        'data-edit',
    ];
    $violations = [];

    $files = (new Finder)
        ->files()
        ->in($publicViewDirectory)
        ->name('*.blade.php');

    foreach ($files as $file) {
        assertPublicCommentViewHasNoForbiddenFragments($file->getPathname(), $forbiddenFragments, $violations);
    }

    foreach ($publicViewFiles as $publicViewFile) {
        assertPublicCommentViewHasNoForbiddenFragments($publicViewFile, $forbiddenFragments, $violations);
    }

    expect($violations)->toBeEmpty();
});

/**
 * @param  list<string>  $forbiddenFragments
 * @param  list<string>  $violations
 */
function assertPublicCommentViewHasNoForbiddenFragments(string $path, array $forbiddenFragments, array &$violations): void
{
    $contents = file_get_contents($path);

    if (! is_string($contents)) {
        $violations[] = $path . ': unreadable';

        return;
    }

    foreach ($forbiddenFragments as $forbiddenFragment) {
        if (str_contains($contents, $forbiddenFragment)) {
            $violations[] = $path . ': ' . $forbiddenFragment;
        }
    }
}
