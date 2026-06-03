<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\MediaAI\Contracts\ImageDoctor;
use Capell\MediaAI\Health\MediaAIHealthCheck;
use Capell\MediaAI\Support\NullImageDoctor;

it('reports a compatible capell api version', function (): void {
    expect(MediaAIHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = MediaAIHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(3)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the adapter resolves, the action is registered, and a request can be built', function (): void {
    expect(MediaAIHealthCheck::passed())->toBeTrue();
});

it('passes the provider adapter check under the default null image doctor', function (): void {
    expect(resolve(ImageDoctor::class))->toBeInstanceOf(NullImageDoctor::class);

    $check = new MediaAIHealthCheck;

    expect($check->providerAdapterCheck()->passed)->toBeTrue();
});

it('does not surface provider credentials in any diagnostic message', function (): void {
    config()->set('capell-media-ai.secret', 'super-secret-provider-key');

    $messages = MediaAIHealthCheck::runDiagnostics()
        ->map(static fn (DoctorCheckResultData $result): string => $result->message)
        ->implode(' ');

    expect($messages)->not->toContain('super-secret-provider-key');
});

it('fails the edit action check when the package is disabled', function (): void {
    config()->set('capell-media-ai.enabled', false);

    $check = new MediaAIHealthCheck;

    expect($check->editActionCheck()->passed)->toBeFalse();
});
