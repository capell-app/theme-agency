<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Tests\Fixtures;

use Capell\SeoSuite\Support\AiResponse;
use Capell\SeoSuite\Support\PrismProvider;
use Override;

final class ContentBriefPrismProviderFake extends PrismProvider
{
    /** @var array<string, mixed> */
    public array $params = [];

    public function __construct(private readonly string $json)
    {
        parent::__construct(['max_retries' => 1]);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    #[Override]
    public function chat(array $params): AiResponse
    {
        $this->params = $params;

        return new AiResponse(
            content: $this->json,
            tokensUsed: 12,
            model: 'test-model',
            duration: 0.02,
            metadata: ['prompt_tokens' => 5, 'completion_tokens' => 7],
        );
    }
}
