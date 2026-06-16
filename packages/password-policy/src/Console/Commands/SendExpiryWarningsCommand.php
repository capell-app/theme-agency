<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Console\Commands;

use Capell\PasswordPolicy\Actions\SendPasswordExpiryWarningNotificationsAction;
use Illuminate\Console\Command;

final class SendExpiryWarningsCommand extends Command
{
    protected $signature = 'capell:password-policy:send-expiry-warnings
        {--days= : Override the configured warning window in days}';

    protected $description = 'Send password expiry warning notifications to users inside the configured warning window.';

    public function handle(): int
    {
        $days = $this->positiveIntegerOption('days');

        if ($days === 0) {
            $this->components->error((string) __('capell-password-policy::commands.positive_integer_required'));

            return self::FAILURE;
        }

        $sent = SendPasswordExpiryWarningNotificationsAction::run(days: $days);

        $this->components->info((string) __('capell-password-policy::commands.send_expiry_warnings_complete', [
            'count' => $sent,
        ]));

        return self::SUCCESS;
    }

    private function positiveIntegerOption(string $name): ?int
    {
        $option = $this->option($name);

        if ($option === null || $option === '') {
            return null;
        }

        if (! is_string($option) && ! is_int($option)) {
            return 0;
        }

        $value = (string) $option;

        return ctype_digit($value) && (int) $value > 0 ? (int) $value : 0;
    }
}
