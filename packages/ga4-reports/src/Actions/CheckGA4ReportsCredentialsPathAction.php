<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Actions;

use Capell\GA4Reports\Data\GA4ReportsCredentialsStatusData;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static GA4ReportsCredentialsStatusData run(string $credentialsPath)
 */
final class CheckGA4ReportsCredentialsPathAction
{
    use AsAction;

    public function handle(string $credentialsPath): GA4ReportsCredentialsStatusData
    {
        $path = trim($credentialsPath);

        if ($path === '') {
            return $this->status(
                GA4ReportsCredentialsStatusData::STATUS_MISSING,
                readable: false,
                valid: false,
                messageKey: 'capell-ga4-reports::settings.credentials_path_missing',
            );
        }

        if (! is_readable($path) || is_dir($path)) {
            return $this->status(
                GA4ReportsCredentialsStatusData::STATUS_NOT_READABLE,
                readable: false,
                valid: false,
                messageKey: 'capell-ga4-reports::settings.credentials_path_not_readable',
            );
        }

        try {
            $contents = file_get_contents($path);

            if (! is_string($contents)) {
                return $this->invalidJson();
            }

            $credentials = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return $this->invalidJson();
        }

        if (! is_array($credentials)) {
            return $this->invalidJson();
        }

        $clientEmail = $credentials['client_email'] ?? null;
        $privateKey = $credentials['private_key'] ?? null;

        if (
            ! is_string($clientEmail)
            || trim($clientEmail) === ''
            || ! is_string($privateKey)
            || trim($privateKey) === ''
        ) {
            return $this->status(
                GA4ReportsCredentialsStatusData::STATUS_INVALID_SERVICE_ACCOUNT,
                readable: true,
                valid: false,
                messageKey: 'capell-ga4-reports::settings.credentials_path_not_service_account',
            );
        }

        return $this->status(
            GA4ReportsCredentialsStatusData::STATUS_VALID,
            readable: true,
            valid: true,
            messageKey: 'capell-ga4-reports::settings.credentials_path_valid',
        );
    }

    private function invalidJson(): GA4ReportsCredentialsStatusData
    {
        return $this->status(
            GA4ReportsCredentialsStatusData::STATUS_INVALID_JSON,
            readable: true,
            valid: false,
            messageKey: 'capell-ga4-reports::settings.credentials_path_invalid_json',
        );
    }

    private function status(string $status, bool $readable, bool $valid, string $messageKey): GA4ReportsCredentialsStatusData
    {
        return new GA4ReportsCredentialsStatusData(
            status: $status,
            readable: $readable,
            valid: $valid,
            messageKey: $messageKey,
        );
    }
}
