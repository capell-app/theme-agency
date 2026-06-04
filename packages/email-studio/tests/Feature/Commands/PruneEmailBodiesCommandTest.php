<?php

declare(strict_types=1);

use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Models\EmailMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

afterEach(function (): void {
    Carbon::setTestNow();
});

it('runs body retention through the console command in json dry-run mode', function (): void {
    Carbon::setTestNow('2026-06-04 12:00:00');

    $message = EmailMessage::factory()->create([
        'status' => EmailMessageStatus::Sent,
        'rendered_html' => '<p>Retained</p>',
        'rendered_text' => 'Retained',
        'sent_at' => Carbon::now()->subDays(15),
    ]);

    $exitCode = Artisan::call('capell-email-studio:prune-bodies', [
        '--days' => '7',
        '--dry-run' => true,
        '--json' => true,
    ]);

    $output = json_decode((string) Artisan::output(), associative: true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($output)->toBe([
            'retention_days' => 7,
            'dry_run' => true,
            'matched_messages' => 1,
            'pruned_messages' => 0,
        ])
        ->and($message->refresh()->rendered_html)->toBe('<p>Retained</p>')
        ->and($message->rendered_text)->toBe('Retained');
});

it('clears retained rendered bodies through the console command', function (): void {
    Carbon::setTestNow('2026-06-04 12:00:00');

    $message = EmailMessage::factory()->create([
        'status' => EmailMessageStatus::Sent,
        'rendered_html' => '<p>PII</p>',
        'rendered_text' => 'PII',
        'sent_at' => Carbon::now()->subDays(15),
    ]);

    $this->artisan('capell-email-studio:prune-bodies', ['--days' => '7'])
        ->expectsOutputToContain('cleared 1 rendered body snapshot')
        ->assertSuccessful();

    expect($message->refresh()->rendered_html)->toBeNull()
        ->and($message->rendered_text)->toBeNull();
});

it('rejects invalid retention days before clearing bodies', function (): void {
    $message = EmailMessage::factory()->create([
        'status' => EmailMessageStatus::Sent,
        'rendered_html' => '<p>PII</p>',
        'rendered_text' => 'PII',
        'sent_at' => now()->subDays(15),
    ]);

    $this->artisan('capell-email-studio:prune-bodies', ['--days' => 'nope'])
        ->expectsOutput(__('capell-email-studio::package.commands.positive_integer', ['option' => '--days']))
        ->assertFailed();

    expect($message->refresh()->rendered_html)->toBe('<p>PII</p>')
        ->and($message->rendered_text)->toBe('PII');
});
