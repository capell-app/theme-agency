<?php

declare(strict_types=1);

use Capell\PublicActions\Actions\DispatchPublicActionDestinationAction;
use Capell\PublicActions\Contracts\PublicActionWebhookHostResolver;
use Capell\PublicActions\Data\ResolvedWebhookEndpointData;
use Capell\PublicActions\Enums\PublicActionDispatchStatus;
use Capell\PublicActions\Jobs\DispatchPublicActionDestinationJob;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionSubmission;
use Capell\PublicActions\Support\Providers\HttpWebhookPublicActionAdapter;
use Capell\PublicActions\Tests\Fakes\FakePublicActionWebhookHostResolver;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('dispatches a submission to a json webhook and records a successful attempt', function (): void {
    Http::fake([
        'https://hooks.example.test/success' => Http::response('{"id":"accepted"}', 202, ['X-Request-Id' => 'provider-123']),
    ]);

    $action = PublicAction::factory()->create([
        'key' => 'preview-access',
        'name' => 'Preview access',
    ]);
    $destination = PublicActionDestination::factory()->for($action, 'action')->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.example.test/success',
        'secret' => 'signing-secret',
        'headers' => ['X-Custom' => 'custom-value'],
        'settings' => ['method' => 'POST', 'timeout_seconds' => 5],
    ]);
    $submission = PublicActionSubmission::factory()->for($action, 'action')->create([
        'payload' => ['email' => 'person@example.test'],
    ]);

    $result = resolve(DispatchPublicActionDestinationAction::class)->handle($destination, $submission);

    expect($result->success)->toBeTrue()
        ->and($result->responseStatus)->toBe(202)
        ->and($result->externalId)->toBe('provider-123');

    $attempt = PublicActionDispatchAttempt::query()->firstOrFail();

    expect($attempt->status)->toBe(PublicActionDispatchStatus::Succeeded)
        ->and($attempt->attempt)->toBe(1)
        ->and($attempt->response_status)->toBe(202)
        ->and($attempt->request_hash)->toHaveLength(64);

    Http::assertSent(function (Request $request): bool {
        $data = json_decode($request->body(), true, flags: JSON_THROW_ON_ERROR);

        return $request->method() === 'POST'
            && $request->url() === 'https://hooks.example.test/success'
            && $request->hasHeader('X-Custom', 'custom-value')
            && $request->hasHeader('X-Capell-Signature')
            && $request->hasHeader('Host', 'hooks.example.test')
            && data_get($data, 'action.key') === 'preview-access'
            && data_get($data, 'payload.email') === 'person@example.test';
    });
});

it('does not follow redirects to unchecked internal webhook endpoints', function (string $redirectUrl): void {
    /** @var array<string, mixed>|null $requestOptions */
    $requestOptions = null;

    Http::fake(function (Request $request, array $options) use ($redirectUrl, &$requestOptions): PromiseInterface {
        $requestOptions = $options;

        return Http::response('', 302, ['Location' => $redirectUrl]);
    });

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.example.test/redirect',
    ]);
    $submission = PublicActionSubmission::factory()->create();

    $result = resolve(HttpWebhookPublicActionAdapter::class)->dispatch($destination, $submission);
    $attempt = PublicActionDispatchAttempt::query()->firstOrFail();

    expect($result->success)->toBeFalse()
        ->and($result->responseStatus)->toBe(302)
        ->and($attempt->status)->toBe(PublicActionDispatchStatus::Retryable)
        ->and($attempt->response_status)->toBe(302)
        ->and(data_get($requestOptions, 'allow_redirects'))->toBeFalse();

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://hooks.example.test/redirect');
})->with([
    'cloud metadata link-local address' => ['http://169.254.169.254/latest/meta-data'],
    'localhost loopback address' => ['http://127.0.0.1/admin'],
    'internal hostname' => ['https://internal.service.localhost/private'],
]);

it('records provider failures as retryable and redacts response summaries', function (): void {
    Http::fake([
        'https://hooks.example.test/fail' => Http::response('failed for secret-token at https://hooks.example.test/fail', 500),
    ]);

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.example.test/fail',
        'secret' => 'secret-token',
        'headers' => ['Authorization' => 'Bearer secret-token'],
    ]);
    $submission = PublicActionSubmission::factory()->create();

    $result = resolve(DispatchPublicActionDestinationAction::class)->handle($destination, $submission);
    $attempt = PublicActionDispatchAttempt::query()->firstOrFail();

    expect($result->success)->toBeFalse()
        ->and($attempt->status)->toBe(PublicActionDispatchStatus::Retryable)
        ->and($attempt->response_status)->toBe(500)
        ->and($attempt->response_summary)->toContain('[redacted]')
        ->and($attempt->response_summary)->not->toContain('secret-token')
        ->and($attempt->response_summary)->not->toContain('https://hooks.example.test/fail');
});

it('releases webhook dispatch jobs when provider failures are retryable', function (): void {
    config()->set('capell-public-actions.dispatch_retry_seconds', 45);

    Http::fake([
        'https://hooks.example.test/retry-job' => Http::response('try later', 503),
    ]);

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.example.test/retry-job',
    ]);
    $submission = PublicActionSubmission::factory()->create();

    $job = new DispatchPublicActionDestinationJob($destination, $submission);
    $job->withFakeQueueInteractions();
    $job->handle(resolve(DispatchPublicActionDestinationAction::class));

    $job->assertReleased(45);

    expect(PublicActionDispatchAttempt::query()->firstOrFail()->status)->toBe(PublicActionDispatchStatus::Retryable);
});

it('records connection failures as retryable without leaking secrets', function (): void {
    Http::fake(function (): never {
        throw new ConnectionException('Could not connect with secret-token');
    });

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.example.test/timeout',
        'secret' => 'secret-token',
    ]);
    $submission = PublicActionSubmission::factory()->create();

    $result = resolve(DispatchPublicActionDestinationAction::class)->handle($destination, $submission);
    $attempt = PublicActionDispatchAttempt::query()->firstOrFail();

    expect($result->success)->toBeFalse()
        ->and($attempt->status)->toBe(PublicActionDispatchStatus::Retryable)
        ->and($attempt->error_message)->toContain('[redacted]')
        ->and($attempt->error_message)->not->toContain('secret-token');
});

it('increments retry attempts while keeping the same request hash for unchanged payloads', function (): void {
    Http::fake([
        'https://hooks.example.test/retry' => Http::response('try again', 503),
    ]);

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.example.test/retry',
    ]);
    $submission = PublicActionSubmission::factory()->create([
        'payload' => ['email' => 'same@example.test'],
    ]);

    resolve(DispatchPublicActionDestinationAction::class)->handle($destination, $submission);
    resolve(DispatchPublicActionDestinationAction::class)->handle($destination, $submission);

    $attempts = PublicActionDispatchAttempt::query()->orderBy('attempt')->get();
    $firstAttempt = $attempts->get(0);
    $secondAttempt = $attempts->get(1);

    throw_if(! $firstAttempt instanceof PublicActionDispatchAttempt || ! $secondAttempt instanceof PublicActionDispatchAttempt, RuntimeException::class, 'Expected two public action dispatch attempts.');

    expect($attempts)->toHaveCount(2)
        ->and($firstAttempt->attempt)->toBe(1)
        ->and($secondAttempt->attempt)->toBe(2)
        ->and($firstAttempt->request_hash)->toBe($secondAttempt->request_hash);
});

it('can dispatch with the adapter directly for registered adapter use cases', function (): void {
    Http::fake([
        'https://hooks.example.test/direct' => Http::response('', 204),
    ]);

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.example.test/direct',
        'settings' => ['method' => 'PUT'],
    ]);
    $submission = PublicActionSubmission::factory()->create();

    $result = resolve(HttpWebhookPublicActionAdapter::class)->dispatch($destination, $submission);

    expect($result->success)->toBeTrue()
        ->and(PublicActionDispatchAttempt::query()->firstOrFail()->status)->toBe(PublicActionDispatchStatus::Succeeded);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT');
});

it('pins webhook dispatches to validated ipv6 addresses while keeping the original host', function (): void {
    /** @var array<string, mixed>|null $requestOptions */
    $requestOptions = null;

    $resolver = resolve(PublicActionWebhookHostResolver::class);
    throw_unless($resolver instanceof FakePublicActionWebhookHostResolver);
    $resolver->set('hooks.ipv6.example.test', ['2606:2800:220:1:248:1893:25c8:1946']);

    Http::fake(function (Request $request, array $options) use (&$requestOptions): PromiseInterface {
        $requestOptions = $options;

        return Http::response('', 204);
    });

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.ipv6.example.test/ipv6',
    ]);
    $submission = PublicActionSubmission::factory()->create();

    $result = resolve(HttpWebhookPublicActionAdapter::class)->dispatch($destination, $submission);

    expect($result->success)->toBeTrue()
        ->and($requestOptions)->toBeArray()
        ->and($requestOptions['curl'][CURLOPT_RESOLVE] ?? null)->toBe([
            'hooks.ipv6.example.test:443:[2606:2800:220:1:248:1893:25c8:1946]',
        ]);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://hooks.ipv6.example.test/ipv6'
        && $request->hasHeader('Host', 'hooks.ipv6.example.test'));
});

it('blocks private webhook endpoint hosts', function (): void {
    Http::fake();

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://127.0.0.1/metadata',
    ]);
    $submission = PublicActionSubmission::factory()->create();

    $result = resolve(HttpWebhookPublicActionAdapter::class)->dispatch($destination, $submission);

    expect($result->success)->toBeFalse()
        ->and(PublicActionDispatchAttempt::query()->firstOrFail()->status)->toBe(PublicActionDispatchStatus::Failed);

    Http::assertNothingSent();
});

it('blocks webhook endpoint hosts that resolve to private addresses', function (): void {
    Http::fake();

    $resolver = resolve(PublicActionWebhookHostResolver::class);
    throw_unless($resolver instanceof FakePublicActionWebhookHostResolver);
    $resolver->set('metadata.example.test', ['169.254.169.254']);

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://metadata.example.test/latest',
    ]);
    $submission = PublicActionSubmission::factory()->create();

    $result = resolve(HttpWebhookPublicActionAdapter::class)->dispatch($destination, $submission);

    expect($result->success)->toBeFalse()
        ->and(PublicActionDispatchAttempt::query()->firstOrFail()->status)->toBe(PublicActionDispatchStatus::Failed);

    Http::assertNothingSent();
});

it('fails closed when a webhook endpoint host cannot be resolved', function (): void {
    Http::fake();

    $resolver = resolve(PublicActionWebhookHostResolver::class);
    throw_unless($resolver instanceof FakePublicActionWebhookHostResolver);
    $resolver->set('missing.example.test', []);

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://missing.example.test/webhook',
    ]);
    $submission = PublicActionSubmission::factory()->create();

    $result = resolve(HttpWebhookPublicActionAdapter::class)->dispatch($destination, $submission);

    expect($result->success)->toBeFalse()
        ->and(PublicActionDispatchAttempt::query()->firstOrFail()->status)->toBe(PublicActionDispatchStatus::Failed);

    Http::assertNothingSent();
});

it('pins webhook dispatch to the validated address while keeping the original host', function (): void {
    $resolver = resolve(PublicActionWebhookHostResolver::class);
    throw_unless($resolver instanceof FakePublicActionWebhookHostResolver);
    $resolver->set('hooks.example.test', ['93.184.216.34', '93.184.216.35']);

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.example.test:8443/pinned',
    ]);
    $adapter = resolve(HttpWebhookPublicActionAdapter::class);

    $endpointMethod = new ReflectionMethod($adapter, 'endpoint');
    $endpoint = $endpointMethod->invoke($adapter, $destination);
    throw_unless($endpoint instanceof ResolvedWebhookEndpointData, RuntimeException::class, 'Expected resolved webhook endpoint.');

    $optionsMethod = new ReflectionMethod($adapter, 'requestOptions');
    $options = $optionsMethod->invoke($adapter, $endpoint);
    throw_unless(is_array($options), RuntimeException::class, 'Expected webhook request options.');
    $curlOptions = $options['curl'] ?? null;
    throw_unless(is_array($curlOptions), RuntimeException::class, 'Expected curl request options.');

    expect($endpoint->host)->toBe('hooks.example.test')
        ->and($endpoint->port)->toBe(8443)
        ->and($endpoint->address)->toBe('93.184.216.34')
        ->and($endpoint->hostHeader())->toBe('hooks.example.test:8443')
        ->and($options)->toHaveKey('curl')
        ->and($curlOptions[CURLOPT_RESOLVE] ?? null)->toBe(['hooks.example.test:8443:93.184.216.34']);
});

it('formats ipv6 addresses for curl host pinning', function (): void {
    $resolver = resolve(PublicActionWebhookHostResolver::class);
    throw_unless($resolver instanceof FakePublicActionWebhookHostResolver);
    $resolver->set('hooks.ipv6.example.test', ['2606:2800:220:1:248:1893:25c8:1946']);

    $destination = PublicActionDestination::factory()->create([
        'adapter' => 'http_webhook',
        'endpoint_url' => 'https://hooks.ipv6.example.test:8443/pinned',
    ]);
    $adapter = resolve(HttpWebhookPublicActionAdapter::class);

    $endpointMethod = new ReflectionMethod($adapter, 'endpoint');
    $endpoint = $endpointMethod->invoke($adapter, $destination);
    throw_unless($endpoint instanceof ResolvedWebhookEndpointData, RuntimeException::class, 'Expected resolved webhook endpoint.');

    $optionsMethod = new ReflectionMethod($adapter, 'requestOptions');
    $options = $optionsMethod->invoke($adapter, $endpoint);
    throw_unless(is_array($options), RuntimeException::class, 'Expected webhook request options.');
    $curlOptions = $options['curl'] ?? null;
    throw_unless(is_array($curlOptions), RuntimeException::class, 'Expected curl request options.');

    expect($endpoint->address)->toBe('2606:2800:220:1:248:1893:25c8:1946')
        ->and($curlOptions[CURLOPT_RESOLVE] ?? null)->toBe([
            'hooks.ipv6.example.test:8443:[2606:2800:220:1:248:1893:25c8:1946]',
        ]);
});
