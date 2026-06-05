<?php

declare(strict_types=1);

use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Symfony\Component\HttpKernel\Exception\HttpException;

function frontendAuthoringSignerPayload(): EditableRegionPayloadData
{
    return new EditableRegionPayloadData(
        model: 'App\\Models\\Translation',
        recordKey: 42,
        field: 'title',
        label: 'Page title',
        type: 'text',
        selector: '#main h1:first-of-type',
        currentUrl: 'https://example.test/about',
        pageUrlId: 7,
        siteId: 1,
        languageId: 1,
        regionKey: 'page.title',
    );
}

function frontendAuthoringSignerEncodeValue(mixed $value): string
{
    return rtrim(strtr(base64_encode(json_encode($value, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
}

/**
 * @param  array<string, mixed>  $envelope
 */
function frontendAuthoringSignerEncodeEnvelope(array $envelope): string
{
    return frontendAuthoringSignerEncodeValue($envelope);
}

it('round-trips a signed payload through encode and decode', function (): void {
    $signer = new EditableRegionSigner;
    $payload = frontendAuthoringSignerPayload();

    $encoded = $signer->encode($payload);

    expect($signer->decode($encoded)->toArray())->toBe($payload->toArray());
});

it('rejects a malformed base64 payload', function (): void {
    $signer = new EditableRegionSigner;

    expect(fn (): EditableRegionPayloadData => $signer->decode('not valid base64 @@@@'))
        ->toThrow(HttpException::class);
});

it('rejects a payload that is not the expected envelope shape', function (): void {
    $signer = new EditableRegionSigner;

    $encoded = frontendAuthoringSignerEncodeValue(['unexpected' => true]);

    expect(fn (): EditableRegionPayloadData => $signer->decode($encoded))
        ->toThrow(HttpException::class);
});

it('rejects encoded payloads that decode to unsupported formats', function (mixed $value): void {
    $signer = new EditableRegionSigner;

    expect(fn (): EditableRegionPayloadData => $signer->decode(frontendAuthoringSignerEncodeValue($value)))
        ->toThrow(HttpException::class);
})->with([
    'null json' => [null],
    'scalar json' => ['payload'],
    'list json' => [['data', 'signature']],
    'non-array data' => [[
        'data' => 'not-an-array',
        'signature' => str_repeat('a', 64),
    ]],
    'missing signature' => [[
        'data' => frontendAuthoringSignerPayload()->toArray(),
    ]],
]);

it('rejects a payload missing required data keys', function (): void {
    $signer = new EditableRegionSigner;

    $incompleteData = ['model' => 'App\\Models\\Translation', 'recordKey' => 1];
    $envelope = [
        'data' => $incompleteData,
        'signature' => hash_hmac('sha256', json_encode($incompleteData, JSON_THROW_ON_ERROR), (string) config('app.key')),
    ];
    $encoded = frontendAuthoringSignerEncodeEnvelope($envelope);

    expect(fn (): EditableRegionPayloadData => $signer->decode($encoded))
        ->toThrow(HttpException::class);
});

it('rejects a payload whose signature does not match the data', function (): void {
    $signer = new EditableRegionSigner;
    $payload = frontendAuthoringSignerPayload();

    $encoded = $signer->encode($payload);

    $decodedEnvelope = json_decode(
        (string) base64_decode(strtr($encoded, '-_', '+/'), true),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
    $decodedEnvelope['signature'] = str_repeat('0', strlen((string) $decodedEnvelope['signature']));
    $tampered = frontendAuthoringSignerEncodeEnvelope($decodedEnvelope);

    expect(fn (): EditableRegionPayloadData => $signer->decode($tampered))
        ->toThrow(HttpException::class);
});

it('rejects a payload with a truncated signature', function (): void {
    $signer = new EditableRegionSigner;
    $payload = frontendAuthoringSignerPayload();

    $decodedEnvelope = json_decode(
        (string) base64_decode(strtr($signer->encode($payload), '-_', '+/'), true),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
    $decodedEnvelope['signature'] = substr((string) $decodedEnvelope['signature'], 0, -1);
    $tampered = frontendAuthoringSignerEncodeEnvelope($decodedEnvelope);

    expect(fn (): EditableRegionPayloadData => $signer->decode($tampered))
        ->toThrow(HttpException::class);
});

it('rejects a payload whose data was tampered after signing', function (): void {
    $signer = new EditableRegionSigner;
    $payload = frontendAuthoringSignerPayload();

    $encoded = $signer->encode($payload);

    $decodedEnvelope = json_decode(
        (string) base64_decode(strtr($encoded, '-_', '+/'), true),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
    $decodedEnvelope['data']['field'] = 'content';
    $tampered = frontendAuthoringSignerEncodeEnvelope($decodedEnvelope);

    expect(fn (): EditableRegionPayloadData => $signer->decode($tampered))
        ->toThrow(HttpException::class);
});
