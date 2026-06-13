<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\LiveChat\Actions\IndexLiveChatKnowledgeSourceAction;
use Capell\LiveChat\Actions\SearchLiveChatKnowledgeDocumentsAction;
use Capell\LiveChat\Actions\StartLiveChatConversationAction;
use Capell\LiveChat\Data\IncomingLiveChatMessageData;
use Capell\LiveChat\Data\LiveChatKnowledgeSearchResultData;
use Capell\LiveChat\Enums\KnowledgeSourceType;
use Capell\LiveChat\Enums\LiveChatAIRunStatus;
use Capell\LiveChat\Enums\LiveChatSourcePolicy;
use Capell\LiveChat\Enums\MessageRole;
use Capell\LiveChat\Models\LiveChatAIRun;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatKnowledgeGap;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-15 10:00:00', 'Europe/London'));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('answers from matched manual knowledge documents and audits source ids', function (): void {
    $installation = $this->createLiveChatInstallation();
    $source = LiveChatKnowledgeSource::query()->create([
        'site_id' => $installation->site_id,
        'type' => KnowledgeSourceType::Manual,
        'source_key' => 'support-sla',
        'title' => 'Support SLA',
        'content' => 'Our uptime SLA is 99.9 percent for hosted customer sites. Priority incidents are reviewed within one business hour.',
        'status' => 'active',
    ]);
    $document = (new IndexLiveChatKnowledgeSourceAction)->handle($source, $installation->id);

    expect($document)->toBeInstanceOf(LiveChatKnowledgeDocument::class);

    if (! $document instanceof LiveChatKnowledgeDocument) {
        throw new RuntimeException('Manual source did not produce a knowledge document.');
    }

    $result = (new StartLiveChatConversationAction)->handle(
        new IncomingLiveChatMessageData(
            body: 'What uptime SLA do you offer?',
            visitorToken: 'grounded-visitor-token',
        ),
        $installation->site_id,
        $installation,
    );

    $assistantMessage = $result['assistant_message'];
    $conversation = $result['conversation'];
    $aiRun = LiveChatAIRun::query()->where('conversation_id', $conversation->id)->latest('id')->firstOrFail();
    $assistantMetadata = $assistantMessage->metadata ?? [];

    expect($assistantMessage->body)->toContain('From Support SLA')
        ->and($assistantMessage->body)->toContain('99.9 percent')
        ->and($assistantMetadata['knowledge_sources'] ?? null)->toBe(['Support SLA'])
        ->and($assistantMetadata['source_document_ids'] ?? null)->toBe([$document->id])
        ->and($aiRun->status)->toBe(LiveChatAIRunStatus::Succeeded)
        ->and($aiRun->source_document_ids)->toBe([$document->id])
        ->and(LiveChatKnowledgeGap::query()->count())->toBe(0);
});

it('records no-source fallback responses as knowledge gaps', function (): void {
    $installation = $this->createLiveChatInstallation();

    $result = (new StartLiveChatConversationAction)->handle(
        new IncomingLiveChatMessageData(
            body: 'Do you provide bespoke museum onboarding workshops for curators?',
            visitorToken: 'gap-visitor-token',
        ),
        $installation->site_id,
        $installation,
    );

    $conversation = $result['conversation'];
    $aiRun = LiveChatAIRun::query()->where('conversation_id', $conversation->id)->latest('id')->firstOrFail();
    $gap = LiveChatKnowledgeGap::query()->firstOrFail();
    $gapMetadata = $gap->metadata ?? [];

    expect($aiRun->status)->toBe(LiveChatAIRunStatus::Fallback)
        ->and($aiRun->source_document_ids)->toBe([])
        ->and($gap->question)->toBe('Do you provide bespoke museum onboarding workshops for curators?')
        ->and($gap->source_area)->toBe('local-fallback')
        ->and($gapMetadata['reason'] ?? null)->toBe('no_matching_source');
});

it('contributes published AI-readable Knowledge Base articles when enabled for an installation', function (): void {
    $installation = $this->createLiveChatInstallation();
    $installation->forceFill([
        'source_policy' => LiveChatSourcePolicy::ManualAndKnowledgeBase,
    ])->save();
    $collection = KnowledgeBaseCollection::query()->create([
        'key' => 'support',
        'title' => 'Support',
        'slug' => 'support',
        'is_public' => true,
    ]);
    $article = KnowledgeBaseArticle::query()->create([
        'collection_id' => $collection->id,
        'title' => 'Password reset policy',
        'slug' => 'password-reset-policy',
        'summary' => 'Password resets require owner approval.',
        'status' => KnowledgeBaseArticleStatus::Published,
        'search_weight' => 80,
        'is_ai_readable' => true,
        'published_at' => CarbonImmutable::now(),
    ]);
    $version = KnowledgeBaseArticleVersion::query()->create([
        'article_id' => $article->id,
        'version' => 'v1',
        'title' => 'Password reset policy',
        'summary' => 'Password resets require owner approval.',
        'body' => '<p>Password resets require owner approval before access is restored.</p>',
        'published_at' => CarbonImmutable::now(),
    ]);
    $article->forceFill(['current_version_id' => $version->id])->save();
    $conversation = LiveChatConversation::query()->create([
        'site_id' => $installation->site_id,
        'installation_id' => $installation->id,
    ]);
    $message = $conversation->messages()->create([
        'role' => MessageRole::Visitor,
        'body' => 'How do password resets work?',
    ]);

    $results = (new SearchLiveChatKnowledgeDocumentsAction)->handle($conversation, $message);
    $firstResult = $results[0] ?? null;

    if (! $firstResult instanceof LiveChatKnowledgeSearchResultData) {
        throw new RuntimeException('Knowledge Base article did not produce a search result.');
    }

    $metadata = $firstResult->document->metadata ?? [];

    expect($results)->toHaveCount(1)
        ->and($firstResult->document->source_type)->toBe(KnowledgeSourceType::KnowledgeBase)
        ->and($firstResult->document->title)->toBe('Password reset policy')
        ->and($firstResult->document->content)->toContain('owner approval')
        ->and($metadata['provider'] ?? null)->toBe('knowledge-base');
});
