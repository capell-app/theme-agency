<?php

declare(strict_types=1);

namespace Capell\Newsletter\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\FormBuilder\Events\FormSubmitted;
use Capell\Newsletter\Actions\EvaluateNewsletterSegmentAction;
use Capell\Newsletter\Enums\SegmentType;
use Capell\Newsletter\Listeners\SubscribeFromFormSubmission;
use Capell\Newsletter\Models\Segment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class NewsletterHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->formSubscriptionCheck(),
            $check->providerSyncRetryCheck(),
            $check->providerWebhookCheck(),
            $check->segmentEvaluationCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the Form Builder listener is wired and consent storage exists,
     * so submissions can create subscribers with consent evidence.
     */
    public function formSubscriptionCheck(): DoctorCheckResultData
    {
        $listenerRegistered = $this->formSubmissionListenerRegistered();
        $missingTables = $this->missingTables(['newsletter_subscribers', 'newsletter_consent_events']);
        $passed = $listenerRegistered && $missingTables === [];

        $message = match (true) {
            ! $listenerRegistered => 'The Form Builder submission listener is not registered, so form submissions cannot create subscribers.',
            $missingTables !== [] => 'Missing subscriber/consent tables: ' . implode(', ', $missingTables) . '.',
            default => 'Form submissions are wired to create subscribers and record consent evidence.',
        };

        return new DoctorCheckResultData(
            label: 'Newsletter form subscription capture',
            passed: $passed,
            message: $message,
            remediation: $passed
                ? null
                : 'Ensure NewsletterServiceProvider registers the FormSubmitted listener and the newsletter migrations have run.',
        );
    }

    /**
     * Asserts the durable sync-attempt table exists so provider sync failures
     * can be retried rather than lost.
     */
    public function providerSyncRetryCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables(['newsletter_sync_attempts']);
        $passed = $missingTables === [];

        return new DoctorCheckResultData(
            label: 'Newsletter provider sync retry storage',
            passed: $passed,
            message: $passed
                ? 'Provider sync failures are persisted to durable sync attempts for retry.'
                : 'Missing sync attempt table: ' . implode(', ', $missingTables) . '.',
            remediation: $passed
                ? null
                : 'Run the newsletter migrations to create the newsletter_sync_attempts table.',
        );
    }

    /**
     * Asserts the webhook idempotency table exists so inbound provider webhooks
     * are not reprocessed.
     */
    public function providerWebhookCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables(['newsletter_processed_webhook_events']);
        $passed = $missingTables === [];

        return new DoctorCheckResultData(
            label: 'Newsletter provider webhook idempotency',
            passed: $passed,
            message: $passed
                ? 'Provider webhooks are de-duplicated through the processed webhook events table.'
                : 'Missing webhook idempotency table: ' . implode(', ', $missingTables) . '.',
            remediation: $passed
                ? null
                : 'Run the newsletter migrations to create the newsletter_processed_webhook_events table.',
        );
    }

    /**
     * Asserts segment evaluation resolves to a subscriber query builder for both
     * static and dynamic segments.
     */
    public function segmentEvaluationCheck(): DoctorCheckResultData
    {
        if ($this->missingTables(['newsletter_segments', 'newsletter_subscribers']) !== []) {
            return new DoctorCheckResultData(
                label: 'Newsletter segment evaluation',
                passed: false,
                message: 'Segment evaluation cannot run because the segment or subscriber tables are missing.',
                remediation: 'Run the newsletter migrations to create the segment and subscriber tables.',
            );
        }

        $evaluatesToBuilder = $this->segmentEvaluatesToBuilder();

        return new DoctorCheckResultData(
            label: 'Newsletter segment evaluation',
            passed: $evaluatesToBuilder,
            message: $evaluatesToBuilder
                ? 'Static and dynamic segments evaluate to a typed subscriber query builder.'
                : 'Segment evaluation did not return a subscriber query builder.',
            remediation: $evaluatesToBuilder
                ? null
                : 'Inspect EvaluateNewsletterSegmentAction; it must return an Eloquent query builder.',
        );
    }

    public function formSubmissionListenerRegistered(): bool
    {
        if (! class_exists(FormSubmitted::class)) {
            return false;
        }

        foreach (Event::getRawListeners()[FormSubmitted::class] ?? [] as $listener) {
            if ($listener === SubscribeFromFormSubmission::class) {
                return true;
            }
        }

        return false;
    }

    public function segmentEvaluatesToBuilder(): bool
    {
        try {
            $staticSegment = new Segment(['type' => SegmentType::Static]);
            $dynamicSegment = new Segment(['type' => SegmentType::SavedFilter, 'filters' => []]);

            return EvaluateNewsletterSegmentAction::run($staticSegment) instanceof Builder
                && EvaluateNewsletterSegmentAction::run($dynamicSegment) instanceof Builder;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  list<string>  $tableNames
     * @return list<string>
     */
    public function missingTables(array $tableNames): array
    {
        return array_values(array_filter(
            $tableNames,
            static fn (string $tableName): bool => ! Schema::hasTable($tableName),
        ));
    }
}
