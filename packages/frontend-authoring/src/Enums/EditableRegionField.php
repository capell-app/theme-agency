<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Enums;

enum EditableRegionField: string
{
    case Title = 'title';
    case Content = 'content';
    case Meta = 'meta';

    public static function fromValue(string $value): self
    {
        return self::tryFrom($value)
            ?? (str_starts_with($value, 'meta.') ? self::Meta : self::Title);
    }

    public function isDirectAttribute(): bool
    {
        return in_array($this, [self::Title, self::Content], true);
    }

    public function isMetaAttribute(): bool
    {
        return $this === self::Meta;
    }
}
