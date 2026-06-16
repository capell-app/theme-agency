<?php

declare(strict_types=1);

namespace Capell\PublicActions\Actions;

use Capell\Core\Actions\LoadSiteDomainFromUrlAction;
use Capell\Core\Models\SiteDomain;
use Capell\PublicActions\Contracts\PublicActionHandler;
use Capell\PublicActions\Data\PublicActionMetadataData;
use Capell\PublicActions\Data\PublicActionPayloadData;
use Capell\PublicActions\Data\PublicActionResultData;
use Capell\PublicActions\Data\PublicActionSubmissionData;
use Capell\PublicActions\Enums\PublicActionDestinationStatus;
use Capell\PublicActions\Enums\PublicActionDispatchStatus;
use Capell\PublicActions\Enums\PublicActionStatus;
use Capell\PublicActions\Enums\PublicActionSubmissionStatus;
use Capell\PublicActions\Jobs\DispatchPublicActionDestinationJob;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionSubmission;
use Capell\PublicActions\Support\PublicActionHandlerRegistry;
use Capell\PublicActions\Support\PublicActionSpamProtectionAdapterRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static PublicActionResultData run(PublicAction|string $action, array<string, mixed> $input, ?Request $request = null)
 */
final class SubmitPublicActionAction
{
    use AsAction;

    /**
     * Hard cap on accepted payload byte size when an action has no payload_schema.
     * Keeps anonymous submissions bounded so misconfigured actions cannot become
     * a data-exfiltration / DoS amplification vector.
     */
    private const int UNSCHEMED_PAYLOAD_BYTE_LIMIT = 16 * 1024;

    public function __construct(
        private readonly PublicActionHandlerRegistry $handlers,
        private readonly PublicActionSpamProtectionAdapterRegistry $spamProtectionAdapters,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function handle(PublicAction|string $action, array $input, ?Request $request = null): PublicActionResultData
    {
        $publicAction = $this->resolve($action, $request);

        $this->assertActionIsAvailable($publicAction);
        $this->assertSpamProtectionPasses($input, $request);

        $payload = $this->validatedPayload($publicAction, $input);
        $idempotencyKey = $this->idempotencyKey($input, $request);

        $submission = $this->createSubmission($publicAction, $payload, $idempotencyKey, $request);

        if (! $submission->wasRecentlyCreated) {
            return $this->resultFromExistingSubmission($publicAction, $submission, $request);
        }

        try {
            $result = $this->resolveHandler($publicAction)->handle(new PublicActionSubmissionData(
                actionKey: $publicAction->key,
                payload: new PublicActionPayloadData($submission->payload ?? []),
                metadata: PublicActionMetadataData::from($submission->metadata ?? []),
                sourceType: $submission->source_type,
                sourceId: $submission->source_id,
            ));
        } catch (ValidationException $exception) {
            $submission->forceFill(['status' => PublicActionSubmissionStatus::Failed])->save();

            throw $exception;
        } catch (Throwable $exception) {
            $submission->forceFill([
                'status' => PublicActionSubmissionStatus::Failed,
                'metadata' => [
                    ...($submission->metadata ?? []),
                    'handler_failed' => true,
                    'handler_error_type' => class_basename($exception),
                ],
            ])->save();

            throw ValidationException::withMessages([
                'action' => __('capell-public-actions::generic.unavailable'),
            ]);
        }

        $dispatches = DB::transaction(function () use ($publicAction, $submission, $result): array {
            $submission->forceFill([
                'status' => $result->success ? PublicActionSubmissionStatus::Handled : PublicActionSubmissionStatus::Failed,
                'metadata' => [
                    ...($submission->metadata ?? []),
                    'result_success' => $result->success,
                    'result_message' => $result->message,
                    'result_redirect_url' => $result->redirectUrl,
                ],
            ])->save();

            return $result->success ? $this->prepareDestinationDispatches($publicAction, $submission) : [];
        });

        $this->dispatchPreparedDestinations($dispatches, $submission);

        return $this->resultWithActionDefaults($publicAction, $submission, $result, $request);
    }

    public function resolve(PublicAction|string $action, ?Request $request = null): PublicAction
    {
        if ($action instanceof PublicAction) {
            return $action;
        }

        $query = PublicAction::query()->where('key', $action);
        $siteId = $request instanceof Request ? $this->siteId($request) : $this->siteId(request());

        if ($siteId !== null) {
            $query
                ->where(fn (Builder $builder): Builder => $builder
                    ->where('site_scope_key', 'global')
                    ->orWhere('site_scope_key', 'site:' . $siteId))
                ->orderByRaw('CASE WHEN site_scope_key = ? THEN 0 ELSE 1 END', ['site:' . $siteId]);
        } else {
            $query->where('site_scope_key', 'global');
        }

        return $query->firstOrFail();
    }

    /**
     * @throws ValidationException
     */
    private function assertActionIsAvailable(PublicAction $action): void
    {
        if ($action->status === PublicActionStatus::Active) {
            return;
        }

        throw ValidationException::withMessages([
            'action' => __('capell-public-actions::generic.unavailable'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function validatedPayload(PublicAction $action, array $input): array
    {
        $validated = Validator::make($input, [
            'source_type' => ['nullable', 'string', 'max:255'],
            'source_id' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $payload = $this->validatedSchemaPayload($action, $input)
            ?? $this->cappedUnschemedPayload($action, $input);

        return [
            ...$payload,
            ...Arr::only($validated, ['source_type', 'source_id']),
        ];
    }

    /**
     * Apply a hard byte-size cap when the action has no payload schema so that
     * misconfigured actions cannot accept unbounded submissions. Framework
     * fields are stripped; oversized payloads raise a validation error so the
     * caller gets a clear signal instead of silent truncation.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    private function cappedUnschemedPayload(PublicAction $action, array $input): array
    {
        if (config('capell-public-actions.allow_schemaless_payloads', false) !== true) {
            throw ValidationException::withMessages([
                'payload' => __('capell-public-actions::generic.schema_required'),
            ]);
        }

        $candidate = Arr::except($input, [
            '_token',
            '_method',
            'g-recaptcha-response',
            'cf-turnstile-response',
            'h-captcha-response',
            '_hp',
            'idempotency_key',
            'source_type',
            'source_id',
        ]);

        $encoded = json_encode($candidate, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $byteSize = is_string($encoded) ? strlen($encoded) : self::UNSCHEMED_PAYLOAD_BYTE_LIMIT + 1;

        Log::warning('capell-public-actions: submission accepted without payload schema; payload is capped.', [
            'action_key' => $action->key,
            'payload_byte_size' => $byteSize,
            'payload_field_count' => count($candidate),
        ]);

        if ($byteSize > self::UNSCHEMED_PAYLOAD_BYTE_LIMIT) {
            throw ValidationException::withMessages([
                'payload' => __('capell-public-actions::generic.schema_required'),
            ]);
        }

        return $candidate;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>|null
     */
    private function validatedSchemaPayload(PublicAction $action, array $input): ?array
    {
        $fields = $this->schemaFields($action);

        if ($fields === []) {
            return null;
        }

        $rules = [];
        $fieldKeys = [];

        foreach ($fields as $field) {
            $fieldKey = $this->schemaFieldKey($field);

            if ($fieldKey === null) {
                continue;
            }

            $fieldKeys[] = $fieldKey;
            $rules[$fieldKey] = $this->schemaFieldRules($field);
        }

        if ($rules === []) {
            return null;
        }

        Validator::make($input, $rules)->validate();

        return Arr::only($input, $fieldKeys);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function schemaFields(PublicAction $action): array
    {
        $fields = data_get($action->payload_schema, 'fields');

        if (! is_array($fields)) {
            return [];
        }

        return array_values(array_filter(
            $fields,
            is_array(...),
        ));
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function schemaFieldKey(array $field): ?string
    {
        $fieldKey = $field['key'] ?? null;

        if (! is_string($fieldKey) || trim($fieldKey) === '') {
            return null;
        }

        return $fieldKey;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return list<string>
     */
    private function schemaFieldRules(array $field): array
    {
        $rules = [(bool) ($field['required'] ?? false) ? 'required' : 'nullable'];
        $fieldType = is_string($field['type'] ?? null) ? $field['type'] : 'text';

        if ($fieldType === 'email') {
            $rules[] = 'email:rfc';
            $rules[] = 'max:255';

            return $rules;
        }

        if ($fieldType === 'checkbox') {
            $rules[] = (bool) ($field['required'] ?? false) ? 'accepted' : 'boolean';

            return $rules;
        }

        if ($fieldType === 'textarea') {
            $rules[] = 'string';
            $rules[] = 'max:10000';

            return $rules;
        }

        $rules[] = 'string';
        $rules[] = 'max:255';

        return $rules;
    }

    private function resolveHandler(PublicAction $action): PublicActionHandler
    {
        $handler = $this->handlers->resolve($action->handler_key);

        if ($handler instanceof PublicActionHandler) {
            return $handler;
        }

        throw new InvalidArgumentException(sprintf('Public action handler [%s] is not registered.', $action->handler_key));
    }

    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    private function assertSpamProtectionPasses(array $input, ?Request $request): void
    {
        foreach ($this->spamProtectionAdapters->enabled() as $adapter) {
            $result = $adapter->check($input, $request);

            if ($result->passed) {
                continue;
            }

            throw ValidationException::withMessages([
                $result->field ?? 'spam' => $result->message ?? __('capell-public-actions::generic.spam_detected'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    private function idempotencyKey(array $input, ?Request $request): ?string
    {
        $candidate = $request?->header('Idempotency-Key') ?? $input['idempotency_key'] ?? null;

        if ($candidate === null || $candidate === '') {
            return null;
        }

        if (! is_string($candidate) || strlen($candidate) > 128 || preg_match('/^[A-Za-z0-9._:-]+$/', $candidate) !== 1) {
            throw ValidationException::withMessages([
                'idempotency_key' => __('validation.regex', ['attribute' => 'idempotency key']),
            ]);
        }

        return $candidate;
    }

    private function resultFromExistingSubmission(
        PublicAction $action,
        PublicActionSubmission $submission,
        ?Request $request,
    ): PublicActionResultData {
        return $this->resultWithActionDefaults($action, $submission, new PublicActionResultData(
            success: $submission->status === PublicActionSubmissionStatus::Handled,
            message: is_string($submission->metadata['result_message'] ?? null)
                ? $submission->metadata['result_message']
                : null,
            redirectUrl: is_string($submission->metadata['result_redirect_url'] ?? null)
                ? $submission->metadata['result_redirect_url']
                : null,
        ), $request);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function createSubmission(
        PublicAction $action,
        array $payload,
        ?string $idempotencyKey,
        ?Request $request,
    ): PublicActionSubmission {
        $values = [
            'site_id' => $action->site_id,
            'source_type' => $payload['source_type'] ?? null,
            'source_id' => $payload['source_id'] ?? null,
            'payload' => Arr::except($payload, ['source_type', 'source_id']),
            'metadata' => $this->metadata($action, $request)->toArray(),
            'status' => PublicActionSubmissionStatus::Received,
            'submitted_at' => now(),
        ];

        if ($idempotencyKey === null) {
            return PublicActionSubmission::query()->create([
                ...$values,
                'public_action_id' => $action->getKey(),
                'idempotency_key' => null,
            ]);
        }

        return PublicActionSubmission::query()->createOrFirst(
            [
                'public_action_id' => $action->getKey(),
                'idempotency_key' => $idempotencyKey,
            ],
            $values,
        );
    }

    private function metadata(PublicAction $action, ?Request $request): PublicActionMetadataData
    {
        if (! $request instanceof Request) {
            return new PublicActionMetadataData(siteId: $action->site_id);
        }

        return new PublicActionMetadataData(
            ipHash: hash('sha256', (string) $request->ip()),
            userAgent: $request->userAgent(),
            url: $request->fullUrl(),
            referer: $request->headers->get('referer'),
            route: $request->route()?->getName(),
            siteId: $action->site_id ?? $this->siteId($request),
        );
    }

    private function resultWithActionDefaults(
        PublicAction $action,
        PublicActionSubmission $submission,
        PublicActionResultData $result,
        ?Request $request,
    ): PublicActionResultData {
        return new PublicActionResultData(
            success: $result->success,
            message: $result->message ?? ($result->success ? $action->success_message : $action->failure_message),
            redirectUrl: $result->redirectUrl
                ?? ($result->success ? $action->success_redirect_url : $action->failure_redirect_url)
                ?? ($result->success ? $this->payloadRedirectUrl($submission, $request) : null),
            createdModelType: $result->createdModelType,
            createdModelId: $result->createdModelId,
        );
    }

    /**
     * @return list<array{destination: PublicActionDestination, attempt: PublicActionDispatchAttempt, sync: bool}>
     */
    private function prepareDestinationDispatches(PublicAction $action, PublicActionSubmission $submission): array
    {
        $destinations = $action->destinations()
            ->where('status', PublicActionDestinationStatus::Active)
            ->get();
        $dispatches = [];

        foreach ($destinations as $destination) {
            throw_unless($destination instanceof PublicActionDestination);

            $attempt = PublicActionDispatchAttempt::query()->create([
                'public_action_submission_id' => $submission->getKey(),
                'public_action_destination_id' => $destination->getKey(),
                'adapter' => $destination->adapter,
                'status' => PublicActionDispatchStatus::Pending,
                'attempt' => $this->nextDestinationAttemptNumber($destination, $submission),
                'request_hash' => hash('sha256', sprintf(
                    'pending:%s:%s:%s',
                    $this->modelKey($submission),
                    $this->modelKey($destination),
                    $destination->adapter,
                )),
                'response_status' => null,
                'response_summary' => null,
                'error_message' => null,
                'dispatched_at' => null,
            ]);

            $dispatches[] = [
                'destination' => $destination,
                'attempt' => $attempt,
                'sync' => (bool) data_get($destination->settings, 'sync', false),
            ];
        }

        return $dispatches;
    }

    /**
     * @param  list<array{destination: PublicActionDestination, attempt: PublicActionDispatchAttempt, sync: bool}>  $dispatches
     */
    private function dispatchPreparedDestinations(array $dispatches, PublicActionSubmission $submission): void
    {
        foreach ($dispatches as $dispatch) {
            if ($dispatch['sync']) {
                DispatchPublicActionDestinationAction::run($dispatch['destination'], $submission, $dispatch['attempt']);

                continue;
            }

            dispatch(new DispatchPublicActionDestinationJob($dispatch['destination'], $submission, $dispatch['attempt']))
                ->afterCommit();
        }
    }

    private function nextDestinationAttemptNumber(PublicActionDestination $destination, PublicActionSubmission $submission): int
    {
        $attempt = PublicActionDispatchAttempt::query()
            ->where('public_action_submission_id', $submission->getKey())
            ->where('public_action_destination_id', $destination->getKey())
            ->max('attempt');

        return (is_numeric($attempt) ? (int) $attempt : 0) + 1;
    }

    private function modelKey(Model $model): string
    {
        $key = $model->getKey();

        if (is_int($key) || is_string($key)) {
            return (string) $key;
        }

        return '';
    }

    private function payloadRedirectUrl(PublicActionSubmission $submission, ?Request $request): ?string
    {
        $redirectUrl = data_get($submission->payload, 'redirect');

        if (! is_string($redirectUrl) || $redirectUrl === '') {
            return null;
        }

        if (str_starts_with($redirectUrl, '/') && ! str_starts_with($redirectUrl, '//')) {
            return $redirectUrl;
        }

        if (! $request instanceof Request || filter_var($redirectUrl, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $redirectScheme = parse_url($redirectUrl, PHP_URL_SCHEME);
        $redirectHost = parse_url($redirectUrl, PHP_URL_HOST);

        return is_string($redirectScheme)
            && in_array(strtolower($redirectScheme), ['http', 'https'], true)
            && is_string($redirectHost)
            && strcasecmp($redirectHost, $request->getHost()) === 0
            ? $redirectUrl
            : null;
    }

    private function siteId(Request $request): ?int
    {
        if (! Schema::hasTable('sites') || ! Schema::hasTable('site_domains')) {
            return null;
        }

        if ($request->fullUrl() === '') {
            return null;
        }

        $resolved = LoadSiteDomainFromUrlAction::run($request->fullUrl());
        $siteDomain = is_array($resolved) ? ($resolved[0] ?? null) : null;

        return $siteDomain instanceof SiteDomain ? $siteDomain->site_id : null;
    }
}
