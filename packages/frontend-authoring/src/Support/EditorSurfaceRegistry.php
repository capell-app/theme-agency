<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Support;

use Capell\FrontendAuthoring\Contracts\EditableRegionEditorSurface;
use InvalidArgumentException;

final class EditorSurfaceRegistry
{
    /**
     * @var array<string, EditableRegionEditorSurface>
     */
    private array $surfaces = [];

    public function register(EditableRegionEditorSurface $surface): void
    {
        $this->surfaces[$surface->surface()] = $surface;
    }

    public function surface(string $key): EditableRegionEditorSurface
    {
        return $this->surfaces[$key]
            ?? throw new InvalidArgumentException(sprintf('Editable region editor surface [%s] is not registered.', $key));
    }
}
