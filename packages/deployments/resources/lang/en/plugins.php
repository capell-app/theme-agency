<?php

declare(strict_types=1);

return [
    'deployment_connection' => [
        'connect_bitbucket' => 'Connect Bitbucket Repository',
        'connect_github' => 'Connect GitHub Repository',
        'connect_gitlab' => 'Connect GitLab Repository',
        'connected_to' => 'Connected to :provider — :repo',
        'disconnect' => 'Disconnect',
        'disconnect_confirm' => 'Are you sure you want to disconnect this deployment repository?',
        'disconnected' => 'Deployment repository disconnected.',
        'install_policy_label' => 'Install policy',
        'nav_label' => 'Deployment Repository',
        'none_connected' => 'No deployment repository is connected. Choose a Git provider above to connect the repository used for plugin deployments.',
        'not_connected' => 'No deployment repository configured.',
        'oauth_connected' => ':provider connected successfully.',
        'oauth_failed' => ':provider OAuth failed. Check client credentials.',
        'oauth_invalid_state' => 'OAuth session validation failed. Please start the connection again.',
        'oauth_missing_code' => 'OAuth error: missing code parameter.',
        'provider_not_configured' => ':provider OAuth is not configured.',
        'package_column' => 'Package',
        'published_column' => 'Published',
        'publish_statuses' => [
            'dry_run' => 'Dry run',
            'failure' => 'Failed',
            'pending' => 'Pending',
            'success' => 'Passed',
        ],
        'pull_request_reference' => 'PR :id',
        'recent_publishes' => 'Recent publishes',
        'reference_column' => 'Reference',
        'repo_name_label' => 'Repository name',
        'repo_owner_label' => 'Repository owner or group',
        'repository_required' => 'Enter the repository owner and name before connecting.',
        'no_recent_publishes' => 'No Composer requirement publishes have been recorded for this repository yet.',
        'no_reference' => 'Not available',
        'oauth_user_failed' => 'Could not fetch :provider user info.',
        'status_column' => 'Status',
        'title' => 'Deployment Repository',
    ],
];
