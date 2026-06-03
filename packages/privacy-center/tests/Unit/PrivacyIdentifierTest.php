<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Support\PrivacyIdentifier;
use Capell\PrivacyCenter\Tests\PrivacyCenterTestCase;
use Illuminate\Support\Facades\Config;

require_once dirname(__DIR__) . '/autoload.php';

uses(PrivacyCenterTestCase::class);

it('returns null for empty values', function (): void {
    expect(PrivacyIdentifier::hashNullable(null))->toBeNull()
        ->and(PrivacyIdentifier::hashNullable(''))->toBeNull()
        ->and(PrivacyIdentifier::hashNullable('   '))->toBeNull();
});

it('hashes deterministically and case-insensitively for the same secret', function (): void {
    $first = PrivacyIdentifier::hashNullable('User@Example.com');
    $second = PrivacyIdentifier::hashNullable('user@example.com');

    expect($first)->toBe($second)
        ->and($first)->not->toBe('User@Example.com');
});

it('produces different hashes when the configured secret changes', function (): void {
    Config::set('capell-privacy-center.hash_secret', 'secret-one');
    $withFirstSecret = PrivacyIdentifier::hashNullable('user@example.com');

    Config::set('capell-privacy-center.hash_secret', 'secret-two');
    $withSecondSecret = PrivacyIdentifier::hashNullable('user@example.com');

    expect($withFirstSecret)->not->toBe($withSecondSecret);
});

it('fails loudly instead of using a guessable salt when no secret is configured', function (): void {
    Config::set('capell-privacy-center.hash_secret');
    Config::set('app.key');

    PrivacyIdentifier::hashNullable('user@example.com');
})->throws(RuntimeException::class, 'guessable salt');
