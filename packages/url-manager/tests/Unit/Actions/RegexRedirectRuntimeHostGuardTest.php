<?php

declare(strict_types=1);

use Capell\UrlManager\Actions\ResolveRedirectRuleAction;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Capell\UrlManager\Models\RedirectRule;

/**
 * Persist a regex redirect rule directly, bypassing the save-time allowlist
 * validation. This mirrors the real attack: the save-time host check only sees
 * the literal template (host "$1" / "$1.evil.example"), which passes, while the
 * runtime substitution produces a concrete off-allowlist host.
 */
function makeRegexRedirectRule(string $sourcePattern, string $targetTemplate): RedirectRule
{
    $redirectRule = new RedirectRule;
    $redirectRule->source_url = $sourcePattern;
    $redirectRule->source_hash = hash('sha256', $sourcePattern);
    $redirectRule->target_url = $targetTemplate;
    $redirectRule->target_hash = hash('sha256', $targetTemplate);
    $redirectRule->status_code = 301;
    $redirectRule->match_type = RedirectMatchType::Regex;
    $redirectRule->status = RedirectRuleStatus::Active;
    $redirectRule->priority = 0;
    $redirectRule->preserve_query = false;
    $redirectRule->save();

    return $redirectRule;
}

it('does not honour a regex redirect whose substituted target resolves to an off-allowlist external host', function (): void {
    config([
        'app.url' => 'https://app.test',
        'capell-url-manager.redirects.absolute_target_allowed_hosts' => [],
        'capell-url-manager.redirects.allow_app_url_host' => true,
    ]);

    // Template host "$1.evil.example" passes the literal save-time check, but the
    // backreference substitutes the captured path segment into the host at runtime,
    // producing an absolute URL pointing off the allowlist.
    makeRegexRedirectRule('#^/go/(.+)$#', 'https://$1.evil.example/');

    $resolution = ResolveRedirectRuleAction::run('/go/attacker', recordHit: false);

    expect($resolution)->toBeNull();
});

it('still honours a same-host regex redirect with a relative target', function (): void {
    config([
        'app.url' => 'https://app.test',
        'capell-url-manager.redirects.absolute_target_allowed_hosts' => [],
        'capell-url-manager.redirects.allow_app_url_host' => true,
    ]);

    makeRegexRedirectRule('#^/products/(\d+)$#', '/catalogue/$1');

    $resolution = ResolveRedirectRuleAction::run('/products/42', recordHit: false);

    expect($resolution?->targetUrl)->toBe('/catalogue/42')
        ->and($resolution?->matchType)->toBe(RedirectMatchType::Regex);
});

it('still honours a regex redirect that resolves to an absolute URL on an allowed host', function (): void {
    config([
        'app.url' => 'https://app.test',
        'capell-url-manager.redirects.absolute_target_allowed_hosts' => ['cdn.allowed.example'],
        'capell-url-manager.redirects.allow_app_url_host' => true,
    ]);

    makeRegexRedirectRule('#^/asset/(.+)$#', 'https://cdn.allowed.example/$1');

    $resolution = ResolveRedirectRuleAction::run('/asset/logo.png', recordHit: false);

    expect($resolution?->targetUrl)->toBe('https://cdn.allowed.example/logo.png');
});
