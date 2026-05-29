<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Http\Controllers;

use Capell\Frontend\Contracts\AdminAccessCheckerInterface;
use Capell\FrontendAuthoring\Actions\ValidateEditableRegionPayloadAction;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Capell\FrontendAuthoring\Support\EditorSurfaceRegistry;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class EditRegionController extends BaseController
{
    public function __invoke(Request $request, string $payload): View
    {
        $user = $this->authorizeAdmin($request);

        $region = ValidateEditableRegionPayloadAction::run(
            resolve(EditableRegionSigner::class)->decode($payload),
            $user,
        );

        return resolve(EditorSurfaceRegistry::class)
            ->surface($region->surface)
            ->render($region, $user);
    }

    private function authorizeAdmin(Request $request): AuthenticatableContract
    {
        $user = $request->user();

        abort_unless($user instanceof AuthenticatableContract, 403);
        abort_unless(resolve(AdminAccessCheckerInterface::class)->isAdmin($user), 403);

        return $user;
    }
}
