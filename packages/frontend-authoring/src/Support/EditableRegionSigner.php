<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Support;

use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Illuminate\Support\Facades\URL;

final class EditableRegionSigner
{
    public function idFor(EditableRegionPayloadData $payload): string
    {
        return $this->signatureFor($payload->toArray());
    }

    public function encode(EditableRegionPayloadData $payload): string
    {
        $data = $payload->toArray();

        return rtrim(strtr(base64_encode(json_encode([
            'data' => $data,
            'signature' => $this->signatureFor($data),
        ], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
    }

    public function decode(string $encodedPayload): EditableRegionPayloadData
    {
        $json = base64_decode(strtr($encodedPayload, '-_', '+/'), true);

        abort_unless(is_string($json), 403);

        $payload = json_decode($json, true);

        abort_if(! is_array($payload) || ! isset($payload['data'], $payload['signature']) || ! is_array($payload['data']), 403);
        abort_unless($this->hasRequiredData($payload['data']), 403);

        $signature = (string) $payload['signature'];

        abort_unless(hash_equals($this->signatureFor($payload['data']), $signature), 403);

        return EditableRegionPayloadData::fromArray($payload['data']);
    }

    public function signedEditUrl(EditableRegionPayloadData $payload): string
    {
        return URL::temporarySignedRoute(
            'capell-frontend.authoring.edit',
            now()->addMinutes(15),
            ['payload' => $this->encode($payload)],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasRequiredData(array $data): bool
    {
        return collect([
            'model',
            'recordKey',
            'field',
            'label',
            'type',
            'selector',
            'currentUrl',
            'pageUrlId',
            'siteId',
            'languageId',
            'regionKey',
        ])->every(fn (string $key): bool => array_key_exists($key, $data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function signatureFor(array $data): string
    {
        return hash_hmac('sha256', json_encode($data, JSON_THROW_ON_ERROR), (string) config('app.key'));
    }
}
