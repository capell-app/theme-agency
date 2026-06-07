<?php

declare(strict_types=1);

namespace Capell\MediaAI\Jobs;

use Capell\Core\Models\Media;
use Capell\MediaAI\Contracts\ImageDoctor;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;

final class RunImageDoctorJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public readonly int $mediaId,
        public readonly string $operation,
        public readonly string $instructions,
        public readonly ?string $locale,
        public readonly ?string $notifiableClass,
        public readonly int|string|null $notifiableKey,
    ) {}

    public function handle(): void
    {
        $media = Media::query()->find($this->mediaId);

        if (! $media instanceof Media) {
            return;
        }

        $result = resolve(ImageDoctor::class)->doctor(
            $media,
            new ImageDoctorRequest(
                operation: $this->operation,
                instructions: $this->instructions,
                locale: $this->locale,
            ),
        );

        $notification = Notification::make('capell_media_ai_image_doctor_completed')
            ->title($result->message ?? __('capell-media-ai::media-ai.success'));

        $result->successful
            ? $notification->success()
            : $notification->warning();

        $notifiable = $this->notifiable();

        $notifiable instanceof Model
            ? $notification->sendToDatabase($notifiable)
            : $notification->send();
    }

    private function notifiable(): ?Model
    {
        if ($this->notifiableClass === null || $this->notifiableKey === null || ! is_subclass_of($this->notifiableClass, Model::class)) {
            return null;
        }

        return $this->notifiableClass::query()->find($this->notifiableKey);
    }
}
