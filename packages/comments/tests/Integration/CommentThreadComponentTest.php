<?php

declare(strict_types=1);

use Capell\Comments\Livewire\CommentThreadComponent;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Settings\CommentSettings;
use Capell\Core\Models\Site;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('submits a public comment through the cached thread key workflow', function (): void {
    Notification::fake();
    bindCommentThreadSettings();

    $page = $this->createCommentsPage();
    $threadKey = CommentThreadComponent::threadKeyFor($page);

    Livewire::test(CommentThreadComponent::class, ['threadKey' => $threadKey])
        ->assertSet('threadKey', $threadKey)
        ->assertSet('commentWebsite', '')
        ->assertSet('formRenderedAt', fn (int $formRenderedAt): bool => $formRenderedAt > 0)
        ->set('body', '<strong>Useful feedback</strong>')
        ->set('authorName', 'Public Reader')
        ->set('authorEmail', 'reader@example.com')
        ->set('formRenderedAt', now()->subSeconds(3)->getTimestamp())
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSet('body', '')
        ->assertSet('authorName', null)
        ->assertSet('authorEmail', null)
        ->assertSet('commentWebsite', '');

    $comment = Comment::query()->where('commentable_id', $page->getKey())->firstOrFail();
    $commentAuthor = capell_test_instance($comment->author, CommentAuthor::class);

    expect($commentAuthor->name)->toBe('Public Reader')
        ->and($comment->body)->toBe('Useful feedback')
        ->and($comment->site_id)->toBe((int) $page->site_id);
});

it('rejects public comments that trip the bot trap fields', function (): void {
    Notification::fake();
    bindCommentThreadSettings();

    $page = $this->createCommentsPage();
    $threadKey = CommentThreadComponent::threadKeyFor($page);

    Livewire::test(CommentThreadComponent::class, ['threadKey' => $threadKey])
        ->set('body', 'Bot filled the hidden field')
        ->set('authorName', 'Bot')
        ->set('authorEmail', 'bot@example.com')
        ->set('commentWebsite', 'https://spam.test')
        ->set('formRenderedAt', now()->subSeconds(3)->getTimestamp())
        ->call('submit')
        ->assertHasErrors(['body'])
        ->assertSet('submitted', false);

    Livewire::test(CommentThreadComponent::class, ['threadKey' => $threadKey])
        ->set('body', 'Submitted too quickly')
        ->set('authorName', 'Fast Bot')
        ->set('authorEmail', 'fast@example.com')
        ->call('submit')
        ->assertHasErrors(['body'])
        ->assertSet('submitted', false);

    expect(Comment::query()->whereIn('body', [
        'Bot filled the hidden field',
        'Submitted too quickly',
    ])->exists())->toBeFalse();
});

it('throttles repeated public submissions even when the author email changes', function (): void {
    Notification::fake();
    bindCommentThreadSettings();
    config()->set('capell-comments.throttle.max_attempts', 1);
    config()->set('capell-comments.throttle.decay_seconds', 60);

    $page = $this->createCommentsPage();
    $threadKey = CommentThreadComponent::threadKeyFor($page);

    Livewire::test(CommentThreadComponent::class, ['threadKey' => $threadKey])
        ->set('body', 'First comment')
        ->set('authorName', 'Public Reader')
        ->set('authorEmail', 'first@example.com')
        ->set('formRenderedAt', now()->subSeconds(3)->getTimestamp())
        ->call('submit')
        ->assertSet('submitted', true)
        ->set('body', 'Second comment')
        ->set('authorName', 'Public Reader')
        ->set('authorEmail', 'second@example.com')
        ->set('formRenderedAt', now()->subSeconds(3)->getTimestamp())
        ->call('submit')
        ->assertHasErrors(['body']);

    expect(Comment::query()->where('commentable_id', $page->getKey())->count())->toBe(1)
        ->and(Comment::query()->where('body', 'Second comment')->exists())->toBeFalse();
});

it('memoizes the resolved commentable during public submit refreshes', function (): void {
    Notification::fake();
    bindCommentThreadSettings();

    $page = $this->createCommentsPage();
    $pageSelects = 0;

    DB::listen(static function (QueryExecuted $query) use (&$pageSelects): void {
        $sql = strtolower($query->sql);

        if (str_contains($sql, 'from "pages"') || str_contains($sql, 'from `pages`') || str_contains($sql, 'from pages')) {
            $pageSelects++;
        }
    });

    Livewire::test(CommentThreadComponent::class, ['threadKey' => CommentThreadComponent::threadKeyFor($page)])
        ->set('body', 'Memoized comment')
        ->set('authorName', 'Public Reader')
        ->set('authorEmail', 'reader@example.com')
        ->set('formRenderedAt', now()->subSeconds(3)->getTimestamp())
        ->call('submit')
        ->assertSet('submitted', true);

    expect($pageSelects)->toBe(2);
});

it('loads additional replies for the selected parent comment', function (): void {
    bindCommentThreadSettings(['reply_page_size' => 1]);

    $page = $this->createCommentsPage();
    $parent = Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Parent comment',
    ]);

    foreach (['First reply', 'Second reply'] as $index => $body) {
        Comment::factory()->create([
            'site_id' => $page->site_id,
            'commentable_type' => $page->getMorphClass(),
            'commentable_id' => $page->getKey(),
            'parent_id' => $parent->getKey(),
            'root_id' => $parent->getKey(),
            'depth' => 1,
            'submitted_at' => now()->addSeconds($index),
            'body' => $body,
        ]);
    }

    Livewire::test(CommentThreadComponent::class, ['threadKey' => CommentThreadComponent::threadKeyFor($page)])
        ->assertSet('replyPageSize', 1)
        ->assertSee('Parent comment')
        ->assertSee('First reply')
        ->assertDontSee('Second reply')
        ->assertSee(__('capell-comments::generic.load_more_replies'))
        ->call('loadMoreReplies', (string) $parent->public_id)
        ->assertSet('replyLimits.' . $parent->public_id, 2)
        ->assertSee('Second reply')
        ->assertDontSee(__('capell-comments::generic.load_more_replies'));
});

it('keeps reply intent inside the public thread component until the visitor cancels it', function (): void {
    $page = $this->createCommentsPage();

    Livewire::test(CommentThreadComponent::class, ['threadKey' => CommentThreadComponent::threadKeyFor($page)])
        ->call('replyTo', 'public-parent-id')
        ->assertSet('parentPublicId', 'public-parent-id')
        ->call('cancelReply')
        ->assertSet('parentPublicId', null);
});

it('ignores invalid or stale thread keys without exposing comment state', function (): void {
    $page = $this->createCommentsPage();
    $threadKey = CommentThreadComponent::threadKeyFor($page);
    $otherSite = Site::factory()->create();
    $page->forceFill(['site_id' => $otherSite->getKey()])->save();

    Livewire::test(CommentThreadComponent::class, ['threadKey' => 'not-a-valid-key'])
        ->assertSet('comments', [])
        ->set('body', 'Ignored')
        ->call('submit')
        ->assertSet('submitted', false);

    Livewire::test(CommentThreadComponent::class, ['threadKey' => $threadKey])
        ->assertSet('comments', [])
        ->set('body', 'Stale scope')
        ->call('submit')
        ->assertSet('submitted', false);

    expect(Comment::query()->whereIn('body', ['Ignored', 'Stale scope'])->exists())->toBeFalse();
});

/**
 * @param  array<string, mixed>  $overrides
 */
function bindCommentThreadSettings(array $overrides = []): void
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
