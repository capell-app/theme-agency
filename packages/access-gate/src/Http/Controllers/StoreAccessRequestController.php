<?php

declare(strict_types=1);

namespace Capell\AccessGate\Http\Controllers;

use Capell\AccessGate\Actions\CreateRegistrationAction;
use Capell\AccessGate\Actions\ResolveAccessGateAreaForRequestAction;
use Capell\AccessGate\Enums\IdentityMode;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Support\AccessGateResponseHeaders;
use Capell\AccessGate\Support\RegistrationFieldRegistry;
use Capell\AccessGate\Support\RequestedUrlGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class StoreAccessRequestController
{
    public function __construct(
        private readonly CreateRegistrationAction $createRegistration,
        private readonly RegistrationFieldRegistry $fields,
        private readonly RequestedUrlGuard $requestedUrls,
    ) {}

    public function __invoke(Request $request, string $area): RedirectResponse
    {
        $accessArea = ResolveAccessGateAreaForRequestAction::run($request, $area);

        if (config('access-gate.registration.methods.email.enabled', true) !== true) {
            throw ValidationException::withMessages([
                'email' => __('capell-access-gate::public.request_unavailable'),
            ]);
        }

        $registration = $this->createRegistration->handle($accessArea, [
            ...$this->safePublicInput($request, $accessArea),
            'metadata' => [
                'ip_hash' => hash('sha256', (string) $request->ip()),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        $response = to_route('capell-access-gate.request', ['area' => $accessArea->key])
            ->with('access_gate_request_submitted', $accessArea->key)
            ->with('access_gate_registration_id', $registration->getKey())
            ->with('access_gate_status', __('capell-access-gate::public.request_submitted'));

        AccessGateResponseHeaders::noStore($response);

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function safePublicInput(Request $request, Area $area): array
    {
        $authenticatedEmail = $this->authenticatedEmail($request);

        $input = [
            'email' => $area->identity_mode === IdentityMode::Authenticated && $authenticatedEmail !== null
                ? $authenticatedEmail
                : $request->input('email'),
            'requested_url' => $this->requestedUrls->allowed($request, $area, $request->input('requested_url')),
        ];

        if ($area->identity_mode === IdentityMode::Authenticated && $request->user() !== null) {
            $input['user_id'] = $request->user()->getAuthIdentifier();
        }

        foreach ($this->fields->all() as $field) {
            $input[$field->key()] = $request->input($field->key());
        }

        return $input;
    }

    private function authenticatedEmail(Request $request): ?string
    {
        $email = data_get($request->user(), 'email');

        return is_string($email) && $email !== '' ? $email : null;
    }
}
