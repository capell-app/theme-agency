<?php

declare(strict_types=1);

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Providers\FrontendServiceProvider;
use Capell\Frontend\Data\MainContentRenderHookData;
use Capell\Frontend\Data\RenderHookContext;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\RenderHookRegistry;

it('auto injects only an anonymous-safe cached thread shell', function (): void {
    config()->set('capell-comments.auto_inject', true);

    $page = $this->createCommentsPage();
    $page->forceFill(['name' => 'Sensitive Editorial Page'])->save();

    $author = CommentAuthor::factory()->create([
        'site_id' => $page->site_id,
        'name' => 'Private Author',
        'email' => 'private-author@example.com',
    ]);

    Comment::factory()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Approved public comment loaded later',
    ]);

    Comment::factory()->pendingApproval()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Pending moderation comment',
    ]);

    Comment::factory()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'status' => CommentStatus::Spam,
        'body' => 'Spam moderation comment',
    ]);

    (new FrontendServiceProvider($this->app))->boot();

    /** @var RenderHookRegistry<RenderHookContext> $registry */
    $registry = resolve(RenderHookRegistry::class);
    $output = $registry->renderAll(
        RenderHookLocation::MainContent,
        new MainContentRenderHookData(layout: null, page: $page),
        scenario: 'frontend-main-layout',
        target: 'capell::layout.main',
    );

    expect($output)
        ->toContain('data-comments-thread')
        ->toContain('data-thread-key=')
        ->toContain(route('capell-comments.thread'))
        ->not->toContain('<livewire:')
        ->not->toContain('wire:snapshot')
        ->not->toContain('Approved public comment loaded later')
        ->not->toContain('Pending moderation comment')
        ->not->toContain('Spam moderation comment')
        ->not->toContain('Private Author')
        ->not->toContain('private-author@example.com')
        ->not->toContain('Sensitive Editorial Page')
        ->not->toContain('commentable_id')
        ->not->toContain('commentable_type')
        ->not->toContain('site_id')
        ->not->toContain('language_id')
        ->not->toContain('pending_approval')
        ->not->toContain('spam')
        ->not->toContain('filament')
        ->not->toContain('moderation')
        ->not->toContain('/admin');
});
