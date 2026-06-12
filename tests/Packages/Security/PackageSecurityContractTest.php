<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../scripts/audit-package-security.php';

it('keeps every package manifest on the security contract', function (): void {
    expect(capell_security_manifest_contract_issues(base_path()))->toBe([]);
});

it('declares and protects public package routes', function (): void {
    expect(capell_security_route_contract_issues(base_path()))->toBe([]);
});

it('uses explicit timeouts for all external HTTP clients', function (): void {
    expect(capell_security_http_clients_without_timeouts(base_path()))->toBe([]);
});

it('keeps public package Blade free of authoring and secret surfaces', function (): void {
    expect(capell_security_public_blade_violations(base_path()))->toBe([]);
});

it('keeps GitHub workflow security gates intact', function (): void {
    expect(capell_security_workflow_issues(base_path()))->toBe([]);
});

it('declares authorization posture for admin package surfaces', function (): void {
    $invalid = [];

    foreach (capell_security_manifest_payloads(base_path()) as $slug => $manifest) {
        $surfaces = capell_security_string_list($manifest['surfaces'] ?? []);

        if (! in_array('admin', $surfaces, true)) {
            continue;
        }

        $security = $manifest['security'] ?? null;
        $adminSurface = is_array($security) ? ($security['adminSurface'] ?? null) : null;
        $authorization = is_array($adminSurface) ? ($adminSurface['authorization'] ?? null) : null;

        if (! in_array($authorization, ['permissions', 'policies', 'panel-auth'], true)) {
            $invalid[$slug] = is_scalar($authorization) ? (string) $authorization : get_debug_type($authorization);
        }
    }

    expect($invalid)->toBe([]);
});
