<?php

declare(strict_types=1);

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentToken;

it('shows a verification confirmation page without consuming the token', function (): void {
    $page = $this->createCommentsPage();
    $comment = Comment::factory()->pendingEmailVerification()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ]);
    $rawToken = 'route-token';
    $token = CommentToken::query()->create([
        'site_id' => $comment->site_id,
        'comment_author_id' => $comment->comment_author_id,
        'comment_id' => $comment->getKey(),
        'type' => CommentTokenType::VerifyEmail,
        'token_hash' => hash('sha256', $rawToken),
        'expires_at' => now()->addHour(),
    ]);

    $this->get(route('capell-comments.verify', ['token' => $rawToken]))
        ->assertSuccessful()
        ->assertSee(__('capell-comments::messages.verify_email_confirmation'));

    expect($token->refresh()->consumed_at)->toBeNull()
        ->and($comment->refresh()->status)->toBe(CommentStatus::PendingEmailVerification);
});

it('consumes verification tokens only after post confirmation', function (): void {
    $page = $this->createCommentsPage();
    $comment = Comment::factory()->pendingEmailVerification()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ]);
    $rawToken = 'confirmed-route-token';
    $token = CommentToken::query()->create([
        'site_id' => $comment->site_id,
        'comment_author_id' => $comment->comment_author_id,
        'comment_id' => $comment->getKey(),
        'type' => CommentTokenType::VerifyEmail,
        'token_hash' => hash('sha256', $rawToken),
        'expires_at' => now()->addHour(),
    ]);

    $this->post(route('capell-comments.verify.store', ['token' => $rawToken]))
        ->assertRedirect(route('capell-comments.verify', ['token' => $rawToken]));

    expect($token->refresh()->consumed_at)->not->toBeNull()
        ->and($comment->refresh()->status)->toBe(CommentStatus::PendingApproval);
});

it('serves hydrated thread markup with no-store cache headers', function (): void {
    $response = $this->get(route('capell-comments.thread', ['thread' => 'opaque-thread-key']))
        ->assertSuccessful();

    expect((string) $response->baseResponse->headers->get('Cache-Control'))->toContain('no-store');
});
