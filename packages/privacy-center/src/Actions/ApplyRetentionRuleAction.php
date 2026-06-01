<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Data\RetentionExecutionResultData;
use Capell\PrivacyCenter\Enums\RetentionAction;
use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Models\PolicyAcceptance;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Models\RetentionRule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class ApplyRetentionRuleAction
{
    use AsAction;

    public function handle(RetentionRule $rule, ?CarbonInterface $now = null): RetentionExecutionResultData
    {
        $recordType = $this->recordType($rule);
        $now = $now instanceof CarbonInterface ? CarbonImmutable::instance($now) : CarbonImmutable::now();
        $cutoff = $now->subDays($rule->retention_days);
        $query = $this->expiredQuery($recordType, $rule, $cutoff);
        $matchedRecords = (clone $query)->count();
        $affectedRecords = $this->applyAction($query, $rule);

        return new RetentionExecutionResultData(
            ruleId: (int) $rule->getKey(),
            recordType: $recordType,
            action: $rule->action,
            matchedRecords: $matchedRecords,
            affectedRecords: $affectedRecords,
        );
    }

    /**
     * @return class-string<Model>
     */
    private function recordType(RetentionRule $rule): string
    {
        $recordType = $rule->record_type;

        if (! is_string($recordType) || $recordType === '' || ! is_subclass_of($recordType, Model::class)) {
            throw new InvalidArgumentException('Privacy retention rules require a valid Eloquent record type.');
        }

        return $recordType;
    }

    /**
     * @param  class-string<Model>  $recordType
     * @return Builder<Model>
     */
    private function expiredQuery(string $recordType, RetentionRule $rule, CarbonInterface $cutoff): Builder
    {
        /** @var Builder<Model> $query */
        $query = $recordType::query();

        if ($rule->site_id !== null) {
            $query->where('site_id', $rule->site_id);
        }

        return $query->where($this->dateColumn($recordType), '<=', $cutoff);
    }

    /**
     * @param  class-string<Model>  $recordType
     */
    private function dateColumn(string $recordType): string
    {
        return match ($recordType) {
            ConsentRecord::class => 'decided_at',
            PolicyAcceptance::class => 'accepted_at',
            PrivacyRequest::class => 'submitted_at',
            default => 'created_at',
        };
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyAction(Builder $query, RetentionRule $rule): int
    {
        return match ($rule->action) {
            RetentionAction::Delete => $query->delete(),
            RetentionAction::Anonymize => $this->anonymize($query, $rule),
            RetentionAction::Review => $this->markForReview($query, $rule),
        };
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function anonymize(Builder $query, RetentionRule $rule): int
    {
        return match ($rule->record_type) {
            ConsentRecord::class => $query->update([
                'subject_type' => null,
                'subject_id' => null,
                'source_type' => null,
                'source_id' => null,
                'ip_hash' => null,
                'user_agent_hash' => null,
                'evidence' => null,
                'metadata' => null,
            ]),
            PolicyAcceptance::class => $query->update([
                'subject_type' => null,
                'subject_id' => null,
                'ip_hash' => null,
                'user_agent_hash' => null,
                'metadata' => null,
            ]),
            PrivacyRequest::class => $query->update([
                'requester_type' => null,
                'requester_id' => null,
                'subject_type' => null,
                'subject_id' => null,
                'email_hash' => null,
                'workflow_payload' => null,
                'metadata' => null,
            ]),
            default => $query->update(['updated_at' => now()]),
        };
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function markForReview(Builder $query, RetentionRule $rule): int
    {
        $affectedRecords = 0;

        $query->eachById(function (Model $record) use ($rule, &$affectedRecords): void {
            $metadata = $record->getAttribute('metadata');
            $record->setAttribute('metadata', array_replace(is_array($metadata) ? $metadata : [], [
                'retention_review' => [
                    'rule_id' => $rule->getKey(),
                    'data_domain' => $rule->data_domain,
                    'marked_at' => now()->toISOString(),
                ],
            ]));
            $record->save();

            $affectedRecords++;
        });

        return $affectedRecords;
    }
}
