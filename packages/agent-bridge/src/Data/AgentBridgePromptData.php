<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Data;

use Illuminate\Support\Arr;

final readonly class AgentBridgePromptData
{
    public function __construct(
        public string $goal,
        public string $area = 'other',
        public string $operation = 'inspect',
        public string $safety = 'preview_first',
        public string $target = '',
        public string $constraints = '',
        public string $successCriteria = '',
    ) {}

    /** @param array<string, mixed> $state */
    public static function fromArray(array $state): self
    {
        return new self(
            goal: trim((string) Arr::get($state, 'goal', '')),
            area: (string) Arr::get($state, 'area', 'other'),
            operation: (string) Arr::get($state, 'operation', 'inspect'),
            safety: (string) Arr::get($state, 'safety', 'preview_first'),
            target: trim((string) Arr::get($state, 'target', '')),
            constraints: trim((string) Arr::get($state, 'constraints', '')),
            successCriteria: trim((string) Arr::get($state, 'success_criteria', '')),
        );
    }

    /** @return array<string, string> */
    public function toFormState(): array
    {
        return [
            'goal' => $this->goal,
            'area' => $this->area,
            'operation' => $this->operation,
            'safety' => $this->safety,
            'target' => $this->target,
            'constraints' => $this->constraints,
            'success_criteria' => $this->successCriteria,
        ];
    }
}
