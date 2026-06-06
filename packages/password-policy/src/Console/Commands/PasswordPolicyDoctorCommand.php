<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Console\Commands;

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\PasswordPolicy\Health\PasswordPolicyHealthCheck;
use Illuminate\Console\Command;

final class PasswordPolicyDoctorCommand extends Command
{
    protected $signature = 'capell:password-policy:doctor {--json : Output diagnostics as JSON}';

    protected $description = 'Run Password Policy install-health diagnostics.';

    public function handle(): int
    {
        $results = PasswordPolicyHealthCheck::runDiagnostics();

        if ((bool) $this->option('json')) {
            $this->line(json_encode(
                $results->map(static fn (DoctorCheckResultData $result): array => [
                    'label' => $result->label,
                    'passed' => $result->passed,
                    'message' => $result->message,
                    'remediation' => $result->remediation,
                ])->values()->all(),
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
            ));

            return PasswordPolicyHealthCheck::passed() ? self::SUCCESS : self::FAILURE;
        }

        foreach ($results as $result) {
            $message = sprintf(
                '%s: %s',
                $result->label,
                $result->passed ? __('capell-password-policy::commands.doctor_passed') : __('capell-password-policy::commands.doctor_failed'),
            );

            $result->passed ? $this->components->info($message) : $this->components->error($message);
        }

        return PasswordPolicyHealthCheck::passed() ? self::SUCCESS : self::FAILURE;
    }
}
