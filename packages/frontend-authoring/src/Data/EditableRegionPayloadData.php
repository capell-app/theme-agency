<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Data;

final class EditableRegionPayloadData
{
    public function __construct(
        public string $model,
        public int $recordKey,
        public string $field,
        public string $label,
        public string $type,
        public string $selector,
        public string $currentUrl,
        public int $pageUrlId,
        public int $siteId,
        public int $languageId,
        public string $regionKey,
        public string $surface = 'field',
        public ?string $target = null,
        public ?string $description = null,
        /** @var array<string, mixed> */
        public array $context = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            model: (string) $payload['model'],
            recordKey: (int) $payload['recordKey'],
            field: (string) $payload['field'],
            label: (string) $payload['label'],
            type: (string) $payload['type'],
            selector: (string) $payload['selector'],
            currentUrl: (string) $payload['currentUrl'],
            pageUrlId: (int) $payload['pageUrlId'],
            siteId: (int) $payload['siteId'],
            languageId: (int) $payload['languageId'],
            regionKey: (string) $payload['regionKey'],
            surface: (string) ($payload['surface'] ?? 'field'),
            target: isset($payload['target']) ? (string) $payload['target'] : null,
            description: isset($payload['description']) ? (string) $payload['description'] : null,
            context: is_array($payload['context'] ?? null) ? $payload['context'] : [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'recordKey' => $this->recordKey,
            'field' => $this->field,
            'label' => $this->label,
            'type' => $this->type,
            'selector' => $this->selector,
            'currentUrl' => $this->currentUrl,
            'pageUrlId' => $this->pageUrlId,
            'siteId' => $this->siteId,
            'languageId' => $this->languageId,
            'regionKey' => $this->regionKey,
            'surface' => $this->surface,
            'target' => $this->target,
            'description' => $this->description,
            'context' => $this->context,
        ];
    }
}
