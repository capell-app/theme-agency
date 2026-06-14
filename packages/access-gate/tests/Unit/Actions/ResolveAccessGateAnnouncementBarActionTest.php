<?php

declare(strict_types=1);

use Capell\AccessGate\Actions\ResolveAccessGateAnnouncementBarAction;
use Capell\AccessGate\Models\Area;
use Illuminate\Http\Request;

it('resolves an enabled announcement for matching public paths', function (): void {
    Area::factory()->create([
        'key' => 'capell-preview',
        'announcement_enabled' => true,
        'announcement_message' => 'Launch offer: 50% off all extensions.',
        'announcement_short_message' => '50% off extensions',
        'announcement_link_label' => 'Claim launch offer',
        'announcement_link_short_label' => 'Claim',
        'announcement_link_url' => '/marketplace/browse',
        'announcement_path_patterns' => [
            'extensions',
            'extensions/*',
            'marketplace/extensions/*',
        ],
    ]);

    $announcement = ResolveAccessGateAnnouncementBarAction::run(Request::create('/extensions/themes'));

    expect($announcement)->not->toBeNull()
        ->and($announcement->message)->toBe('Launch offer: 50% off all extensions.')
        ->and($announcement->shortMessage)->toBe('50% off extensions')
        ->and($announcement->linkLabel)->toBe('Claim launch offer')
        ->and($announcement->linkShortLabel)->toBe('Claim')
        ->and($announcement->linkUrl)->toBe('/marketplace/browse');
});

it('allows http and https announcement links', function (string $url): void {
    Area::factory()->create([
        'key' => 'capell-preview',
        'announcement_enabled' => true,
        'announcement_message' => 'Launch offer: 50% off all extensions.',
        'announcement_link_label' => 'Claim launch offer',
        'announcement_link_url' => $url,
        'announcement_path_patterns' => [
            'extensions/*',
        ],
    ]);

    $announcement = ResolveAccessGateAnnouncementBarAction::run(Request::create('/extensions/themes'));

    expect($announcement)->not->toBeNull()
        ->and($announcement->linkLabel)->toBe('Claim launch offer')
        ->and($announcement->linkUrl)->toBe($url);
})->with([
    'http' => 'http://example.test/offer',
    'https' => 'https://example.test/offer',
]);

it('does not render unsafe announcement links', function (string $url): void {
    Area::factory()->create([
        'key' => 'capell-preview',
        'announcement_enabled' => true,
        'announcement_message' => 'Launch offer: 50% off all extensions.',
        'announcement_link_label' => 'Claim launch offer',
        'announcement_link_url' => $url,
        'announcement_path_patterns' => [
            'extensions/*',
        ],
    ]);

    $announcement = ResolveAccessGateAnnouncementBarAction::run(Request::create('/extensions/themes'));

    expect($announcement)->not->toBeNull()
        ->and($announcement->linkLabel)->toBeNull()
        ->and($announcement->linkUrl)->toBeNull();
})->with([
    'protocol-relative' => '//example.test/offer',
    'javascript' => 'javascript:alert(1)',
    'ftp' => 'ftp://example.test/offer',
    'bare path' => 'marketplace/browse',
]);

it('does not render orphan announcement link labels or urls', function (): void {
    Area::factory()->create([
        'key' => 'capell-preview',
        'announcement_enabled' => true,
        'announcement_message' => 'Launch offer: 50% off all extensions.',
        'announcement_link_label' => '',
        'announcement_link_url' => '/marketplace/browse',
        'announcement_path_patterns' => [
            'extensions/*',
        ],
    ]);

    $announcement = ResolveAccessGateAnnouncementBarAction::run(Request::create('/extensions/themes'));

    expect($announcement)->not->toBeNull()
        ->and($announcement->linkLabel)->toBeNull()
        ->and($announcement->linkUrl)->toBeNull();
});

it('does not resolve announcements for non matching paths', function (): void {
    Area::factory()->create([
        'key' => 'capell-preview',
        'announcement_enabled' => true,
        'announcement_message' => 'Launch offer: 50% off all extensions.',
        'announcement_short_message' => '50% off extensions',
        'announcement_path_patterns' => [
            'extensions',
            'extensions/*',
        ],
    ]);

    expect(ResolveAccessGateAnnouncementBarAction::run(Request::create('/get-started')))->toBeNull();
});

it('does not resolve disabled announcements', function (): void {
    Area::factory()->create([
        'key' => 'capell-preview',
        'announcement_enabled' => false,
        'announcement_message' => 'Launch offer: 50% off all extensions.',
        'announcement_short_message' => '50% off extensions',
        'announcement_path_patterns' => [
            'extensions/*',
        ],
    ]);

    expect(ResolveAccessGateAnnouncementBarAction::run(Request::create('/extensions/themes')))->toBeNull();
});
