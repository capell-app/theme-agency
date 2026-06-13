<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../scripts/generate-package-security-surface-report.php';

it('keeps the generated package security surface report current', function (): void {
    $root = dirname(__DIR__, 3);
    $report = capell_security_surface_report_markdown($root);
    $path = $root . '/' . CAPELL_SECURITY_SURFACE_REPORT_PATH;
    $current = file_get_contents($path);

    throw_unless(is_string($current), RuntimeException::class, 'Expected package security surface report to be readable.');

    expect($current)->toBe($report);
    expect($report)->toContain('| Package | Risk | Public routes | Webhooks | Throttled | Signed/tokenized | Sensitive fields | Admin authorization | Cache posture |');
    expect($report)->toContain('capell-app/payments');
    expect($report)->toContain('capell-payments.stripe-webhook');
    expect($report)->toContain('permissions;');
    expect($report)->toContain('safe;');
});
