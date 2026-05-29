<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Data;

final class EditableRegionData
{
    public function __construct(
        public string $id,
        public string $label,
        public string $type,
        public string $selector,
        public string $editUrl,
        public string $surface = 'field',
        public ?string $target = null,
        public ?string $description = null,
        /** @var array<string, mixed> */
        public array $context = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type,
            'selector' => $this->selector,
            'edit_url' => $this->editUrl,
            'surface' => $this->surface,
            'target' => $this->target,
            'description' => $this->description,
            'context' => $this->context,
        ];
    }
}
