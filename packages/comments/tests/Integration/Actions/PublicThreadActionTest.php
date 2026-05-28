<?php

declare(strict_types=1);

use Capell\Comments\Actions\BuildPublicThreadAction;
use Capell\Comments\Data\PublicCommentData;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Capell\Comments\Settings\CommentSettings;
use Capell\Core\Models\Language;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;

it('returns only approved comment DTOs for public rendering', function (): void {
    $page = $this->createCommentsPage();
    $approved = Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Visible comment',
    ]);

    Comment::factory()->pendingApproval()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Hidden comment',
    ]);

    $comments = BuildPublicThreadAction::run($page);

    expect($comments)->toHaveCount(1)
        ->and($comments[0]->publicId)->toBe($approved->public_id)
        ->and($comments[0]->body)->toBe('Visible comment')
        ->and($comments[0])->not->toHaveProperty('status')
        ->and($comments[0])->not->toHaveProperty('commentable_id');
});

it('builds nested approved replies without exposing pending replies', function (): void {
    $page = $this->createCommentsPage();
    $parent = Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ]);

    Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'parent_id' => $parent->getKey(),
        'root_id' => $parent->getKey(),
        'depth' => 1,
        'body' => 'Approved reply',
    ]);

    Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'parent_id' => $parent->getKey(),
        'root_id' => $parent->getKey(),
        'depth' => 1,
        'status' => CommentStatus::Spam,
        'body' => 'Spam reply',
    ]);

    $comments = BuildPublicThreadAction::run($page);

    expect($comments[0]->children)->toHaveCount(1)
        ->and($comments[0]->children[0]->body)->toBe('Approved reply');
});

it('limits root comments before loading replies', function (): void {
    $page = $this->createCommentsPage();
    $first = Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'submitted_at' => now()->subMinutes(2),
        'body' => 'First root',
    ]);
    $second = Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'submitted_at' => now()->subMinute(),
        'body' => 'Second root',
    ]);
    Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'parent_id' => $first->getKey(),
        'root_id' => $first->getKey(),
        'depth' => 1,
        'body' => 'Included reply',
    ]);

    $comments = BuildPublicThreadAction::run($page, rootLimit: 1);

    expect($comments)->toHaveCount(1)
        ->and($comments[0]->publicId)->toBe($first->public_id)
        ->and($comments[0]->children)->toHaveCount(1)
        ->and(array_map(
            fn (object $comment): mixed => $comment->publicId,
            $comments,
        ))->not->toContain($second->public_id);
});

it('scopes public comments to the commentable language when present', function (): void {
    $page = $this->createCommentsPage();
    $otherLanguage = Language::factory()->create();
    $languageId = (int) $page->site->language_id;
    $page->setAttribute('language_id', $languageId);

    Comment::factory()->create([
        'site_id' => $page->site_id,
        'language_id' => $languageId,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'English comment',
    ]);
    Comment::factory()->create([
        'site_id' => $page->site_id,
        'language_id' => $otherLanguage->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Welsh comment',
    ]);

    $comments = BuildPublicThreadAction::run($page);

    expect($comments)->toHaveCount(1)
        ->and($comments[0]->body)->toBe('English comment');
});

it('does not expose approved comments when comments are disabled for public reads', function (): void {
    $page = $this->createCommentsPage();

    Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Approved but disabled',
    ]);

    bindPublicThreadCommentSettings(['enabled' => false]);

    expect(BuildPublicThreadAction::run($page))->toBe([]);
});

it('does not expose approved comments when public publication is disabled for the commentable type', function (): void {
    $page = $this->createCommentsPage();

    Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Approved but unavailable',
    ]);

    bindPublicThreadCommentSettings(['publication_policy' => CommentPublicationPolicy::Disabled->value]);

    expect(BuildPublicThreadAction::run($page))->toBe([]);
});

it('escapes public comment output and hides pending comments when rendered', function (): void {
    $approved = new PublicCommentData(
        publicId: 'public-comment',
        body: '<script>alert("x")</script>',
        authorName: '<strong>Ben</strong>',
        submittedAt: now()->toImmutable(),
        depth: 0,
        replyCount: 0,
        children: [],
    );

    $html = Blade::render(
        '@include("capell-comments::livewire.thread", ["comments" => [$approved], "submitted" => false, "parentPublicId" => null])',
        ['approved' => $approved, 'errors' => new ViewErrorBag],
    );

    expect($html)->toContain('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;')
        ->and($html)->toContain('&lt;strong&gt;Ben&lt;/strong&gt;')
        ->and($html)->not->toContain('<script>alert("x")</script>')
        ->and($html)->not->toContain('pending_approval')
        ->and($html)->not->toContain('commentable_id');
});

it('keeps auto injected cached shell free of livewire component state', function (): void {
    $html = Blade::render('@include("capell-comments::livewire.thread-shell", ["threadKey" => "opaque-thread-key"])');

    expect($html)->toContain('opaque-thread-key')
        ->and($html)->not->toContain('<livewire:')
        ->and($html)->not->toContain('capell-comments::thread')
        ->and($html)->not->toContain('wire:snapshot')
        ->and($html)->not->toContain('commentable_id');
});

/**
 * @param  array<string, mixed>  $overrides
 */
function bindPublicThreadCommentSettings(array $overrides): void
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
