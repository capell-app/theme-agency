<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\FormBuilder\Events\FormSubmitted;
use Capell\Newsletter\Console\Commands\RequeueDueProviderSyncAttemptsCommand;
use Capell\Newsletter\Enums\SegmentType;
use Capell\Newsletter\Enums\SyncStatus;
use Capell\Newsletter\Listeners\SubscribeFromFormSubmission;
use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Models\SyncAttempt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static Collection<int, DoctorCheckResultData> run(?string $key = null)
 */
final class BuildNewsletterHealthDiagnosticsAction
{
    use AsAction;

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public function handle(?string $key = null): Collection
    {
        $assertions = collect([
            'newsletter.form-subscription' => $this->formSubscriptionCheck(),
            'newsletter.provider-sync-retry' => $this->providerSyncRetryCheck(),
            'newsletter.provider-webhooks' => $this->providerWebhookCheck(),
            'newsletter.segments' => $this->segmentEvaluationCheck(),
        ]);

        if ($key === null) {
            return $assertions->values();
        }

        $assertion = $assertions->get($key);

        return $assertion instanceof DoctorCheckResultData
            ? collect([$assertion])
            : collect();
    }

    public function formSubscriptionCheck(): DoctorCheckResultData
    {
        $listenerRegistered = $this->formSubmissionListenerRegistered();
        $missingTables = $this->missingTables(['newsletter_subscribers', 'newsletter_consent_events']);
        $passed = $listenerRegistered && $missingTables === [];

        $messageKey = match (true) {
            ! $listenerRegistered => 'capell-newsletter::health.form_subscription.listener_missing',
            $missingTables !== [] => 'capell-newsletter::health.form_subscription.tables_missing',
            default => 'capell-newsletter::health.form_subscription.passed',
        };

        return new DoctorCheckResultData(
            label: (string) __('capell-newsletter::health.form_subscription.label'),
            passed: $passed,
            message: (string) __($messageKey, ['tables' => implode(', ', $missingTables)]),
            remediation: $passed
                ? null
                : (string) __('capell-newsletter::health.form_subscription.remediation'),
        );
    }

    public function providerSyncRetryCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables(['newsletter_sync_attempts']);
        $pipelineQueryable = $missingTables === [] && $this->syncRetryPipelineQueryable();
        $exhaustedAttempts = $missingTables === [] ? $this->exhaustedSyncAttemptsCount() : 0;
        $passed = $missingTables === [] && $pipelineQueryable && $exhaustedAttempts === 0;

        $messageKey = match (true) {
            $missingTables !== [] => 'capell-newsletter::health.provider_sync_retry.tables_missing',
            ! $pipelineQueryable => 'capell-newsletter::health.provider_sync_retry.pipeline_unavailable',
            $exhaustedAttempts > 0 => 'capell-newsletter::health.provider_sync_retry.exhausted_attempts',
            default => 'capell-newsletter::health.provider_sync_retry.passed',
        };

        return new DoctorCheckResultData(
            label: (string) __('capell-newsletter::health.provider_sync_retry.label'),
            passed: $passed,
            message: (string) __($messageKey, [
                'count' => $exhaustedAttempts,
                'tables' => implode(', ', $missingTables),
            ]),
            remediation: $passed
                ? null
                : (string) __('capell-newsletter::health.provider_sync_retry.remediation'),
        );
    }

    public function providerWebhookCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables(['newsletter_processed_webhook_events']);
        $routeRegistered = Route::has('capell-newsletter.provider-webhook');
        $actionResolvable = $this->providerWebhookActionResolvable();
        $passed = $missingTables === [] && $routeRegistered && $actionResolvable;

        $messageKey = match (true) {
            $missingTables !== [] => 'capell-newsletter::health.provider_webhooks.tables_missing',
            ! $routeRegistered => 'capell-newsletter::health.provider_webhooks.route_missing',
            ! $actionResolvable => 'capell-newsletter::health.provider_webhooks.action_missing',
            default => 'capell-newsletter::health.provider_webhooks.passed',
        };

        return new DoctorCheckResultData(
            label: (string) __('capell-newsletter::health.provider_webhooks.label'),
            passed: $passed,
            message: (string) __($messageKey, ['tables' => implode(', ', $missingTables)]),
            remediation: $passed
                ? null
                : (string) __('capell-newsletter::health.provider_webhooks.remediation'),
        );
    }

    public function segmentEvaluationCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables(['newsletter_segments', 'newsletter_subscribers']);

        if ($missingTables !== []) {
            return new DoctorCheckResultData(
                label: (string) __('capell-newsletter::health.segments.label'),
                passed: false,
                message: (string) __('capell-newsletter::health.segments.tables_missing', [
                    'tables' => implode(', ', $missingTables),
                ]),
                remediation: (string) __('capell-newsletter::health.segments.remediation'),
            );
        }

        $evaluatesToBuilder = $this->segmentEvaluatesToBuilder();

        return new DoctorCheckResultData(
            label: (string) __('capell-newsletter::health.segments.label'),
            passed: $evaluatesToBuilder,
            message: $evaluatesToBuilder
                ? (string) __('capell-newsletter::health.segments.passed')
                : (string) __('capell-newsletter::health.segments.builder_missing'),
            remediation: $evaluatesToBuilder
                ? null
                : (string) __('capell-newsletter::health.segments.remediation'),
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

    public function syncRetryPipelineQueryable(): bool
    {
        if (! class_exists(RequeueDueProviderSyncAttemptsCommand::class)) {
            return false;
        }

        $initialTransactionLevel = DB::transactionLevel();

        try {
            DB::beginTransaction();

            $requeuedCount = RequeueDueProviderSyncAttemptsAction::run(limit: 1, dispatchJobs: false);

            return $requeuedCount >= 0;
        } catch (Throwable) {
            return false;
        } finally {
            while (DB::transactionLevel() > $initialTransactionLevel) {
                DB::rollBack();
            }
        }
    }

    public function exhaustedSyncAttemptsCount(): int
    {
        return SyncAttempt::query()
            ->where('sync_status', SyncStatus::Exhausted)
            ->count();
    }

    public function providerWebhookActionResolvable(): bool
    {
        try {
            return app()->make(HandleProviderWebhookAction::class) instanceof HandleProviderWebhookAction;
        } catch (Throwable) {
            return false;
        }
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
