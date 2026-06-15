<?php

declare(strict_types=1);

namespace Capell\AccessGate\Console\Commands;

use Capell\AccessGate\Actions\ExportAccessGateAuditCsvAction;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

final class AccessGateAuditExportCommand extends Command
{
    protected $signature = 'capell:access-gate-audit-export
        {--path= : Optional file path to write instead of printing CSV to stdout}
        {--area= : Limit the export to one access area key}
        {--type= : Limit the export to one event type value}
        {--from= : Export events occurring at or after this date/time}
        {--to= : Export events occurring at or before this date/time}
        {--limit= : Maximum number of audit events to export}';

    protected $description = 'Export Access Gate audit events as CSV.';

    public function handle(): int
    {
        $from = $this->dateOption('from');
        $to = $this->dateOption('to');

        if ($from === false || $to === false) {
            return self::INVALID;
        }

        if ($from instanceof CarbonImmutable && $to instanceof CarbonImmutable && $from->greaterThan($to)) {
            $this->components->error((string) __('capell-access-gate::audit.invalid_range'));

            return self::INVALID;
        }

        $limit = $this->limitOption();

        if ($limit === false) {
            return self::INVALID;
        }

        $csv = ExportAccessGateAuditCsvAction::run(
            areaKey: $this->stringOption('area'),
            type: $this->stringOption('type'),
            from: $from,
            to: $to,
            limit: $limit,
        );

        $path = $this->stringOption('path');

        if ($path === null) {
            $this->output->write($csv);

            return self::SUCCESS;
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $csv);

        $this->components->info((string) __('capell-access-gate::audit.export_written', [
            'path' => $path,
        ]));

        return self::SUCCESS;
    }

    private function stringOption(string $name): ?string
    {
        $value = $this->option($name);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function limitOption(): int|false|null
    {
        $value = $this->option('limit');

        if ($value === null || $value === false || $value === '') {
            return null;
        }

        if (! is_numeric($value) || (int) $value < 1) {
            $this->components->error((string) __('capell-access-gate::audit.invalid_limit'));

            return false;
        }

        return (int) $value;
    }

    private function dateOption(string $name): CarbonImmutable|false|null
    {
        $value = $this->stringOption($name);

        if ($value === null) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            $this->components->error((string) __('capell-access-gate::audit.invalid_date', [
                'option' => $name,
            ]));

            return false;
        }
    }
}
