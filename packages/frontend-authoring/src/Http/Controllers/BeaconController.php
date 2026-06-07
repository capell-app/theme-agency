<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Http\Controllers;

use Capell\FrontendAuthoring\Actions\BuildBeaconResponseAction;
use Capell\FrontendAuthoring\Http\Requests\BeaconRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class BeaconController extends BaseController
{
    public function __invoke(BeaconRequest $request): JsonResponse
    {
        return BuildBeaconResponseAction::run($request);
    }
}
