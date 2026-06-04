<?php

declare(strict_types=1);

use Capell\Comments\Actions\ApplyCommentPrivacyRetentionAction;
use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;

it('anonymizes a matching author while preserving public comment content', function (): void {
    $page = $this->createCommentsPage();
    $author = CommentAuthor::factory()->create([
        'site_id' => $page->site_id,
        'name' => 'Public Reader',
        'email' => 'reader@example.com',
        'internal_notes' => 'Asked for erasure',
    ]);
    $comment = Comment::factory()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Keep this public contribution',
        'visitor_ip_hash' => 'ip-hash',
        'visitor_user_agent_hash' => 'ua-hash',
        'moderation_note' => 'Contains private context',
    ]);
    $token = CommentToken::query()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'comment_id' => $comment->getKey(),
        'type' => CommentTokenType::VerifyEmail,
        'token_hash' => hash('sha256', 'erase-token'),
        'expires_at' => now()->addDay(),
    ]);

    $result = ApplyCommentPrivacyRetentionAction::run(email: 'reader@example.com');

    expect($result->matchedAuthors)->toBe(1)
        ->and($result->anonymizedAuthors)->toBe(1)
        ->and($result->matchedAuthorComments)->toBe(1)
        ->and($result->anonymizedAuthorComments)->toBe(1)
        ->and($result->matchedAuthorTokens)->toBe(1)
        ->and($result->deletedAuthorTokens)->toBe(1)
        ->and($result->affectedRecords())->toBe(3);

    expect($author->refresh())
        ->name->toBe(__('capell-comments::generic.anonymized_author'))
        ->email->toBeNull()
        ->email_hash->toBeNull()
        ->user_type->toBeNull()
        ->user_id->toBeNull()
        ->internal_notes->toBeNull();

    expect($comment->refresh())
        ->body->toBe('Keep this public contribution')
        ->visitor_ip_hash->toBeNull()
        ->visitor_user_agent_hash->toBeNull()
        ->moderation_note->toBeNull();

    expect(CommentToken::query()->whereKey($token->getKey())->exists())->toBeFalse();
});

it('prunes expired tokens and stale visitor identifiers by retention window', function (): void {
    $page = $this->createCommentsPage();
    $author = CommentAuthor::factory()->create(['site_id' => $page->site_id]);
    $oldComment = Comment::factory()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'submitted_at' => now()->subDays(45),
        'visitor_ip_hash' => 'old-ip',
        'visitor_user_agent_hash' => 'old-ua',
        'moderation_note' => 'Old note',
    ]);
    $recentComment = Comment::factory()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'submitted_at' => now()->subDays(5),
        'visitor_ip_hash' => 'recent-ip',
        'visitor_user_agent_hash' => 'recent-ua',
        'moderation_note' => 'Recent note',
    ]);
    $expiredToken = CommentToken::query()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'type' => CommentTokenType::VerifyEmail,
        'token_hash' => hash('sha256', 'expired-token'),
        'expires_at' => now()->subDays(31),
    ]);
    $activeToken = CommentToken::query()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'type' => CommentTokenType::VerifyEmail,
        'token_hash' => hash('sha256', 'active-token'),
        'expires_at' => now()->addDay(),
    ]);

    $result = ApplyCommentPrivacyRetentionAction::run(retentionDays: 30);

    expect($result->matchedExpiredTokens)->toBe(1)
        ->and($result->deletedExpiredTokens)->toBe(1)
        ->and($result->matchedStaleCommentIdentifiers)->toBe(1)
        ->and($result->prunedStaleCommentIdentifiers)->toBe(1);

    expect($oldComment->refresh())
        ->visitor_ip_hash->toBeNull()
        ->visitor_user_agent_hash->toBeNull()
        ->moderation_note->toBeNull();

    expect($recentComment->refresh())
        ->visitor_ip_hash->toBe('recent-ip')
        ->visitor_user_agent_hash->toBe('recent-ua')
        ->moderation_note->toBe('Recent note')
        ->and(CommentToken::query()->whereKey($expiredToken->getKey())->exists())->toBeFalse()
        ->and(CommentToken::query()->whereKey($activeToken->getKey())->exists())->toBeTrue();
});

it('reports privacy retention matches without changing data during dry runs', function (): void {
    $page = $this->createCommentsPage();
    $author = CommentAuthor::factory()->create([
        'site_id' => $page->site_id,
        'name' => 'Dry Run',
        'email' => 'dry@example.com',
        'internal_notes' => 'Keep until real run',
    ]);
    $comment = Comment::factory()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'submitted_at' => now()->subDays(45),
        'visitor_ip_hash' => 'dry-ip',
        'visitor_user_agent_hash' => 'dry-ua',
        'moderation_note' => 'Dry note',
    ]);

    $result = ApplyCommentPrivacyRetentionAction::run(
        retentionDays: 30,
        email: 'dry@example.com',
        dryRun: true,
    );

    expect($result->dryRun)->toBeTrue()
        ->and($result->matchedRecords())->toBeGreaterThan(0)
        ->and($result->affectedRecords())->toBe(0);

    expect($author->refresh())
        ->name->toBe('Dry Run')
        ->email->toBe('dry@example.com')
        ->email_hash->toBe(CommentAuthor::emailHash('dry@example.com'))
        ->internal_notes->toBe('Keep until real run');

    expect($comment->refresh())
        ->visitor_ip_hash->toBe('dry-ip')
        ->visitor_user_agent_hash->toBe('dry-ua')
        ->moderation_note->toBe('Dry note');
});
