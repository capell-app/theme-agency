<?php

declare(strict_types=1);

use Capell\Comments\Actions\BuildPublicThreadAction;
use Capell\Comments\Actions\ResolvePublicCommentableThreadAction;
use Capell\Comments\Data\PublicCommentableThreadData;
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
            fn (PublicCommentData $comment): string => $comment->publicId,
            $comments,
        ))->not->toContain($second->public_id);
});

it('limits visible replies and reports when more replies are available', function (): void {
    $page = $this->createCommentsPage();
    $parent = Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Parent comment',
    ]);

    foreach (['First reply', 'Second reply', 'Third reply'] as $index => $body) {
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

    bindPublicThreadCommentSettings(['reply_page_size' => 2]);

    $comments = BuildPublicThreadAction::run($page);

    expect($comments)->toHaveCount(1)
        ->and($comments[0]->replyCount)->toBe(3)
        ->and($comments[0]->hasMoreReplies)->toBeTrue()
        ->and($comments[0]->children)->toHaveCount(2)
        ->and(array_map(
            fn (PublicCommentData $comment): string => $comment->body,
            $comments[0]->children,
        ))->toBe(['First reply', 'Second reply']);

    $expandedComments = BuildPublicThreadAction::run(
        commentable: $page,
        replyLimitsByPublicId: [(string) $parent->public_id => 3],
    );

    expect($expandedComments[0]->children)->toHaveCount(3)
        ->and($expandedComments[0]->hasMoreReplies)->toBeFalse();
});

it('scopes public comments to the commentable language when present', function (): void {
    $page = $this->createCommentsPage();
    $otherLanguage = Language::factory()->create();
    $site = $page->site()->firstOrFail();
    $languageId = (int) $site->language_id;
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

it('formats public comment timestamps using the commentable language locale', function (): void {
    config()->set('app.locale', 'en');

    $language = Language::factory()->french()->create();
    $site = $this->createCommentsSite();
    $site->forceFill(['language_id' => $language->getKey()])->save();
    $page = $this->createCommentsPage($site);
    $page->setAttribute('language_id', $language->getKey());

    $submittedAt = now()->subDays(2)->toImmutable();

    Comment::factory()->create([
        'site_id' => $page->site_id,
        'language_id' => $language->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'submitted_at' => $submittedAt,
        'body' => 'French timestamp',
    ]);

    $comments = BuildPublicThreadAction::run($page);

    expect($comments)->toHaveCount(1)
        ->and($comments[0]->submittedAtForHumans)->toBe($submittedAt->settings(['locale' => 'fr'])->diffForHumans())
        ->and($comments[0]->submittedAtForHumans)->not->toBe($submittedAt->settings(['locale' => 'en'])->diffForHumans());
});

it('serializes localized public timestamp labels through livewire', function (): void {
    $submittedAt = now()->subHour()->toImmutable();
    $comment = new PublicCommentData(
        publicId: 'public-comment',
        body: 'Visible',
        authorName: 'Ben',
        submittedAt: $submittedAt,
        submittedAtForHumans: 'il y a 1 heure',
        depth: 0,
        replyCount: 0,
        children: [],
    );

    $rehydrated = PublicCommentData::fromLivewire($comment->toLivewire());

    expect($rehydrated->submittedAtForHumans)->toBe('il y a 1 heure')
        ->and($rehydrated->submittedAt->toIso8601String())->toBe($submittedAt->toIso8601String());
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

it('does not resolve public threads when the commentable is not publicly visible', function (): void {
    $page = $this->createCommentsPage();
    $page->forceFill(['visible_from' => now()->addDay()])->save();

    Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Approved but hidden page',
    ]);

    expect(BuildPublicThreadAction::run($page))->toBe([])
        ->and(ResolvePublicCommentableThreadAction::run($page))->toBeNull();
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

it('resolves public commentable thread metadata and approved comments', function (): void {
    $page = $this->createCommentsPage();
    Comment::factory()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Visible thread comment',
    ]);

    $thread = ResolvePublicCommentableThreadAction::run($page);

    throw_unless($thread instanceof PublicCommentableThreadData, RuntimeException::class, 'Expected public comment thread data to resolve.');

    expect($thread)
        ->toBeInstanceOf(PublicCommentableThreadData::class)
        ->commentableType->toBe('page')
        ->siteId->toBe((int) $page->site_id)
        ->and($thread->comments)->toHaveCount(1)
        ->and($thread->comments[0]->body)->toBe('Visible thread comment');
});

it('escapes public comment output and hides pending comments when rendered', function (): void {
    $approved = new PublicCommentData(
        publicId: 'public-comment',
        body: '<script>alert("x")</script>',
        authorName: '<strong>Ben</strong>',
        submittedAt: now()->toImmutable(),
        submittedAtForHumans: '2 minutes ago',
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
    $settings->root_page_size = 20;
    $settings->reply_page_size = 5;
    $settings->site_overrides = [];
    $settings->commentable_type_overrides = [];

    foreach ($overrides as $property => $value) {
        $settings->{$property} = $value;
    }

    app()->instance(CommentSettings::class, $settings);
}
