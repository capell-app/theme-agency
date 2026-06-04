<?php

declare(strict_types=1);

use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;

it('lets a comment author disable reply notifications with a token', function (): void {
    $page = $this->createCommentsPage();
    $author = CommentAuthor::factory()->create([
        'site_id' => $page->site_id,
        'email' => 'parent@example.com',
    ]);
    $comment = Comment::factory()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ]);
    $rawToken = 'reply-notification-token';
    $token = CommentToken::query()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'comment_id' => $comment->getKey(),
        'type' => CommentTokenType::ReplyNotificationOptOut,
        'token_hash' => hash('sha256', $rawToken),
        'expires_at' => null,
    ]);

    $this->get(route('capell-comments.reply-notifications.disable', ['token' => $rawToken]))
        ->assertSuccessful()
        ->assertSee(__('capell-comments::messages.reply_notifications_disabled'));

    expect($author->refresh()->reply_notifications_disabled_at)->not->toBeNull()
        ->and($token->refresh()->consumed_at)->not->toBeNull();
});
