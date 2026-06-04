<?php

declare(strict_types=1);

use Capell\EmailStudio\Actions\PruneEmailBodiesAction;
use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Models\EmailMessage;
use Illuminate\Support\Carbon;

afterEach(function (): void {
    Carbon::setTestNow();
});

it('clears retained rendered bodies past the configured cutoff without deleting audit metadata', function (): void {
    Carbon::setTestNow('2026-06-04 12:00:00');
    config(['capell-email-studio.body_retention_days' => 30]);

    $expiredSent = EmailMessage::factory()->create([
        'status' => EmailMessageStatus::Sent,
        'subject' => 'Expired sent message',
        'preview_text' => 'Support preview',
        'rendered_html' => '<p>PII</p>',
        'rendered_text' => 'PII',
        'context_snapshot' => ['order_id' => 123],
        'headers' => ['X-Test' => 'yes'],
        'sent_at' => Carbon::now()->subDays(31),
        'failed_at' => null,
    ]);

    $freshSent = EmailMessage::factory()->create([
        'status' => EmailMessageStatus::Sent,
        'rendered_html' => '<p>Fresh</p>',
        'rendered_text' => 'Fresh',
        'sent_at' => Carbon::now()->subDays(29),
        'failed_at' => null,
    ]);

    $expiredFailed = EmailMessage::factory()->create([
        'status' => EmailMessageStatus::Failed,
        'rendered_html' => '<p>Failed PII</p>',
        'rendered_text' => 'Failed PII',
        'sent_at' => null,
        'failed_at' => Carbon::now()->subDays(31),
        'failure_reason' => 'Provider failed.',
    ]);

    $oldQueued = EmailMessage::factory()->create([
        'status' => EmailMessageStatus::Queued,
        'rendered_html' => '<p>Still needed</p>',
        'rendered_text' => 'Still needed',
        'created_at' => Carbon::now()->subDays(60),
        'sent_at' => null,
        'failed_at' => null,
    ]);

    $result = PruneEmailBodiesAction::run();

    expect($result->retentionDays)->toBe(30)
        ->and($result->matchedMessages)->toBe(2)
        ->and($result->prunedMessages)->toBe(2);

    $expiredSent->refresh();
    $freshSent->refresh();
    $expiredFailed->refresh();
    $oldQueued->refresh();

    expect($expiredSent->rendered_html)->toBeNull()
        ->and($expiredSent->rendered_text)->toBeNull()
        ->and($expiredSent->subject)->toBe('Expired sent message')
        ->and($expiredSent->preview_text)->toBe('Support preview')
        ->and($expiredSent->context_snapshot)->toBe(['order_id' => 123])
        ->and($expiredSent->headers)->toBe(['X-Test' => 'yes'])
        ->and($freshSent->rendered_html)->toBe('<p>Fresh</p>')
        ->and($freshSent->rendered_text)->toBe('Fresh')
        ->and($expiredFailed->rendered_html)->toBeNull()
        ->and($expiredFailed->rendered_text)->toBeNull()
        ->and($expiredFailed->failure_reason)->toBe('Provider failed.')
        ->and($oldQueued->rendered_html)->toBe('<p>Still needed</p>')
        ->and($oldQueued->rendered_text)->toBe('Still needed');
});

it('reports matches without clearing rendered bodies during dry runs', function (): void {
    Carbon::setTestNow('2026-06-04 12:00:00');

    $message = EmailMessage::factory()->create([
        'status' => EmailMessageStatus::Sent,
        'rendered_html' => '<p>Retained</p>',
        'rendered_text' => 'Retained',
        'sent_at' => Carbon::now()->subDays(10),
    ]);

    $result = PruneEmailBodiesAction::run(retentionDays: 7, dryRun: true);

    expect($result->matchedMessages)->toBe(1)
        ->and($result->prunedMessages)->toBe(0)
        ->and($message->refresh()->rendered_html)->toBe('<p>Retained</p>')
        ->and($message->rendered_text)->toBe('Retained');
});
