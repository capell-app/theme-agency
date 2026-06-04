<?php

declare(strict_types=1);

use Capell\Comments\Livewire\CommentThreadComponent;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Settings\CommentSettings;
use Capell\Core\Models\Site;
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
    $settings->site_overrides = [];
    $settings->commentable_type_overrides = [];

    foreach ($overrides as $property => $value) {
        $settings->{$property} = $value;
    }

    app()->instance(CommentSettings::class, $settings);
}
