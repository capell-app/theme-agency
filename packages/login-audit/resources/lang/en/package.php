<?php

declare(strict_types=1);

return [
    'description' => 'Login Audit records login, failed login, logout, and admin/user activity metadata for Capell users.',
    'health' => [
        'capture_configuration' => [
            'label' => 'Login audit capture configuration',
            'ready' => 'Vendor authentication-log capture is configured to write into the Login Audit table.',
            'not_ready' => 'Vendor authentication-log capture is not configured for the Login Audit table.',
            'remediation' => 'Ensure LoginAuditServiceProvider maps authentication-log.table_name and listener configuration from login-audit.php.',
        ],
    ],
];
