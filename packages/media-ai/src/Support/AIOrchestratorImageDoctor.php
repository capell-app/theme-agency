<?php

declare(strict_types=1);

namespace Capell\MediaAI\Support;

use Capell\AIOrchestrator\Actions\RunAIOrchestratorCapabilityAction;
use Capell\AIOrchestrator\Data\AIOrchestratorRunData;
use Capell\Core\Models\Media;
use Capell\MediaAI\Contracts\ImageDoctor;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Capell\MediaAI\Data\ImageDoctorResult;
use Throwable;

final class AIOrchestratorImageDoctor implements ImageDoctor
{
    private const string RUN_ACTION_CLASS = RunAIOrchestratorCapabilityAction::class;

    private const string RUN_DATA_CLASS = AIOrchestratorRunData::class;

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
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'disk' => $media->disk,
                'size' => $media->size,
                'url' => $media->getFullUrl(),
            ],
        ];
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
