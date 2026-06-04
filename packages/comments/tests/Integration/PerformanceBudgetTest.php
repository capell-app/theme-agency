<?php

declare(strict_types=1);

use Capell\Comments\Actions\BuildPublicThreadAction;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Settings\CommentSettings;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

it('keeps public thread hydration and rendering inside declared performance budgets', function (): void {
    bindCommentPerformanceSettings(['root_page_size' => 5, 'reply_page_size' => 2]);

    $budget = commentsFrontendRenderBudgetMilliseconds();
    $page = $this->createCommentsPage();
    $site = $page->site()->firstOrFail();
    $languageId = (int) $site->language_id;
    $page->setAttribute('language_id', $languageId);
    $author = CommentAuthor::factory()->create(['site_id' => $page->site_id]);

    foreach (range(1, 5) as $rootIndex) {
        $root = Comment::factory()->create([
            'site_id' => $page->site_id,
            'language_id' => $languageId,
            'comment_author_id' => $author->getKey(),
            'commentable_type' => $page->getMorphClass(),
            'commentable_id' => $page->getKey(),
            'submitted_at' => now()->subMinutes(20 - $rootIndex),
            'body' => 'Root comment ' . $rootIndex,
        ]);

        foreach (range(1, 3) as $replyIndex) {
            Comment::factory()->create([
                'site_id' => $page->site_id,
                'language_id' => $languageId,
                'comment_author_id' => $author->getKey(),
                'commentable_type' => $page->getMorphClass(),
                'commentable_id' => $page->getKey(),
                'parent_id' => $root->getKey(),
                'root_id' => $root->getKey(),
                'depth' => 1,
                'submitted_at' => now()->subMinutes(15 - $replyIndex),
                'body' => sprintf('Reply %s.%s', $rootIndex, $replyIndex),
            ]);
        }
    }

    [$comments, $queryCount] = commentsMeasureQueries(
        fn (): array => BuildPublicThreadAction::run($page),
    );

    expect($queryCount)->toBeLessThanOrEqual(20)
        ->and($comments)->toHaveCount(5)
        ->and($comments[0]->children)->toHaveCount(2)
        ->and($comments[0]->replyCount)->toBe(3)
        ->and($comments[0]->hasMoreReplies)->toBeTrue();

    view('capell-comments::livewire.partials.comment-list', ['comments' => $comments])->render();

    [$html, $renderMilliseconds] = commentsMeasureMilliseconds(
        fn (): string => view('capell-comments::livewire.partials.comment-list', ['comments' => $comments])->render(),
    );

    expect($renderMilliseconds)->toBeLessThanOrEqual($budget)
        ->and($html)->toContain('Root comment 1')
        ->and($html)->toContain(__('capell-comments::generic.load_more_replies'))
        ->and($html)->not->toContain('Reply 1.3');
});

/**
 * @template TValue
 *
 * @param  Closure(): TValue  $callback
 * @return array{0: TValue, 1: int}
 */
function commentsMeasureQueries(Closure $callback): array
{
    $queryCount = 0;

    DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
        $queryCount++;
    });

    return [$callback(), $queryCount];
}

/**
 * @template TValue
 *
 * @param  Closure(): TValue  $callback
 * @return array{0: TValue, 1: float}
 */
function commentsMeasureMilliseconds(Closure $callback): array
{
    $startedAt = hrtime(true);
    $result = $callback();
    $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;

    return [$result, $elapsedMilliseconds];
}

function commentsFrontendRenderBudgetMilliseconds(): float
{
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    return (float) data_get($manifest, 'performance.frontendRenderBudgetMs', 20);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function bindCommentPerformanceSettings(array $overrides = []): void
{
    /** @var CommentSettings $settings */
    $settings = (new ReflectionClass(CommentSettings::class))->newInstanceWithoutConstructor();
    $settings->enabled = true;
    $settings->identity_mode = 'both';
    $settings->publication_policy = 'require_approval';
    $settings->verification_flow = 'verify_then_moderate';
    $settings->require_email_verification = true;
    $settings->max_depth = 4;
    $settings->root_page_size = 20;
    $settings->reply_page_size = 5;
    $settings->site_overrides = [];
    $settings->commentable_type_overrides = [];

    foreach ($overrides as $property => $value) {
        $settings->{$property} = $value;
    }

    app()->instance(CommentSettings::class, $settings);
}
