<?php

declare(strict_types=1);

use Capell\DocumentLifecycle\Actions\PublishDocumentAction;
use Capell\DocumentLifecycle\Actions\RecordDocumentAcceptanceAction;
use Capell\DocumentLifecycle\Actions\RegisterDocumentAction;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Http\Request;

require_once dirname(__DIR__) . '/DocumentLifecycleTestCase.php';

it('records acceptance against the latest published document metadata', function (): void {
    config()->set('legal.bundle_version', '2026-05-07.1');
    config()->set('legal.bundle_hash', str_repeat('a', 64));
    config()->set('legal.documents', ['terms' => 'fallback']);

    $document = RegisterDocumentAction::run('terms', 'Terms of Service');
    $older = PublishDocumentAction::run($document, 'Old terms', versionLabel: '2026-05-07', publishedAt: now()->subDay());
    $latest = PublishDocumentAction::run($document->refresh(), 'Current terms', versionLabel: '2026-05-08');
    $user = User::factory()->create();

    $acceptance = RecordDocumentAcceptanceAction::run(
        documentKey: 'terms',
        acceptor: $user,
        subject: $user,
        context: 'registration',
        metadata: ['flow' => 'fortify'],
        ipAddress: '198.51.100.24',
        userAgent: 'Capell Acceptance Test/1.0',
    );

    expect($acceptance->document_key)->toBe('terms')
        ->and($acceptance->document_version)->toBe('2026-05-08')
        ->and($acceptance->document_publication_id)->toBe($latest->getKey())
        ->and($acceptance->document_publication_id)->not->toBe($older->getKey())
        ->and($acceptance->document_hash)->toBe($latest->content_hash)
        ->and($acceptance->legal_bundle_version)->toBe('2026-05-07.1')
        ->and($acceptance->legal_bundle_hash)->toBe(str_repeat('a', 64))
        ->and($acceptance->legal_document_versions)->toBe(['terms' => 'fallback'])
        ->and($acceptance->acceptor->is($user))->toBeTrue()
        ->and($acceptance->subject->is($user))->toBeTrue()
        ->and($acceptance->context)->toBe('registration')
        ->and($acceptance->metadata)->toBe(['flow' => 'fortify'])
        ->and($acceptance->ip_hash)->toBe(hash('sha256', config('app.key') . ':198.51.100.24'))
        ->and($acceptance->user_agent_hash)->toBe(hash('sha256', config('app.key') . ':Capell Acceptance Test/1.0'));
});

it('hashes explicitly provided ip and user agent independently of the request', function (): void {
    $acceptance = RecordDocumentAcceptanceAction::run(
        documentKey: 'terms',
        context: 'console',
        ipAddress: '203.0.113.5',
        userAgent: 'queue-worker/1.0',
    );

    expect($acceptance->ip_hash)
        ->toBe(hash('sha256', config('app.key') . ':203.0.113.5'))
        ->and($acceptance->user_agent_hash)
        ->toBe(hash('sha256', config('app.key') . ':queue-worker/1.0'));
});

it('does not read request context inside the action', function (): void {
    app()->instance('request', Request::create(
        uri: '/admin/document-lifecycle',
        method: 'POST',
        server: [
            'REMOTE_ADDR' => '198.51.100.44',
            'HTTP_USER_AGENT' => 'admin-browser/2.0',
        ],
    ));

    $acceptance = RecordDocumentAcceptanceAction::run(
        documentKey: 'terms',
        context: 'console',
    );

    expect($acceptance->ip_hash)->toBeNull()
        ->and($acceptance->user_agent_hash)->toBeNull();
});

it('falls back to configured legal versions before a document has been migrated', function (): void {
    config()->set('legal.terms_version', '2026-05-07');
    config()->set('legal.documents', ['privacy' => '2026-05-08']);

    $acceptance = RecordDocumentAcceptanceAction::run('privacy', context: 'legacy-preview');

    expect($acceptance->document_key)->toBe('privacy')
        ->and($acceptance->document_version)->toBe('2026-05-08')
        ->and($acceptance->document_publication_id)->toBeNull()
        ->and($acceptance->document_hash)->toBeNull();
});
