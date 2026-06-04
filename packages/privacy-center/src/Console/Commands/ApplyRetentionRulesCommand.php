<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Console\Commands;

use Capell\PrivacyCenter\Actions\ApplyRetentionRulesAction;
use Capell\PrivacyCenter\Data\RetentionExecutionResultData;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

final class ApplyRetentionRulesCommand extends Command
{
    protected $signature = 'privacy:apply-retention {--json : Output the retention execution summary as JSON}';

    protected $description = 'Apply active Privacy Center retention rules.';

    public function handle(): int
    {
        $results = ApplyRetentionRulesAction::run();

        if ($this->option('json') === true) {
            $this->line(json_encode($this->rows($results), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->components->info((string) __('capell-privacy-center::privacy.commands.apply_retention.summary', [
            'rules' => $results->count(),
            'matched' => $results->sum(static fn (RetentionExecutionResultData $result): int => $result->matchedRecords),
            'affected' => $results->sum(static fn (RetentionExecutionResultData $result): int => $result->affectedRecords),
        ]));

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, RetentionExecutionResultData>  $results
     * @return list<array{rule_id: int, record_type: string, action: string, matched_records: int, affected_records: int}>
     */
    private function rows(Collection $results): array
    {
        return array_values($results
            ->map(static fn (RetentionExecutionResultData $result): array => [
                'rule_id' => $result->ruleId,
                'record_type' => $result->recordType,
                'action' => $result->action->value,
                'matched_records' => $result->matchedRecords,
                'affected_records' => $result->affectedRecords,
            ])
            ->values()
            ->all());
    }
}
