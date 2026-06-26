<?php

declare(strict_types=1);

use Capell\AIOrchestrator\Contracts\AiActionContextInterface;
use Capell\AIOrchestrator\Data\Ai\AiGenerationResultData;
use Capell\AIOrchestrator\Events\Ai\AiGenerationCompleted;
use Capell\AIOrchestrator\Events\Ai\AiGenerationFailed;
use Capell\AIOrchestrator\Events\Ai\AiGenerationStarted;
use Capell\AIOrchestrator\Models\AIGenerationHistory;
use Capell\AIOrchestrator\Support\Ai\AiResponse;
use Capell\SeoSuite\Actions\ApplyAiDraftAction;
use Capell\SeoSuite\Actions\RecordAiGenerationAction;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

function makeSeoSuiteLifecycleContext(string $content = 'Draft content'): AiActionContextInterface
{
    return new readonly class($content) implements AiActionContextInterface
    {
        public function __construct(private string $content) {}

        public function getContent(): string
        {
            return $this->content;
        }

        public function getKeywords(): string
        {
            return 'seo';
        }

        public function getPageId(): int
        {
            return 99;
        }

        public function getPageType(): string
        {
            return 'page';
        }

        public function getLanguageId(): int
        {
            return 7;
        }
    };
}

it('applies ai draft content to saveable targets and dispatches lifecycle events', function (): void {
    Event::fake();
    Log::shouldReceive('info')->once();

    $target = new class
    {
        public ?string $content = null;

        public bool $saved = false;

        public function save(): bool
        {
            $this->saved = true;

            return true;
        }
    };

    $result = ApplyAiDraftAction::run(makeSeoSuiteLifecycleContext('Generated page body'), [
        'target' => $target,
    ]);

    expect($result)->toBeTrue()
        ->and($target->content)->toBe('Generated page body')
        ->and($target->saved)->toBeTrue();

    Event::assertDispatched(AiGenerationStarted::class);
    Event::assertDispatched(AiGenerationCompleted::class);
    Event::assertNotDispatched(AiGenerationFailed::class);
});

it('rejects ai draft targets without content properties', function (): void {
    Event::fake();
    Log::shouldReceive('error')->once();

    expect(fn (): bool => ApplyAiDraftAction::run(makeSeoSuiteLifecycleContext(), [
        'target' => new stdClass,
    ]))->toThrow(InvalidArgumentException::class, 'Target must have a content property');

    Event::assertDispatched(AiGenerationStarted::class);
    Event::assertDispatched(AiGenerationFailed::class);
    Event::assertNotDispatched(AiGenerationCompleted::class);
});

it('records ai generation arrays through the lifecycle wrapper', function (): void {
    Event::fake();
    Log::shouldReceive('info')->once();

    $history = RecordAiGenerationAction::run([
        'action' => 'GeneratePageTitleAction',
        'model' => 'gpt-4o-mini',
        'input' => 'Input text',
        'output' => 'Output text',
        'prompt_tokens' => 5,
        'completion_tokens' => 8,
        'total_tokens' => 13,
        'cost_micros' => 11,
        'duration' => 0.25,
        'metadata' => ['source' => 'unit'],
    ]);

    expect($history)->toBeInstanceOf(AIGenerationHistory::class)
        ->and($history->action)->toBe('GeneratePageTitleAction')
        ->and($history->cost_micros)->toBe(11)
        ->and($history->cost_currency)->toBe('USD')
        ->and($history->metadata)->toBe(['source' => 'unit']);

    Event::assertDispatched(AiGenerationStarted::class);
    Event::assertDispatched(AiGenerationCompleted::class);
    Event::assertNotDispatched(AiGenerationFailed::class);
});

it('records ai generation result data with response metadata and request details', function (): void {
    $history = RecordAiGenerationAction::run(new AiGenerationResultData(
        actionKey: 'GenerateMetaDescriptionAction',
        output: ['description' => 'Output text'],
        inputText: 'Input text',
        outputText: 'Output text',
        response: new AiResponse(
            content: 'Output text',
            tokensUsed: 21,
            model: 'gpt-4o-mini',
            duration: 0.5,
            metadata: ['prompt_tokens' => 9, 'completion_tokens' => 12, 'provider' => 'openai'],
        ),
        messages: [
            ['role' => 'user', 'content' => 'Write a description'],
        ],
        params: ['temperature' => 0.2],
        pageableId: 55,
        pageableType: 'page',
        languageId: 3,
        metadata: ['feature' => 'meta_description'],
        aiCreatorSessionId: 44,
    ));

    expect($history->action)->toBe('GenerateMetaDescriptionAction')
        ->and($history->model)->toBe('gpt-4o-mini')
        ->and($history->prompt_tokens)->toBe(9)
        ->and($history->completion_tokens)->toBe(12)
        ->and($history->total_tokens)->toBe(21)
        ->and($history->cost_currency)->toBe('USD')
        ->and($history->metadata)->toMatchArray([
            'provider' => 'openai',
            'feature' => 'meta_description',
            'ai_messages' => [
                ['role' => 'user', 'content' => 'Write a description'],
            ],
            'ai_params' => ['temperature' => 0.2],
            'ai_creator_session_id' => 44,
        ]);
});

it('estimates ai generation costs from model pricing when no explicit cost is supplied', function (): void {
    $history = RecordAiGenerationAction::run([
        'action' => 'GeneratePageTitleAction',
        'model' => 'gpt-4o',
        'input' => 'Input text',
        'output' => 'Output text',
        'prompt_tokens' => 1_000_000,
        'completion_tokens' => 1_000_000,
        'total_tokens' => 2_000_000,
        'duration' => 0.25,
        'metadata' => ['user_id' => 55],
    ]);

    expect($history->cost_micros)->toBe(20_000_000)
        ->and($history->cost_currency)->toBe('USD')
        ->and($history->created_by_user_id)->toBe(55);
});

it('records ai generation context input when no explicit result payload is supplied', function (): void {
    $history = RecordAiGenerationAction::run(makeSeoSuiteLifecycleContext('Context content'), [
        'source' => 'context',
    ]);

    expect($history->action)->toBe(RecordAiGenerationAction::class)
        ->and($history->input)->toBe('Context content')
        ->and($history->pageable_id)->toBe(99)
        ->and($history->pageable_type)->toBe('page')
        ->and($history->language_id)->toBe(7)
        ->and($history->metadata)->toBe(['source' => 'context']);
});

it('rejects unsupported ai generation record inputs', function (): void {
    Event::fake();
    Log::shouldReceive('error')->once();

    expect(fn (): AIGenerationHistory => RecordAiGenerationAction::run('invalid'))
        ->toThrow(InvalidArgumentException::class, 'Invalid input for RecordAiGenerationAction');

    Event::assertDispatched(AiGenerationStarted::class);
    Event::assertDispatched(AiGenerationFailed::class);
    Event::assertNotDispatched(AiGenerationCompleted::class);
});
