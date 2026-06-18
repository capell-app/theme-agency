<?php

declare(strict_types=1);

namespace Capell\MediaAI\Support;

use Capell\AIOrchestrator\Actions\RunAIOrchestratorCapabilityAction;
use Capell\AIOrchestrator\Data\AIOrchestratorRunData;
use Capell\Core\Models\Media;
use Capell\MediaAI\Contracts\ImageDoctor;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Capell\MediaAI\Data\ImageDoctorResult;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class AIOrchestratorImageDoctor implements ImageDoctor
{
    private const string RUN_ACTION_CLASS = RunAIOrchestratorCapabilityAction::class;

    private const string RUN_DATA_CLASS = AIOrchestratorRunData::class;

    /**
     * Lifetime of the signed URL handed to the third-party AI orchestrator for
     * private media. Kept short so the credential expires soon after the run.
     */
    private const int PRIVATE_URL_LIFETIME_MINUTES = 5;

    public function doctor(Media $media, ImageDoctorRequest $request): ImageDoctorResult
    {
        if (! class_exists(self::RUN_ACTION_CLASS) || ! class_exists(self::RUN_DATA_CLASS)) {
            return ImageDoctorResult::failure(__('capell-media-ai::media-ai.ai_orchestrator_unavailable'));
        }

        try {
            $runActionClass = self::RUN_ACTION_CLASS;
            $runDataClass = self::RUN_DATA_CLASS;

            $result = $runActionClass::run(new $runDataClass(
                moduleKey: $this->moduleKey(),
                capabilityKey: $this->capabilityKey(),
                prompt: $this->prompt($request),
                context: $this->context($media, $request),
            ));
        } catch (Throwable) {
            return ImageDoctorResult::failure(__('capell-media-ai::media-ai.provider_failed'));
        }

        return $this->result($result);
    }

    private function moduleKey(): string
    {
        $moduleKey = config('capell-media-ai.image_doctor.ai_orchestrator.module', 'media-ai');

        return is_string($moduleKey) && $moduleKey !== '' ? $moduleKey : 'media-ai';
    }

    private function capabilityKey(): string
    {
        $capabilityKey = config('capell-media-ai.image_doctor.ai_orchestrator.capability', 'doctor-image');

        return is_string($capabilityKey) && $capabilityKey !== '' ? $capabilityKey : 'doctor-image';
    }

    private function prompt(ImageDoctorRequest $request): string
    {
        return trim(sprintf(
            'Perform the Media AI image doctor operation [%s] for locale [%s] with budget cents [%s] and model [%s]. Instructions: %s',
            $request->operation,
            $request->locale ?? 'default',
            $request->budgetCents === null ? 'default' : (string) $request->budgetCents,
            $request->model ?? 'default',
            $request->instructions,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function context(Media $media, ImageDoctorRequest $request): array
    {
        return [
            'operation' => $request->operation,
            'instructions' => $request->instructions,
            'locale' => $request->locale,
            'budget_cents' => $request->budgetCents,
            'model' => $request->model,
            'media' => [
                'id' => $media->getKey(),
                'model_type' => $media->model_type,
                'model_id' => $media->model_id,
                'collection_name' => $media->collection_name,
                'name' => $media->name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'url' => $this->resolveMediaUrl($media),
            ],
        ];
    }

    /**
     * Resolve a URL safe to hand to the third-party orchestrator.
     *
     * For public-visibility media a normal (permanent) URL is acceptable. For
     * private/non-public media a permanent URL would leak indefinitely, so a
     * short-lived signed/temporary URL is issued instead. If the disk cannot
     * mint a temporary URL the URL is omitted entirely rather than leaking the
     * permanent one.
     */
    private function resolveMediaUrl(Media $media): ?string
    {
        if (! $this->isPrivateMedia($media)) {
            return $media->getFullUrl();
        }

        try {
            $temporaryUrl = $media->getTemporaryUrl(
                now()->addMinutes(self::PRIVATE_URL_LIFETIME_MINUTES),
            );
        } catch (Throwable) {
            return null;
        }

        return $temporaryUrl !== '' ? $temporaryUrl : null;
    }

    private function isPrivateMedia(Media $media): bool
    {
        $disk = $media->disk;

        if (! is_string($disk) || $disk === '') {
            return false;
        }

        if (config("filesystems.disks.{$disk}.visibility") === 'private') {
            return true;
        }

        try {
            return Storage::disk($disk)->getVisibility($media->getPathRelativeToRoot()) === 'private';
        } catch (Throwable) {
            return false;
        }
    }

    private function result(mixed $result): ImageDoctorResult
    {
        if ($result instanceof ImageDoctorResult) {
            return $result;
        }

        if (is_array($result)) {
            $successful = $result['successful'] ?? $result['success'] ?? true;
            $message = $result['message'] ?? null;
            $altText = $result['alt_text'] ?? $result['altText'] ?? null;
            $caption = $result['caption'] ?? null;

            return new ImageDoctorResult(
                successful: (bool) $successful,
                message: is_string($message) ? $message : null,
                altText: is_string($altText) ? $altText : null,
                caption: is_string($caption) ? $caption : null,
            );
        }

        if (is_string($result) && $result !== '') {
            return ImageDoctorResult::success($result);
        }

        return ImageDoctorResult::success(__('capell-media-ai::media-ai.success'));
    }
}
