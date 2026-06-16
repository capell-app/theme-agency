<?php

declare(strict_types=1);

use Capell\PublicActions\Actions\ReplayPublicActionDispatchAttemptAction;
use Capell\PublicActions\Enums\PublicActionDispatchStatus;
use Capell\PublicActions\Enums\PublicActionStatus;
use Capell\PublicActions\Enums\PublicActionSubmissionStatus;
use Capell\PublicActions\Jobs\DispatchPublicActionDestinationJob;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionSubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('submits an active public action and redirects with no-store headers', function (): void {
    PublicAction::factory()->create([
        'key' => 'preview-access',
        'handler_key' => 'test.handler',
        'success_redirect_url' => 'https://example.test/thanks',
    ]);

    $response = $this
        ->from('/source-page')
        ->withHeader('User-Agent', 'PublicActionsTest/1.0')
        ->post('/actions/preview-access', [
            'email' => 'person@example.test',
            'source_type' => 'button',
            'source_id' => 'hero',
        ]);

    $response
        ->assertRedirect('https://example.test/thanks')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Expires', '0');

    $submission = PublicActionSubmission::query()->firstOrFail();

    expect($submission->status)->toBe(PublicActionSubmissionStatus::Handled)
        ->and($submission->payload)->toMatchArray(['email' => 'person@example.test'])
        ->and($submission->source_type)->toBe('button')
        ->and($submission->source_id)->toBe('hero')
        ->and($submission->metadata['ip_hash'] ?? null)->toBe(hash('sha256', '127.0.0.1'))
        ->and($submission->metadata['user_agent'] ?? null)->toBe('PublicActionsTest/1.0')
        ->and($submission->metadata)->not->toHaveKey('ip');
});

it('returns json for json submissions', function (): void {
    PublicAction::factory()->create([
        'key' => 'download-access',
        'handler_key' => 'test.handler',
    ]);

    $response = $this->postJson('/actions/download-access', [
        'email' => 'person@example.test',
    ]);

    $response
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJson([
            'success' => true,
            'message' => 'download-access',
        ]);
});

it('honours per-action submit rate limit overrides', function (): void {
    PublicAction::factory()->create([
        'key' => 'limited-action',
        'handler_key' => 'test.handler',
        'settings' => [
            'rate_limit' => [
                'per_minute' => 1,
            ],
        ],
    ]);

    $this->postJson('/actions/limited-action', [
        'email' => 'person@example.test',
    ])->assertOk();

    $this->postJson('/actions/limited-action', [
        'email' => 'person@example.test',
    ])->assertTooManyRequests();
});

it('replays duplicate submissions with the same idempotency key without creating another submission', function (): void {
    PublicAction::factory()->create([
        'key' => 'idempotent-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'type' => 'email', 'required' => true],
            ],
        ],
    ]);

    $this
        ->withHeader('Idempotency-Key', 'request-123')
        ->postJson('/actions/idempotent-action', [
            'email' => 'first@example.test',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this
        ->withHeader('Idempotency-Key', 'request-123')
        ->postJson('/actions/idempotent-action', [
            'email' => 'second@example.test',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $submission = PublicActionSubmission::query()->firstOrFail();

    expect(PublicActionSubmission::query()->count())->toBe(1)
        ->and($submission->idempotency_key)->toBe('request-123')
        ->and($submission->payload)->toBe(['email' => 'first@example.test']);
});

it('reuses an idempotent submission that was already inserted by another request', function (): void {
    $action = PublicAction::factory()->create([
        'key' => 'already-inserted-idempotent-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'type' => 'email', 'required' => true],
            ],
        ],
    ]);

    PublicActionSubmission::factory()
        ->for($action, 'action')
        ->create([
            'idempotency_key' => 'request-456',
            'payload' => ['email' => 'first@example.test'],
            'metadata' => [
                'result_success' => true,
                'result_message' => 'already handled',
            ],
            'status' => PublicActionSubmissionStatus::Handled,
        ]);

    $this
        ->withHeader('Idempotency-Key', 'request-456')
        ->postJson('/actions/already-inserted-idempotent-action', [
            'email' => 'second@example.test',
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'already handled',
        ]);

    $submission = PublicActionSubmission::query()->firstOrFail();

    expect(PublicActionSubmission::query()->count())->toBe(1)
        ->and($submission->payload)->toBe(['email' => 'first@example.test']);
});

it('rejects filled honeypot fields before creating a submission', function (): void {
    PublicAction::factory()->create([
        'key' => 'honeypot-action',
        'handler_key' => 'test.handler',
    ]);

    $this->postJson('/actions/honeypot-action', [
        'email' => 'person@example.test',
        '_hp' => 'filled by bot',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['_hp']);

    expect(PublicActionSubmission::query()->count())->toBe(0);
});

it('supports turnstile spam protection adapters when configured', function (): void {
    config()->set('capell-public-actions.spam_protection.enabled', ['turnstile']);
    config()->set('capell-public-actions.spam_protection.turnstile.secret', 'secret-key');

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
    ]);

    PublicAction::factory()->create([
        'key' => 'turnstile-action',
        'handler_key' => 'test.handler',
    ]);

    $this->postJson('/actions/turnstile-action', [
        'email' => 'person@example.test',
        'cf-turnstile-response' => 'token',
    ])->assertOk();

    expect(PublicActionSubmission::query()->firstOrFail()->payload)
        ->not->toHaveKey('cf-turnstile-response');
});

it('supports hcaptcha spam protection adapters when configured', function (): void {
    config()->set('capell-public-actions.spam_protection.enabled', ['hcaptcha']);
    config()->set('capell-public-actions.spam_protection.hcaptcha.secret', 'secret-key');

    Http::fake([
        'https://hcaptcha.com/siteverify' => Http::response(['success' => true]),
    ]);

    PublicAction::factory()->create([
        'key' => 'hcaptcha-action',
        'handler_key' => 'test.handler',
    ]);

    $this->postJson('/actions/hcaptcha-action', [
        'email' => 'person@example.test',
        'h-captcha-response' => 'token',
    ])->assertOk();

    expect(PublicActionSubmission::query()->firstOrFail()->payload)
        ->not->toHaveKey('h-captcha-response');
});

it('supports recaptcha spam protection adapters when configured', function (): void {
    config()->set('capell-public-actions.spam_protection.enabled', ['recaptcha']);
    config()->set('capell-public-actions.spam_protection.recaptcha.secret', 'secret-key');
    config()->set('capell-public-actions.spam_protection.recaptcha.minimum_score', 0.7);
    config()->set('capell-public-actions.spam_protection.recaptcha.action', 'public_action');

    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.9,
            'action' => 'public_action',
        ]),
    ]);

    PublicAction::factory()->create([
        'key' => 'recaptcha-action',
        'handler_key' => 'test.handler',
    ]);

    $this->postJson('/actions/recaptcha-action', [
        'email' => 'person@example.test',
        'g-recaptcha-response' => 'token',
    ])->assertOk();

    expect(PublicActionSubmission::query()->firstOrFail()->payload)
        ->not->toHaveKey('g-recaptcha-response');
});

it('rejects recaptcha responses below the configured score', function (): void {
    config()->set('capell-public-actions.spam_protection.enabled', ['recaptcha']);
    config()->set('capell-public-actions.spam_protection.recaptcha.secret', 'secret-key');
    config()->set('capell-public-actions.spam_protection.recaptcha.minimum_score', 0.7);

    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.2,
        ]),
    ]);

    PublicAction::factory()->create([
        'key' => 'recaptcha-reject-action',
        'handler_key' => 'test.handler',
    ]);

    $this->postJson('/actions/recaptcha-reject-action', [
        'email' => 'person@example.test',
        'g-recaptcha-response' => 'token',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['g-recaptcha-response']);

    expect(PublicActionSubmission::query()->count())->toBe(0);
});

it('ignores public payload redirects to other hosts', function (): void {
    PublicAction::factory()->create([
        'key' => 'redirect-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'type' => 'email', 'required' => true],
                ['key' => 'redirect', 'type' => 'url', 'required' => false],
            ],
        ],
    ]);

    $this
        ->from('/source-page')
        ->post('/actions/redirect-action', [
            'email' => 'person@example.test',
            'redirect' => 'https://evil.example/phish',
        ])
        ->assertRedirect('/source-page');
});

it('allows public payload redirects on the current host', function (): void {
    PublicAction::factory()->create([
        'key' => 'same-host-redirect-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'type' => 'email', 'required' => true],
                ['key' => 'redirect', 'type' => 'url', 'required' => false],
            ],
        ],
    ]);

    $this
        ->from('/source-page')
        ->post('/actions/same-host-redirect-action', [
            'email' => 'person@example.test',
            'redirect' => 'http://localhost/thanks',
        ])
        ->assertRedirect('http://localhost/thanks');
});

it('rejects non-http public payload redirects even when the host matches', function (): void {
    PublicAction::factory()->create([
        'key' => 'unsafe-scheme-redirect-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'type' => 'email', 'required' => true],
                ['key' => 'redirect', 'type' => 'text'],
            ],
        ],
    ]);

    $this
        ->from('/source-page')
        ->post('/actions/unsafe-scheme-redirect-action', [
            'email' => 'person@example.test',
            'redirect' => 'javascript://localhost/thanks',
        ])
        ->assertRedirect('/source-page');
});

it('allows safe relative payload redirects', function (): void {
    PublicAction::factory()->create([
        'key' => 'relative-redirect-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'type' => 'email', 'required' => true],
                ['key' => 'redirect', 'type' => 'text'],
            ],
        ],
    ]);

    $this
        ->from('/source-page')
        ->post('/actions/relative-redirect-action', [
            'email' => 'person@example.test',
            'redirect' => '/thanks',
        ])
        ->assertRedirect('/thanks');
});

it('validates checkbox and textarea schema fields while ignoring invalid schema keys', function (): void {
    PublicAction::factory()->create([
        'key' => 'typed-schema-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'type' => 'email', 'required' => true],
                ['key' => 'consent', 'type' => 'checkbox', 'required' => true],
                ['key' => 'message', 'type' => 'textarea'],
                ['key' => ' ', 'type' => 'text'],
                ['label' => 'Missing key', 'type' => 'text'],
            ],
        ],
    ]);

    $this->postJson('/actions/typed-schema-action', [
        'email' => 'person@example.test',
        'consent' => '1',
        'message' => 'Tell me more.',
        'ignored' => 'not stored',
    ])->assertOk();

    expect(PublicActionSubmission::query()->firstOrFail()->payload)->toBe([
        'email' => 'person@example.test',
        'consent' => '1',
        'message' => 'Tell me more.',
    ]);
});

it('dispatches active destinations after a successful submission', function (): void {
    Http::fake([
        'https://hooks.example.test/access' => Http::response('', 204),
    ]);

    $action = PublicAction::factory()->create([
        'key' => 'destination-action',
        'handler_key' => 'test.handler',
    ]);

    PublicActionDestination::factory()->for($action, 'action')->create([
        'endpoint_url' => 'https://hooks.example.test/access',
        'settings' => ['sync' => true],
    ]);

    $this->post('/actions/destination-action', [
        'email' => 'person@example.test',
    ])->assertRedirect();

    expect(PublicActionDispatchAttempt::query()->firstOrFail()->status)->toBe(PublicActionDispatchStatus::Succeeded);
});

it('queues asynchronous destination dispatches after successful submissions', function (): void {
    Queue::fake();

    $action = PublicAction::factory()->create([
        'key' => 'queued-destination-action',
        'handler_key' => 'test.handler',
    ]);

    PublicActionDestination::factory()->for($action, 'action')->create([
        'settings' => ['sync' => false],
    ]);

    $this->post('/actions/queued-destination-action', [
        'email' => 'person@example.test',
    ])->assertRedirect();

    Queue::assertPushed(DispatchPublicActionDestinationJob::class);

    $attempt = PublicActionDispatchAttempt::query()->firstOrFail();

    expect($attempt->status)->toBe(PublicActionDispatchStatus::Pending)
        ->and($attempt->dispatched_at)->toBeNull()
        ->and($attempt->submission)->toBeInstanceOf(PublicActionSubmission::class)
        ->and($attempt->destination)->toBeInstanceOf(PublicActionDestination::class);
});

it('prunes submissions older than the configured retention window', function (): void {
    $action = PublicAction::factory()->create();
    $destination = PublicActionDestination::factory()->for($action, 'action')->create();
    $oldSubmission = PublicActionSubmission::factory()->for($action, 'action')->create([
        'submitted_at' => now()->subDays(45),
    ]);
    $recentSubmission = PublicActionSubmission::factory()->for($action, 'action')->create([
        'submitted_at' => now()->subDays(5),
    ]);

    PublicActionDispatchAttempt::factory()
        ->for($oldSubmission, 'submission')
        ->for($destination, 'destination')
        ->create();
    PublicActionDispatchAttempt::factory()
        ->for($recentSubmission, 'submission')
        ->for($destination, 'destination')
        ->create();

    $this
        ->artisan('capell:public-actions:prune-submissions', ['--days' => 30, '--dry-run' => true])
        ->assertSuccessful();

    expect(PublicActionSubmission::query()->count())->toBe(2)
        ->and(PublicActionDispatchAttempt::query()->count())->toBe(2);

    $this
        ->artisan('capell:public-actions:prune-submissions', ['--days' => 30])
        ->assertSuccessful();

    expect(PublicActionSubmission::query()->pluck('id')->all())->toBe([(int) $recentSubmission->getKey()])
        ->and(PublicActionDispatchAttempt::query()->count())->toBe(1);
});

it('replays failed dispatch attempts manually', function (): void {
    Http::fake([
        'https://hooks.example.test/replay' => Http::response('', 204),
    ]);

    $action = PublicAction::factory()->create();
    $destination = PublicActionDestination::factory()->for($action, 'action')->create([
        'endpoint_url' => 'https://hooks.example.test/replay',
    ]);
    $submission = PublicActionSubmission::factory()->for($action, 'action')->create();
    $failedAttempt = PublicActionDispatchAttempt::factory()
        ->for($submission, 'submission')
        ->for($destination, 'destination')
        ->create([
            'status' => PublicActionDispatchStatus::Failed,
        ]);

    $result = ReplayPublicActionDispatchAttemptAction::run($failedAttempt);

    expect($result->success)->toBeTrue()
        ->and(PublicActionDispatchAttempt::query()->count())->toBe(2)
        ->and(PublicActionDispatchAttempt::query()->latest('id')->first()?->status)->toBe(PublicActionDispatchStatus::Succeeded);
});

it('does not replay successful dispatch attempts', function (): void {
    $attempt = PublicActionDispatchAttempt::factory()->create([
        'status' => PublicActionDispatchStatus::Succeeded,
    ]);

    expect(fn (): mixed => ReplayPublicActionDispatchAttemptAction::run($attempt))
        ->toThrow(RuntimeException::class);
});

it('marks submissions failed when no handler is registered for the action', function (): void {
    PublicAction::factory()->create([
        'key' => 'missing-handler-action',
        'handler_key' => 'missing.handler',
    ]);

    $this->postJson('/actions/missing-handler-action', [
        'email' => 'person@example.test',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['action']);

    $submission = PublicActionSubmission::query()->firstOrFail();

    expect($submission->status)->toBe(PublicActionSubmissionStatus::Failed)
        ->and($submission->metadata['handler_failed'] ?? null)->toBeTrue()
        ->and($submission->metadata['handler_error_type'] ?? null)->toBe('InvalidArgumentException');
});

it('rejects inactive actions without creating a submission', function (): void {
    PublicAction::factory()->create([
        'key' => 'paused-action',
        'status' => PublicActionStatus::Paused,
        'handler_key' => 'test.handler',
    ]);

    $response = $this->postJson('/actions/paused-action', [
        'email' => 'person@example.test',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['action']);

    expect(PublicActionSubmission::query()->count())->toBe(0);
});

it('returns not found for missing actions', function (): void {
    $this->post('/actions/missing-action', [
        'email' => 'person@example.test',
    ])->assertNotFound();
});

it('marks the submission failed when the handler validation fails', function (): void {
    PublicAction::factory()->create([
        'key' => 'failing-action',
        'handler_key' => 'test.validation-handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'label' => 'Email address', 'type' => 'text', 'required' => false],
            ],
        ],
    ]);

    $response = $this->postJson('/actions/failing-action', [
        'email' => '',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    expect(PublicActionSubmission::query()->firstOrFail()->status)->toBe(PublicActionSubmissionStatus::Failed);
});

it('validates submitted payload against the configured action schema before creating a submission', function (): void {
    PublicAction::factory()->create([
        'key' => 'schema-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true],
            ],
        ],
    ]);

    $response = $this->postJson('/actions/schema-action', [
        'email' => 'not-an-email',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    expect(PublicActionSubmission::query()->count())->toBe(0);
});

it('stores only declared schema fields plus source metadata for schema-backed actions', function (): void {
    PublicAction::factory()->create([
        'key' => 'allowlisted-schema-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true],
            ],
        ],
    ]);

    $this->post('/actions/allowlisted-schema-action', [
        'email' => 'person@example.test',
        'unexpected' => 'stored by mistake',
        'source_type' => 'button',
        'source_id' => 'footer',
    ])->assertRedirect();

    $submission = PublicActionSubmission::query()->firstOrFail();

    expect($submission->payload)->toBe(['email' => 'person@example.test'])
        ->and($submission->source_type)->toBe('button')
        ->and($submission->source_id)->toBe('footer');
});

it('rejects oversized submissions when an action has no payload schema', function (): void {
    PublicAction::factory()->create([
        'key' => 'unschemed-action',
        'handler_key' => 'test.handler',
        'payload_schema' => [],
    ]);

    $response = $this->postJson('/actions/unschemed-action', [
        'body' => str_repeat('a', 17 * 1024),
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['payload']);

    expect(PublicActionSubmission::query()->count())->toBe(0);
});

it('renders an enabled public action page without exposing package internals', function (): void {
    PublicAction::factory()->create([
        'key' => 'page-action',
        'name' => 'Request preview',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true],
            ],
        ],
        'settings' => ['public_page_enabled' => true],
    ]);

    $response = $this->get('/actions/page-action');

    $response
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertSee('Request preview')
        ->assertSee('Email address')
        ->assertDontSee('test.handler')
        ->assertDontSee('capell-public-actions')
        ->assertDontSee('handler_key');
});

it('does not render disabled public action pages', function (): void {
    PublicAction::factory()->create([
        'key' => 'hidden-page',
        'handler_key' => 'test.handler',
        'settings' => ['public_page_enabled' => false],
    ]);

    $this->get('/actions/hidden-page')->assertNotFound();
});

it('throttles repeated submissions for the same action and email', function (): void {
    PublicAction::factory()->create([
        'key' => 'limited-action',
        'handler_key' => 'test.handler',
    ]);

    foreach (range(1, 12) as $attempt) {
        $this->post('/actions/limited-action', [
            'email' => 'person@example.test',
            'attempt' => $attempt,
        ])->assertRedirect();
    }

    $this->post('/actions/limited-action', [
        'email' => 'person@example.test',
    ])->assertTooManyRequests();
});
