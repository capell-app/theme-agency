<?php

declare(strict_types=1);

use Capell\Comments\Actions\CreateCommentAction;
use Capell\Comments\Actions\TransitionCommentStatusAction;
use Capell\Comments\Actions\VerifyCommentAuthorEmailAction;
use Capell\Comments\Data\CreateCommentData;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Events\CommentCreated;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;
use Capell\Comments\Notifications\ConfirmCommentAuthorEmailNotification;
use Capell\Comments\Settings\CommentSettings;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
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

it('dispatches an event when a comment is created', function (): void {
    Event::fake([CommentCreated::class]);
    Notification::fake();

    $page = $this->createCommentsPage();

    $comment = CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Hello world',
        authorName: 'Ben',
        authorEmail: 'ben@example.com',
        ipAddress: '192.0.2.10',
        userAgent: 'Comments test',
    ));

    Event::assertDispatched(
        CommentCreated::class,
        fn (CommentCreated $event): bool => $event->comment->is($comment),
    );
});

it('auto publishes only verified trusted authors when configured', function (): void {
    bindCommentSettings([
        'publication_policy' => CommentPublicationPolicy::AutoPublish->value,
    ]);

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
    bindCommentSettings([
        'publication_policy' => CommentPublicationPolicy::AutoPublish->value,
    ]);

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
    bindCommentSettings([
        'commentable_type_overrides' => [
            [
                'commentable_type' => 'page',
                'identity_mode' => 'authenticated',
            ],
        ],
    ]);

    $page = $this->createCommentsPage();

    CreateCommentAction::run(new CreateCommentData(
        commentable: $page,
        body: 'Blocked anonymous',
        authorName: 'Ben',
        authorEmail: 'ben@example.com',
    ));
})->throws(ValidationException::class);

it('rejects invalid comment submissions before persisting moderation records', function (): void {
    $unregisteredCommentable = new class extends Model
    {
        use HasFactory;
    };

    expectCommentValidation(function () use ($unregisteredCommentable): void {
        bindCommentSettings();

        CreateCommentAction::run(new CreateCommentData(
            commentable: $unregisteredCommentable,
            body: 'Invisible target',
            authorName: 'Ben',
            authorEmail: 'ben@example.com',
        ));
    }, 'commentable');

    expectCommentValidation(function (): void {
        bindCommentSettings(['enabled' => false]);

        CreateCommentAction::run(new CreateCommentData(
            commentable: $this->createCommentsPage(),
            body: 'Disabled globally',
            authorName: 'Ben',
            authorEmail: 'ben@example.com',
        ));
    }, 'commentable');

    expectCommentValidation(function (): void {
        bindCommentSettings([
            'publication_policy' => CommentPublicationPolicy::Disabled->value,
        ]);

        CreateCommentAction::run(new CreateCommentData(
            commentable: $this->createCommentsPage(),
            body: 'Publishing is disabled',
            authorName: 'Ben',
            authorEmail: 'ben@example.com',
        ));
    }, 'commentable');

    expectCommentValidation(function (): void {
        bindCommentSettings();

        $page = $this->createCommentsPage();
        CommentAuthor::factory()->blocked()->create([
            'site_id' => $page->site_id,
            'email' => 'blocked@example.com',
        ]);

        CreateCommentAction::run(new CreateCommentData(
            commentable: $page,
            body: 'Blocked author',
            authorName: 'Blocked',
            authorEmail: 'blocked@example.com',
        ));
    }, 'author');

    expectCommentValidation(function (): void {
        bindCommentSettings(['max_depth' => 0]);

        $page = $this->createCommentsPage();
        $parent = Comment::factory()->create([
            'site_id' => $page->site_id,
            'commentable_type' => $page->getMorphClass(),
            'commentable_id' => $page->getKey(),
            'depth' => 0,
        ]);

        CreateCommentAction::run(new CreateCommentData(
            commentable: $page,
            body: 'Too deep',
            authorName: 'Ben',
            authorEmail: 'ben@example.com',
            parentPublicId: $parent->public_id,
        ));
    }, 'parent');

    expectCommentValidation(function (): void {
        bindCommentSettings();

        CreateCommentAction::run(new CreateCommentData(
            commentable: $this->createCommentsPage(),
            body: '<p> </p>',
            authorName: 'Ben',
            authorEmail: 'ben@example.com',
        ));
    }, 'body');

    expectCommentValidation(function (): void {
        bindCommentSettings();

        CreateCommentAction::run(new CreateCommentData(
            commentable: $this->createCommentsPage(),
            body: 'Missing name',
            authorName: ' ',
            authorEmail: 'ben@example.com',
        ));
    }, 'authorName');

    expectCommentValidation(function (): void {
        bindCommentSettings();

        CreateCommentAction::run(new CreateCommentData(
            commentable: $this->createCommentsPage(),
            body: 'Bad email',
            authorName: 'Ben',
            authorEmail: 'not-an-email',
        ));
    }, 'authorEmail');
});

/**
 * @param  array<string, mixed>  $overrides
 */
function bindCommentSettings(array $overrides = []): void
{
    /** @var CommentSettings $settings */
    $settings = (new ReflectionClass(CommentSettings::class))->newInstanceWithoutConstructor();
    $settings->enabled = true;
    $settings->identity_mode = 'both';
    $settings->publication_policy = CommentPublicationPolicy::RequireApproval->value;
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

function expectCommentValidation(Closure $callback, string $field): void
{
    try {
        $callback();
    } catch (ValidationException $validationException) {
        expect($validationException->errors())->toHaveKey($field);

        return;
    }

    throw new RuntimeException(sprintf('Expected comment validation to fail for [%s].', $field));
}

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
    bindCommentSettings([
        'verification_flow' => 'moderate_then_verify',
    ]);

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
