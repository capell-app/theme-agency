<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\SeoSuite\Contracts\AiActionContextInterface;
use Capell\SeoSuite\Data\Ai\AiGenerationInputData;
use Capell\SeoSuite\Events\AiGenerationCompleted;
use Capell\SeoSuite\Events\AiGenerationFailed;
use Capell\SeoSuite\Events\AiGenerationStarted;
use Capell\SeoSuite\Support\Pipelines\GenerateContentPipeline;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class GeneratorPageContentAction
{
    use AsAction;

    public function __construct(private readonly GenerateContentPipeline $pipeline) {}

    /**
     * @param  array{user_id?:int|null,current_title?:string|null,target_length?:int|null,refactor?:bool|null}  $options
     */
    public function handle(AiActionContextInterface $context, array $options = []): string
    {
        $startTime = microtime(true);
        Event::dispatch(new AiGenerationStarted(static::class, [$context, $options]));

        try {
            $input = AiGenerationInputData::forContextAction('GeneratorPageContentAction', $context, $options);
            $result = $this->pipeline->execute($input);

            $duration = microtime(true) - $startTime;

            Event::dispatch(new AiGenerationCompleted(static::class, $result->output, []));

            return (string) $result->output;
        } catch (Throwable $throwable) {
            Log::error('AI Action failed', [
                'action' => static::class,
                'error' => $throwable->getMessage(),
            ]);
            Event::dispatch(new AiGenerationFailed(static::class, $throwable));
            throw $throwable;
        }
    }
}
