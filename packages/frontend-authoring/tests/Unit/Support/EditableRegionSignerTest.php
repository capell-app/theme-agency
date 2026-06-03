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

    $encoded = rtrim(strtr(base64_encode(json_encode(['unexpected' => true], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

    expect(fn (): EditableRegionPayloadData => $signer->decode($encoded))
        ->toThrow(HttpException::class);
});

it('rejects a payload missing required data keys', function (): void {
    $signer = new EditableRegionSigner;

    $incompleteData = ['model' => 'App\\Models\\Translation', 'recordKey' => 1];
    $envelope = [
        'data' => $incompleteData,
        'signature' => hash_hmac('sha256', json_encode($incompleteData, JSON_THROW_ON_ERROR), (string) config('app.key')),
    ];
    $encoded = rtrim(strtr(base64_encode(json_encode($envelope, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

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
    $tampered = rtrim(strtr(base64_encode(json_encode($decodedEnvelope, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

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
    $tampered = rtrim(strtr(base64_encode(json_encode($decodedEnvelope, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

    expect(fn (): EditableRegionPayloadData => $signer->decode($tampered))
        ->toThrow(HttpException::class);
});
