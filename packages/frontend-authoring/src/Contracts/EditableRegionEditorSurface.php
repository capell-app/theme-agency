<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Contracts;

use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\View\View;

interface EditableRegionEditorSurface
{
    public function surface(): string;

    public function render(EditableRegionPayloadData $payload, AuthenticatableContract $user): View;
}
