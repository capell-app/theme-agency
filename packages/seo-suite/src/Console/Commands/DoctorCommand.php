<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Console\Commands;

use Capell\SeoSuite\Actions\BuildSeoSuiteDoctorReportAction;
use Capell\SeoSuite\Data\SeoSuiteDoctorCheckData;
use Illuminate\Console\Command;

final class DoctorCommand extends Command
{
    protected $description = 'Diagnose SEO Suite public crawler outputs and installation health';

    protected $signature = 'capell:seo-suite-doctor
                            {--base-url= : Base public URL to request generated endpoints from}
                            {--skip-http : Skip outbound HTTP endpoint checks}';

    public function handle(): int
    {
        $checks = BuildSeoSuiteDoctorReportAction::run(
            baseUrl: $this->optionString('base-url'),
            includeHttp: ! (bool) $this->option('skip-http'),
        );

        $this->newLine();
        $this->info('Capell SEO Suite Doctor');
        $this->newLine();

        $this->table(
            ['Area', 'Status', 'Check', 'Detail'],
            $checks->map(fn (SeoSuiteDoctorCheckData $check): array => [
                $check->area,
                mb_strtoupper($check->status),
                $check->message,
                $check->detail ?? '',
            ])->all(),
        );

        return $checks->contains(fn (SeoSuiteDoctorCheckData $check): bool => $check->status === 'fail')
            ? Command::FAILURE
            : Command::SUCCESS;
    }

    private function optionString(string $key): ?string
    {
        $value = $this->option($key);

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
