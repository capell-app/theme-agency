<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Support\EditorSurfaces;

use Capell\FrontendAuthoring\Contracts\EditableRegionEditorSurface;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Enums\EditableRegionSurface;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

final class MediaEditorSurface implements EditableRegionEditorSurface
{
    public function surface(): EditableRegionSurface
    {
        return EditableRegionSurface::Media;
    }

    public function render(EditableRegionPayloadData $payload, AuthenticatableContract $user): View
    {
        return resolve(Factory::class)->make('capell::editor.region', [
            'payload' => resolve(EditableRegionSigner::class)->encode($payload),
            'title' => $payload->label,
            'description' => $payload->description ?? __('capell-frontend-authoring::authoring.media_editor_description'),
        ]);
    }
}
