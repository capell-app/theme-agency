<?php

declare(strict_types=1);

return [
    'description' => 'Services, staff, locations, availability windows, and appointment requests for Capell.',
    'health' => [
        'storage_tables' => [
            'label' => 'Bookings database tables',
            'passed' => 'All required Bookings database tables are present.',
            'failed' => 'Missing Bookings database tables: :tables.',
            'remediation' => 'Run the Bookings package migrations, then rerun diagnostics.',
        ],
        'morph_map' => [
            'label' => 'Bookings model morph aliases',
            'passed' => 'Bookings models are registered in the morph map.',
            'failed' => 'Missing Bookings morph aliases: :aliases.',
            'remediation' => 'Ensure the Bookings service provider has booted and registered package models.',
        ],
        'actions' => [
            'label' => 'Bookings domain actions',
            'passed' => ':count Bookings actions are resolvable from the container.',
            'failed' => 'Unresolvable Bookings actions: :actions.',
            'remediation' => 'Rebuild Composer autoload and confirm the Bookings package is installed completely.',
        ],
        'public_routes' => [
            'label' => 'Bookings public routes',
            'passed' => 'Public booking, portal, review, calendar, and webhook routes are registered.',
            'failed' => 'Missing Bookings public routes: :routes.',
            'remediation' => 'Confirm the Bookings package is installed and route caching has been refreshed.',
        ],
        'public_renderer' => [
            'label' => 'Bookings public request renderer',
            'passed' => 'A public booking request renderer is bound.',
            'failed' => 'The public booking request renderer is not bound to a valid renderer.',
            'remediation' => 'Bind Capell\\Bookings\\Contracts\\PublicBookingRequestRenderer to a renderer implementation.',
        ],
        'scheduled_commands' => [
            'label' => 'Bookings scheduled maintenance',
            'passed' => 'Reminder, workflow expiry, review scheduling, and retention pruning commands are scheduled.',
            'failed' => 'Missing Bookings scheduled commands: :commands.',
            'remediation' => 'Confirm the Bookings service provider booted for an installed package and clear cached schedules.',
        ],
        'settings_migration' => [
            'label' => 'Bookings settings migration',
            'passed' => 'Bookings settings are registered and the package settings migration is available.',
            'failed' => 'Bookings settings registration or the package settings migration is missing.',
            'remediation' => 'Publish and run the Bookings settings migration, then clear configuration cache.',
        ],
        'console_commands' => [
            'label' => 'Bookings console commands',
            'passed' => 'Bookings reminder, workflow expiry, review scheduling, and retention pruning commands are registered.',
            'failed' => 'Missing Bookings console commands: :commands.',
            'remediation' => 'Rebuild package discovery and clear the application command cache.',
        ],
    ],
];
