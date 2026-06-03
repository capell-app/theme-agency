<?php

declare(strict_types=1);

namespace Capell\AccessGate\Http\Controllers;

use Capell\AccessGate\Actions\ListAccessRequestMethodsAction;
use Capell\AccessGate\Actions\ResolveAccessGateAreaForRequestAction;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Support\AccessGateResponseHeaders;
use Capell\AccessGate\Support\RegistrationFieldRegistry;
use Capell\AccessGate\Support\RequestedUrlGuard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ShowAccessRequestController
{
    public function __construct(
        private readonly RegistrationFieldRegistry $fields,
        private readonly ListAccessRequestMethodsAction $listAccessRequestMethods,
        private readonly RequestedUrlGuard $requestedUrls,
    ) {}

    public function __invoke(Request $request, string $area): Response
    {
        $accessArea = ResolveAccessGateAreaForRequestAction::run($request, $area);

        $requestedUrl = $this->requestedUrls->allowed($request, $accessArea, $request->query('redirect'));

        $response = response()->view($accessArea->gate_view ?? 'capell-access-gate::request', [
            'area' => $accessArea,
            'fields' => $this->fields->all(),
            'requestMethods' => $this->listAccessRequestMethods->handle($accessArea, $requestedUrl),
            'emailRequestEnabled' => config('access-gate.registration.methods.email.enabled', true),
            'requestedUrl' => $requestedUrl,
            'submittedRegistration' => $this->submittedRegistration($request, $accessArea),
        ]);

        AccessGateResponseHeaders::noStore($response);

        return $response;
    }

    private function submittedRegistration(Request $request, Area $area): ?Registration
    {
        $registrationId = $request->session()->get('access_gate_registration_id');

        if (! is_numeric($registrationId)) {
            return null;
        }

        return Registration::query()
            ->whereKey((int) $registrationId)
            ->where('access_area_id', $area->getKey())
            ->first();
    }
}
