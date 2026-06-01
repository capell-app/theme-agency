<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Data\SubscriberData;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\ImportBatchStatus;
use Capell\Newsletter\Enums\ImportBatchType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\ImportBatch;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class ImportSubscribersAction
{
    use AsAction;

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, int>  $tagIds
     */
    public function handle(
        int $siteId,
        array $rows,
        string $consentBasis,
        bool $dryRun = true,
        array $tagIds = [],
        ?Model $actor = null,
        ?string $filename = null,
    ): ImportBatch {
        $maxRows = (int) config('capell-newsletter.imports.max_rows', 10000);

        if ($maxRows > 0 && count($rows) > $maxRows) {
            throw ValidationException::withMessages([
                'csv' => [__('capell-newsletter::messages.import_too_many_rows', ['max' => $maxRows])],
            ]);
        }

        $validRows = [];
        $invalidRows = [];
        $seenEmails = [];

        if (trim($consentBasis) === '') {
            $invalidRows[] = [
                'row' => 0,
                'errors' => ['consent_basis' => [__('capell-newsletter::messages.missing_consent_basis')]],
            ];
        }

        foreach ($rows as $rowIndex => $row) {
            $validator = Validator::make($row, [
                'email' => ['required', 'email'],
                'first_name' => ['nullable', 'string'],
                'last_name' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                $invalidRows[] = [
                    'row' => $rowIndex + 1,
                    'errors' => $validator->errors()->toArray(),
                ];

                continue;
            }

            $validRow = $validator->validated();
            $emailHash = Subscriber::emailHash((string) $validRow['email']);

            if (isset($seenEmails[$emailHash])) {
                $invalidRows[] = [
                    'row' => $rowIndex + 1,
                    'errors' => ['email' => [__('capell-newsletter::messages.duplicate_import_email')]],
                ];

                continue;
            }

            $seenEmails[$emailHash] = true;
            $validRows[] = $validRow;
        }

        $batch = ImportBatch::query()->create([
            'site_id' => $siteId,
            'type' => ImportBatchType::Import,
            'status' => $dryRun ? ImportBatchStatus::DryRun : ImportBatchStatus::Processing,
            'filename' => $filename,
            'consent_basis' => $consentBasis,
            'dry_run_payload' => [
                'invalid_rows' => $invalidRows,
                'valid_rows' => $dryRun ? $validRows : [],
            ],
            'source_meta' => ['tag_ids' => $tagIds],
            'total_rows' => count($rows),
            'valid_rows' => count($validRows),
            'invalid_rows' => count($invalidRows),
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
        ]);

        if ($dryRun || $invalidRows !== []) {
            return $batch;
        }

        try {
            /** @var list<int> $subscriberIds */
            $subscriberIds = DB::transaction(function () use ($siteId, $validRows, $batch, $consentBasis, $tagIds): array {
                $subscriberIds = [];

                foreach ($validRows as $validRow) {
                    $subscriber = UpsertSubscriberAction::run(new SubscriberData(
                        siteId: $siteId,
                        email: (string) $validRow['email'],
                        status: SubscriberStatus::Subscribed,
                        firstName: is_string($validRow['first_name'] ?? null) ? $validRow['first_name'] : null,
                        lastName: is_string($validRow['last_name'] ?? null) ? $validRow['last_name'] : null,
                    ), new ConsentEvidenceData(
                        sourceType: 'csv_import',
                        sourceId: (string) $batch->getKey(),
                        consentText: $consentBasis,
                    ), ConsentEventType::Imported);

                    ApplyNewsletterTagsAction::run($subscriber, $tagIds);
                    $subscriberIds[] = (int) $subscriber->getKey();
                }

                return $subscriberIds;
            });

            foreach (Subscriber::query()->whereKey($subscriberIds)->cursor() as $subscriber) {
                QueueProviderSyncAction::run($subscriber);
            }

            $batch->forceFill([
                'status' => ImportBatchStatus::Completed,
            ])->save();
        } catch (Throwable $throwable) {
            $batch->forceFill([
                'status' => ImportBatchStatus::Failed,
            ])->save();

            throw $throwable;
        }

        return $batch->refresh();
    }
}
