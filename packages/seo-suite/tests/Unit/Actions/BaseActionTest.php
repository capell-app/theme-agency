<?php

declare(strict_types=1);

use Capell\SeoSuite\Actions\BaseAction;
use Capell\SeoSuite\Contracts\AiActionContextInterface;
use Capell\SeoSuite\Events\AiGenerationCompleted;
use Capell\SeoSuite\Events\AiGenerationFailed;
use Capell\SeoSuite\Events\AiGenerationStarted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

function makeSeoSuiteBaseActionContext(): AiActionContextInterface
{
    return new class implements AiActionContextInterface
    {
        public function getContent(): string
        {
            return 'Body copy';
        }

        public function getKeywords(): string
        {
            return 'capell, seo';
        }

        public function getPageId(): int
        {
            return 123;
        }

        public function getPageType(): string
        {
            return 'page';
        }

        public function getLanguageId(): int
        {
            return 1;
        }
    };
}

it('runs base ai actions through validation, events, logging, and metadata', function (): void {
    Event::fake();
    Log::shouldReceive('info')->once();

    $action = new class extends BaseAction
    {
        /**
         * @param  array<array-key, mixed>  $options
         */
        protected function perform(AiActionContextInterface $context, array $options = []): mixed
        {
            $this->setMetadata('keywords', $context->getKeywords());
            $this->setMetadata('tone', $options['tone'] ?? 'default');

            return 'Generated for ' . $context->getPageId();
        }
    };

    $result = $action->handle(makeSeoSuiteBaseActionContext(), ['tone' => 'direct']);

    expect($result)->toBe('Generated for 123')
        ->and($action->getMetadata())->toBe([
            'keywords' => 'capell, seo',
            'tone' => 'direct',
        ]);

    Event::assertDispatched(AiGenerationStarted::class);
    Event::assertDispatched(AiGenerationCompleted::class);
    Event::assertNotDispatched(AiGenerationFailed::class);
});

it('rejects malformed base ai action input before running hooks', function (): void {
    Event::fake();

    $action = new class extends BaseAction
    {
        public bool $performed = false;

        /**
         * @param  array<array-key, mixed>  $options
         */
        protected function perform(AiActionContextInterface $context, array $options = []): mixed
        {
            $this->performed = true;

            return null;
        }
    };

    expect(fn (): mixed => $action->handle(null, ['tone' => 'direct']))
        ->toThrow(InvalidArgumentException::class, 'Invalid AI action input');
    expect(fn (): mixed => $action->handle(makeSeoSuiteBaseActionContext(), 'tone=direct'))
        ->toThrow(InvalidArgumentException::class, 'Invalid AI action input');

    expect($action->performed)->toBeFalse();
    Event::assertNothingDispatched();
});

it('dispatches failure events and logs when base ai actions throw', function (): void {
    Event::fake();
    Log::shouldReceive('error')->once();

    $action = new class extends BaseAction
    {
        /**
         * @param  array<array-key, mixed>  $options
         */
        protected function perform(AiActionContextInterface $context, array $options = []): mixed
        {
            throw new RuntimeException('Provider unavailable');
        }
    };

    expect(fn (): mixed => $action->handle(makeSeoSuiteBaseActionContext()))
        ->toThrow(RuntimeException::class, 'Provider unavailable');

    Event::assertDispatched(AiGenerationStarted::class);
    Event::assertDispatched(AiGenerationFailed::class);
    Event::assertNotDispatched(AiGenerationCompleted::class);
});

it('supports static run dispatch through the container', function (): void {
    $action = new class extends BaseAction
    {
        /**
         * @param  array<array-key, mixed>  $options
         */
        protected function perform(AiActionContextInterface $context, array $options = []): mixed
        {
            return $options['prefix'] . ':' . $context->getLanguageId();
        }
    };

    app()->instance($action::class, $action);

    expect($action::run(makeSeoSuiteBaseActionContext(), ['prefix' => 'language']))
        ->toBe('language:1');
});
