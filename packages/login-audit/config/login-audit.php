<?php

declare(strict_types=1);

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Rappasoft\LaravelAuthenticationLog\Listeners\FailedLoginListener;
use Rappasoft\LaravelAuthenticationLog\Listeners\LoginListener;
use Rappasoft\LaravelAuthenticationLog\Listeners\LogoutListener;
use Rappasoft\LaravelAuthenticationLog\Listeners\OtherDeviceLogoutListener;
use Rappasoft\LaravelAuthenticationLog\Notifications\FailedLogin;
use Rappasoft\LaravelAuthenticationLog\Notifications\NewDevice;
use Rappasoft\LaravelAuthenticationLog\Notifications\SuspiciousActivity;

return [
    // The database table name
    // You can change this if the database keys get too long for your driver
    'table_name' => 'login_audit',

    // The database connection where the login_audit table resides. Leave empty to use the default
    'db_connection' => null,

    // The events the package listens for to log
    'events' => [
        'login' => Login::class,
        'failed' => Failed::class,
        'logout' => Logout::class,
        'other-device-logout' => OtherDeviceLogout::class,
    ],

    'listeners' => [
        'login' => LoginListener::class,
        'failed' => FailedLoginListener::class,
        'logout' => LogoutListener::class,
        'other-device-logout' => OtherDeviceLogoutListener::class,
    ],

    'notifications' => [
        'new-device' => [
            // Send the NewDevice notification
            'enabled' => true,

            // Use torann/geoip to attempt to get a location
            'location' => false,

            // The Notification class to send
            'template' => NewDevice::class,
        ],
        'failed-login' => [
            // Send the FailedLogin notification
            'enabled' => false,

            // Use torann/geoip to attempt to get a location
            'location' => false,

            // The Notification class to send
            'template' => FailedLogin::class,
        ],
        'suspicious-activity' => [
            // Send the SuspiciousActivity notification
            'enabled' => false,

            // The Notification class to send
            'template' => SuspiciousActivity::class,
        ],
    ],

    'suspicious' => [
        'enabled' => true,
        'failed_login_threshold' => 5,
        'failed_login_window_minutes' => 60,
        'failed_device_window_hours' => 24,
        'location_window_minutes' => 60,
        'check_unusual_times' => false,
        'usual_hours' => [9, 10, 11, 12, 13, 14, 15, 16, 17],
    ],

    // When the clean-up command is run, delete old logs greater than `purge` days
    // Don't schedule the clean-up command if you want to keep logs forever.
    'purge' => 365,

    // If you are behind a CDN proxy, set 'behind_cdn.http_header_field' to the corresponding http header field of your CDN
    // For Cloudflare: https://developers.cloudflare.com/fundamentals/get-started/reference/http-request-headers/
    //    'behind_cdn' => [
    //        'http_header_field' => 'HTTP_CF_CONNECTING_IP' // used by Cloudflare
    //    ],

    // If you are not a CDN user, use false
    'behind_cdn' => false,
];
