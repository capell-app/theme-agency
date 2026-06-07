<?php

declare(strict_types=1);

use Capell\EmailStudio\Http\Controllers\ProviderWebhookController;
use Illuminate\Support\Facades\Route;

$prefix = config('capell-email-studio.public_route_prefix', 'mail');
$prefix = is_string($prefix) && $prefix !== '' ? trim($prefix, '/') : 'mail';

Route::post($prefix . '/provider-events/{token}', ProviderWebhookController::class)
    ->name('capell-email-studio.provider-events');
