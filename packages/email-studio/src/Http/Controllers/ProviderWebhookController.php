<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Http\Controllers;

use Capell\EmailStudio\Actions\RecordProviderEventAction;
use Capell\EmailStudio\Actions\ResolveProviderWebhookProfileAction;
use Capell\EmailStudio\Actions\ValidateProviderWebhookSignatureAction;
use Capell\EmailStudio\Enums\EmailProviderType;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Support\EmailProviderRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProviderWebhookController
{
    public function __invoke(Request $request, string $token): JsonResponse
    {
        $profile = ResolveProviderWebhookProfileAction::run($token);

        abort_unless($profile instanceof EmailProfile, 404);
        abort_unless(ValidateProviderWebhookSignatureAction::run(
            $profile,
            $request->getContent(),
            $request->header('X-Capell-Email-Studio-Signature'),
        ), 401);

        $provider = $profile->provider instanceof EmailProviderType
            ? $profile->provider
            : EmailProviderType::from((string) $profile->provider);

        $payload = $request->json()->all();
        abort_unless(is_array($payload), 422);

        $eventData = resolve(EmailProviderRegistry::class)
            ->adapter($provider)
            ->normalizeWebhookPayload($payload, $this->headers($request));

        RecordProviderEventAction::run($profile, $eventData);

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, string>
     */
    private function headers(Request $request): array
    {
        $headers = [];

        foreach ($request->headers->all() as $name => $values) {
            $value = $values[0] ?? null;

            if (is_string($value)) {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
