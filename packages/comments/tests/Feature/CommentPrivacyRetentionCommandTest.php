<?php

declare(strict_types=1);

use Capell\Comments\Enums\CommentTokenType;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

it('runs comment privacy retention in dry-run json mode', function (): void {
    $page = $this->createCommentsPage();
    $author = CommentAuthor::factory()->create(['site_id' => $page->site_id]);
    Comment::factory()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'submitted_at' => now()->subDays(40),
        'visitor_ip_hash' => 'old-ip',
    ]);
    CommentToken::query()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'type' => CommentTokenType::VerifyEmail,
        'token_hash' => hash('sha256', 'command-token'),
        'expires_at' => now()->subDays(31),
    ]);

    $exitCode = Artisan::call('capell-comments:privacy-retention', [
        '--days' => 30,
        '--dry-run' => true,
        '--json' => true,
    ]);

    $payload = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($payload['dry_run'] ?? null)->toBeTrue()
        ->and($payload['matched_records'] ?? null)->toBeGreaterThanOrEqual(2)
        ->and($payload['affected_records'] ?? null)->toBe(0);
});

it('rejects invalid comment privacy retention options', function (): void {
    $this->artisan('capell-comments:privacy-retention', ['--days' => 'nope'])
        ->expectsOutput(__('capell-comments::messages.privacy_retention_positive_integer', ['option' => '--days']))
        ->assertExitCode(Command::FAILURE);

    $this->artisan('capell-comments:privacy-retention', ['--email' => 'not-an-email'])
        ->expectsOutput(__('capell-comments::messages.privacy_retention_valid_email'))
        ->assertExitCode(Command::FAILURE);
});
