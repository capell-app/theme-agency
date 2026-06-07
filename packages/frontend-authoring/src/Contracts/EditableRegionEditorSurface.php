<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Contracts;

use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Enums\EditableRegionSurface;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\View\View;

interface EditableRegionEditorSurface
{
    public function surface(): EditableRegionSurface;

    public function render(EditableRegionPayloadData $payload, AuthenticatableContract $user): View;
}
