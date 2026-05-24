<?php

declare(strict_types=1);

use Capell\Comments\Actions\CreateCommentAction;
use Capell\Comments\Actions\TransitionCommentStatusAction;
use Capell\Comments\Actions\VerifyCommentAuthorEmailAction;
use Capell\Comments\Data\CreateCommentData;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;
use Capell\Comments\Notifications\ConfirmCommentAuthorEmailNotification;
use Capell\Comments\Settings\CommentSettings;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

it('creates anonymous comments as pending email verification by default', function (): void {
    Notification::fake();

    $page = $this->createCommentsPage();

    $comment = CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: '<script>alert("x")</script>Hello world',
        authorName: 'Ben',
        authorEmail: 'ben@example.com',
        ipAddress: '192.0.2.10',
        userAgent: 'Comments test',
    ));

    expect($comment->status)->toBe(CommentStatus::PendingEmailVerification)
        ->and($comment->body)->toBe('alert("x")Hello world')
        ->and($comment->visitor_ip_hash)->not->toBe(hash('sha256', '192.0.2.10'))
        ->and($comment->author->email)->toBe('ben@example.com');

    Notification::assertSentOnDemand(ConfirmCommentAuthorEmailNotification::class);
});

it('auto publishes only verified trusted authors when configured', function (): void {
    config()->set('capell-comments.publication_policy', CommentPublicationPolicy::AutoPublish->value);

    $page = $this->createCommentsPage();
    $user = User::factory()->create(['email' => 'trusted@example.com', 'email_verified_at' => now(), 'name' => 'Trusted User']);
    $author = CommentAuthor::factory()->trusted()->create([
        'site_id' => $page->site_id,
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->getKey(),
        'email' => $user->email,
        'name' => $user->name,
    ]);

    $comment = CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Trusted comment',
        user: $user,
    ));

    expect($comment->status)->toBe(CommentStatus::Approved);
});

it('does not let anonymous commenters inherit trusted author verification', function (): void {
    Notification::fake();
    config()->set('capell-comments.publication_policy', CommentPublicationPolicy::AutoPublish->value);

    $page = $this->createCommentsPage();
    $author = CommentAuthor::factory()->trusted()->create([
        'site_id' => $page->site_id,
        'email' => 'trusted@example.com',
        'name' => 'Trusted Author',
    ]);

    $comment = CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Impersonation attempt',
        authorName: 'Spoofed Name',
        authorEmail: $author->email,
    ));

    expect($comment->status)->toBe(CommentStatus::PendingEmailVerification)
        ->and($comment->email_verified_at)->toBeNull()
        ->and($comment->author->name)->toBe('Trusted Author')
        ->and($author->refresh()->name)->toBe('Trusted Author');

    Notification::assertSentOnDemand(ConfirmCommentAuthorEmailNotification::class);
});

it('binds a verified authenticated user to an existing anonymous author without duplicate email hash failures', function (): void {
    $page = $this->createCommentsPage();
    $anonymousAuthor = CommentAuthor::factory()->unverified()->create([
        'site_id' => $page->site_id,
        'email' => 'ben@example.com',
        'name' => 'Anonymous Ben',
    ]);
    $user = User::factory()->create([
        'email' => 'ben@example.com',
        'email_verified_at' => now(),
        'name' => 'Ben User',
    ]);

    $comment = CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Signed in now',
        user: $user,
    ));

    expect($comment->comment_author_id)->toBe($anonymousAuthor->getKey())
        ->and($anonymousAuthor->refresh()->user_id)->toBe($user->getKey())
        ->and(CommentAuthor::query()->where('site_id', $page->site_id)->where('email_hash', CommentAuthor::emailHash('ben@example.com'))->count())->toBe(1);
});

it('verifies email tokens once and moves pending email comments to pending approval', function (): void {
    Notification::fake();

    $page = $this->createCommentsPage();
    $comment = CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Needs token',
        authorName: 'Ben',
        authorEmail: 'ben@example.com',
    ));

    /** @var CommentToken $token */
    $token = CommentToken::query()->where('comment_id', $comment->getKey())->firstOrFail();
    $rawToken = 'verify-token';
    $token->forceFill(['token_hash' => hash('sha256', $rawToken)])->save();

    $author = VerifyCommentAuthorEmailAction::run($rawToken);

    expect($author)->toBeInstanceOf(CommentAuthor::class)
        ->and($token->refresh()->consumed_at)->not->toBeNull()
        ->and($comment->refresh()->status)->toBe(CommentStatus::PendingApproval)
        ->and(VerifyCommentAuthorEmailAction::run($rawToken))->toBeNull();
});

it('does not consume expired or wrong-purpose verification tokens', function (): void {
    Notification::fake();

    $page = $this->createCommentsPage();
    $comment = Comment::factory()->pendingEmailVerification()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ]);

    $expiredRawToken = 'expired-token';
    $expiredToken = CommentToken::query()->create([
        'site_id' => $comment->site_id,
        'comment_author_id' => $comment->comment_author_id,
        'comment_id' => $comment->getKey(),
        'type' => CommentTokenType::VerifyEmail,
        'token_hash' => hash('sha256', $expiredRawToken),
        'expires_at' => now()->subMinute(),
    ]);

    $wrongPurposeToken = CommentToken::query()->create([
        'site_id' => $comment->site_id,
        'comment_author_id' => $comment->comment_author_id,
        'comment_id' => $comment->getKey(),
        'type' => CommentTokenType::NotificationPreference,
        'token_hash' => hash('sha256', 'wrong-purpose'),
        'expires_at' => now()->addHour(),
    ]);

    expect(VerifyCommentAuthorEmailAction::run($expiredRawToken))->toBeNull()
        ->and(VerifyCommentAuthorEmailAction::run('wrong-purpose'))->toBeNull()
        ->and($expiredToken->refresh()->consumed_at)->toBeNull()
        ->and($wrongPurposeToken->refresh()->consumed_at)->toBeNull();
});

it('applies commentable-specific identity mode overrides during submission', function (): void {
    /** @var CommentSettings $settings */
    $settings = (new ReflectionClass(CommentSettings::class))->newInstanceWithoutConstructor();
    $settings->enabled = true;
    $settings->identity_mode = 'both';
    $settings->publication_policy = CommentPublicationPolicy::RequireApproval->value;
    $settings->verification_flow = 'verify_then_moderate';
    $settings->require_email_verification = true;
    $settings->max_depth = 4;
    $settings->site_overrides = [];
    $settings->commentable_type_overrides = [
        [
            'commentable_type' => 'page',
            'identity_mode' => 'authenticated',
        ],
    ];
    app()->instance(CommentSettings::class, $settings);

    $page = $this->createCommentsPage();

    CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Blocked anonymous',
        authorName: 'Ben',
        authorEmail: 'ben@example.com',
    ));
})->throws(ValidationException::class);

it('keeps authenticated comments pending email verification when the user email is unverified', function (): void {
    Notification::fake();

    $page = $this->createCommentsPage();
    $user = User::factory()->create([
        'email' => 'unverified@example.com',
        'email_verified_at' => null,
    ]);

    $comment = CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Signed in but unverified',
        user: $user,
    ));

    expect($comment->status)->toBe(CommentStatus::PendingEmailVerification)
        ->and($comment->email_verified_at)->toBeNull();

    Notification::assertSentOnDemand(ConfirmCommentAuthorEmailNotification::class);
});

it('does not approve comments before required email verification', function (): void {
    config()->set('capell-comments.verification_flow', 'moderate_then_verify');

    $page = $this->createCommentsPage();
    $comment = CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Needs verification',
        authorName: 'Ben',
        authorEmail: 'ben@example.com',
    ));

    expect($comment->status)->toBe(CommentStatus::PendingApproval);

    TransitionCommentStatusAction::run($comment, CommentStatus::Approved);
})->throws(ValidationException::class);

it('does not approve a reply until its parent is approved', function (): void {
    $page = $this->createCommentsPage();
    $parent = Comment::factory()->pendingApproval()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ]);

    TransitionCommentStatusAction::run($parent, CommentStatus::Approved);
    $reply = Comment::factory()->pendingApproval()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'parent_id' => $parent->getKey(),
        'root_id' => $parent->getKey(),
        'depth' => 1,
    ]);

    $approvedReply = TransitionCommentStatusAction::run($reply, CommentStatus::Approved);

    expect($approvedReply->status)->toBe(CommentStatus::Approved);
});

it('rejects replies to unavailable parents', function (): void {
    $page = $this->createCommentsPage();
    $parent = Comment::factory()->pendingApproval()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ]);

    CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Reply',
        authorName: 'Ben',
        authorEmail: 'ben@example.com',
        parentPublicId: $parent->public_id,
    ));
})->throws(ValidationException::class);
