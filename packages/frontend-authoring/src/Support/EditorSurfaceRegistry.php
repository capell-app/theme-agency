<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Support;

use Capell\FrontendAuthoring\Contracts\EditableRegionEditorSurface;
use Capell\FrontendAuthoring\Enums\EditableRegionSurface;
use InvalidArgumentException;

final class EditorSurfaceRegistry
{
    /**
     * @var array<string, EditableRegionEditorSurface>
     */
    private array $surfaces = [];

    public function register(EditableRegionEditorSurface $surface): void
    {
        $this->surfaces[$surface->surface()->value] = $surface;
    }

    public function surface(EditableRegionSurface $surface): EditableRegionEditorSurface
    {
        return $this->surfaces[$surface->value]
            ?? throw new InvalidArgumentException(sprintf('Editable region editor surface [%s] is not registered.', $surface->value));
    }
}
