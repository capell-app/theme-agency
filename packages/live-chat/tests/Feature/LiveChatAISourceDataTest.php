<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\LiveChat\Actions\IndexLiveChatKnowledgeSourceAction;
use Capell\LiveChat\Actions\RecordLiveChatAIRunAction;
use Capell\LiveChat\Actions\RecordLiveChatKnowledgeGapAction;
use Capell\LiveChat\Data\LiveChatAIRunData;
use Capell\LiveChat\Enums\LiveChatAIRunStatus;
use Capell\LiveChat\Enums\LiveChatKnowledgeGapStatus;
use Capell\LiveChat\Models\LiveChatAIRun;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatKnowledgeGap;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-15 10:00:00', 'Europe/London'));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('indexes encrypted knowledge source content into searchable documents', function (): void {
    $installation = $this->createLiveChatInstallation();
    $source = LiveChatKnowledgeSource::query()->create([
        'site_id' => $installation->site_id,
        'type' => 'website',
        'source_key' => 'pricing',
        'title' => 'Pricing page',
        'url' => 'https://example.test/pricing',
        'content' => '<h1>Pricing</h1> <p>Plans start at 49 pounds per month.</p>',
        'status' => 'active',
    ]);

    $document = (new IndexLiveChatKnowledgeSourceAction)->handle($source, $installation->id);

    expect($document)->toBeInstanceOf(LiveChatKnowledgeDocument::class)
        ->and($document)->not->toBeNull();

    if (! $document instanceof LiveChatKnowledgeDocument) {
        return;
    }

    expect($document->installation_id)->toBe($installation->id)
        ->and($document->source_id)->toBe($source->id)
        ->and($document->site_id)->toBe($installation->site_id)
        ->and($document->content)->toBe('Pricing Plans start at 49 pounds per month.')
        ->and($document->content_hash)->toBe(hash('sha256', $document->content))
        ->and($document->metadata)->toBe(['source_id' => $source->id])
        ->and($document->last_synced_at?->toDateTimeString())->toBe('2026-06-15 09:00:00');

    $source->refresh();

    expect($source->content_hash)->toBe($document->content_hash)
        ->and($source->last_synced_at?->toDateTimeString())->toBe('2026-06-15 09:00:00');

    $storedSourceContent = DB::table($source->getTable())->where('id', $source->id)->value('content');

    expect($storedSourceContent)->not->toBe('<h1>Pricing</h1> <p>Plans start at 49 pounds per month.</p>');
});

it('records AI run audit data with encrypted payload fields', function (): void {
    $installation = $this->createLiveChatInstallation();
    $document = LiveChatKnowledgeDocument::query()->create([
        'installation_id' => $installation->id,
        'site_id' => $installation->site_id,
        'source_type' => 'manual',
        'source_key' => 'pricing',
        'title' => 'Pricing',
        'content' => 'Plans start at 49 pounds per month.',
        'content_hash' => hash('sha256', 'Plans start at 49 pounds per month.'),
        'status' => 'active',
    ]);

    $run = (new RecordLiveChatAIRunAction)->handle(new LiveChatAIRunData(
        capabilityKey: 'live-chat-local-answer',
        installationId: $installation->id,
        modelTier: 'local',
        confidence: 0.87,
        latencyMs: 43,
        status: LiveChatAIRunStatus::Fallback,
        sourceDocumentIds: [$document->id],
        refusalReason: 'low confidence',
        inputPayload: ['question' => 'How much?'],
        outputPayload: ['answer' => 'Plans start at 49 pounds.'],
    ));

    expect($run)->toBeInstanceOf(LiveChatAIRun::class)
        ->and($run->installation_id)->toBe($installation->id)
        ->and($run->status)->toBe(LiveChatAIRunStatus::Fallback)
        ->and($run->source_document_ids)->toBe([$document->id])
        ->and($run->input_payload)->toBe(['question' => 'How much?'])
        ->and($run->output_payload)->toBe(['answer' => 'Plans start at 49 pounds.'])
        ->and($run->refusal_reason)->toBe('low confidence');

    $storedInputPayload = DB::table($run->getTable())->where('id', $run->id)->value('input_payload');

    expect($storedInputPayload)->not->toContain('How much?');
});

it('aggregates repeated knowledge gaps by normalized question hash', function (): void {
    $installation = $this->createLiveChatInstallation();

    $firstGap = (new RecordLiveChatKnowledgeGapAction)->handle(
        question: '  What integrations do you support? ',
        installationId: $installation->id,
        sourceArea: 'local-answer',
        metadata: ['reason' => 'no source matched'],
    );
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-15 10:05:00', 'Europe/London'));

    $secondGap = (new RecordLiveChatKnowledgeGapAction)->handle(
        question: "what   integrations do you support?\n",
        installationId: $installation->id,
        metadata: ['latest_message_id' => 44],
    );

    expect($secondGap->is($firstGap))->toBeTrue()
        ->and(LiveChatKnowledgeGap::query()->count())->toBe(1)
        ->and($secondGap->question)->toBe('What integrations do you support?')
        ->and($secondGap->status)->toBe(LiveChatKnowledgeGapStatus::Open)
        ->and($secondGap->occurrence_count)->toBe(2)
        ->and($secondGap->source_area)->toBe('local-answer')
        ->and($secondGap->first_seen_at?->toDateTimeString())->toBe('2026-06-15 09:00:00')
        ->and($secondGap->last_seen_at?->toDateTimeString())->toBe('2026-06-15 09:05:00')
        ->and($secondGap->metadata)->toBe([
            'reason' => 'no source matched',
            'latest_message_id' => 44,
        ]);
});
