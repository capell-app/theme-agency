<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Data;

use Capell\FrontendAuthoring\Enums\EditableRegionInputType;
use Capell\FrontendAuthoring\Enums\EditableRegionSurface;

final class EditableRegionData
{
    public function __construct(
        public string $id,
        public string $label,
        public EditableRegionInputType $type,
        public string $selector,
        public string $editUrl,
        public EditableRegionSurface $surface = EditableRegionSurface::Field,
        public ?string $target = null,
        public ?string $description = null,
        /** @var array<string, mixed> */
        public array $context = [],
        /** @var list<string> */
        public array $permissions = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type->value,
            'selector' => $this->selector,
            'edit_url' => $this->editUrl,
            'surface' => $this->surface->value,
            'target' => $this->target,
            'description' => $this->description,
            'context' => $this->context,
            'permissions' => $this->permissions,
        ];
    }
}
