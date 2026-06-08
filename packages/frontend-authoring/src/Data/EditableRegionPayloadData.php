<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Data;

use Capell\FrontendAuthoring\Enums\EditableRegionField;
use Capell\FrontendAuthoring\Enums\EditableRegionInputType;
use Capell\FrontendAuthoring\Enums\EditableRegionSurface;

final class EditableRegionPayloadData
{
    public function __construct(
        public string $model,
        public int $recordKey,
        public string $field,
        public string $label,
        public EditableRegionInputType $type,
        public string $selector,
        public string $currentUrl,
        public int $pageUrlId,
        public int $siteId,
        public int $languageId,
        public string $regionKey,
        public EditableRegionSurface $surface = EditableRegionSurface::Field,
        public ?string $target = null,
        public ?string $description = null,
        /** @var array<string, mixed> */
        public array $context = [],
        /** @var list<string> */
        public array $permissions = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            model: self::stringValue($payload, 'model'),
            recordKey: self::intValue($payload, 'recordKey'),
            field: self::stringValue($payload, 'field'),
            label: self::stringValue($payload, 'label'),
            type: EditableRegionInputType::from(self::stringValue($payload, 'type')),
            selector: self::stringValue($payload, 'selector'),
            currentUrl: self::stringValue($payload, 'currentUrl'),
            pageUrlId: self::intValue($payload, 'pageUrlId'),
            siteId: self::intValue($payload, 'siteId'),
            languageId: self::intValue($payload, 'languageId'),
            regionKey: self::stringValue($payload, 'regionKey'),
            surface: EditableRegionSurface::from(self::optionalStringValue($payload, 'surface') ?? EditableRegionSurface::Field->value),
            target: self::optionalStringValue($payload, 'target'),
            description: self::optionalStringValue($payload, 'description'),
            context: self::stringMap($payload['context'] ?? []),
            permissions: self::stringList($payload['permissions'] ?? []),
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
            'type' => $this->type->value,
            'selector' => $this->selector,
            'currentUrl' => $this->currentUrl,
            'pageUrlId' => $this->pageUrlId,
            'siteId' => $this->siteId,
            'languageId' => $this->languageId,
            'regionKey' => $this->regionKey,
            'surface' => $this->surface->value,
            'target' => $this->target,
            'description' => $this->description,
            'context' => $this->context,
            'permissions' => $this->permissions,
        ];
    }

    public function fieldKind(): EditableRegionField
    {
        return EditableRegionField::fromValue($this->field);
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (! is_string($item)) {
                continue;
            }

            $item = trim($item);

            if ($item !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function stringValue(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function optionalStringValue(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function intValue(array $payload, string $key): int
    {
        $value = $payload[$key] ?? null;

        if (is_int($value)) {
            return $value;
        }

        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }

    /**
     * @return array<string, mixed>
     */
    private static function stringMap(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $map = [];

        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $map[$key] = $item;
            }
        }

        return $map;
    }
}
