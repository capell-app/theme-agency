<?php

declare(strict_types=1);

namespace Capell\MediaAI\Filament;

use Capell\Admin\Contracts\Extenders\MediaEditActionExtender;
use Capell\Admin\Filament\Resources\Media\Pages\EditMedia;
use Capell\Core\Models\Media;
use Capell\MediaAI\Contracts\ImageDoctor;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Capell\MediaAI\Jobs\RunImageDoctorJob;
use Capell\MediaAI\Support\NullImageDoctor;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;

final class MediaAIEditActionExtender implements MediaEditActionExtender
{
    public function getHeaderActions(EditMedia $page): array
    {
        return [
            Action::make('doctor-image')
                ->label(__('capell-media-ai::media-ai.doctor_image'))
                ->icon('heroicon-o-sparkles')
                ->color('gray')
                ->modalDescription(__('capell-media-ai::media-ai.doctor_image_description'))
                ->schema([
                    Select::make('operation')
                        ->label(__('capell-media-ai::media-ai.operation'))
                        ->options(__('capell-media-ai::media-ai.operations'))
                        ->default('improve')
                        ->required()
                        ->in(ImageDoctorRequest::OPERATIONS),
                    Textarea::make('instructions')
                        ->label(__('capell-media-ai::media-ai.instructions'))
                        ->placeholder(__('capell-media-ai::media-ai.instructions_placeholder'))
                        ->rows(4)
                        ->required(),
                ])
                ->authorize(fn (Media $record): bool => Gate::allows('update', $record))
                ->visible(fn (Media $record): bool => $record->isImage() && ! resolve(ImageDoctor::class) instanceof NullImageDoctor)
                ->action(function (Media $record, array $data): void {
                    Gate::authorize('update', $record);

                    $user = auth()->user();
                    $rateLimitKey = $this->rateLimitKey($user instanceof Model ? $user : null, $record);
                    $maxAttempts = $this->maxAttempts();

                    if ($maxAttempts > 0 && RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
                        Notification::make('capell_media_ai_image_doctor_rate_limited')
                            ->title(__('capell-media-ai::media-ai.rate_limited', [
                                'seconds' => RateLimiter::availableIn($rateLimitKey),
                            ]))
                            ->warning()
                            ->send();

                        return;
                    }

                    if ($maxAttempts > 0) {
                        RateLimiter::hit($rateLimitKey, $this->decaySeconds());
                    }

                    dispatch(new RunImageDoctorJob(mediaId: $this->mediaId($record), operation: $this->dataString($data, 'operation', 'improve'), instructions: $this->dataString($data, 'instructions'), locale: app()->getLocale(), budgetCents: $this->budgetCents(), model: $this->model(), notifiableClass: $user instanceof Model ? $user::class : null, notifiableKey: $this->modelKey($user instanceof Model ? $user : null)));

                    Notification::make('capell_media_ai_image_doctor_queued')
                        ->title(__('capell-media-ai::media-ai.queued'))
                        ->success()
                        ->send();
                }),
        ];
    }

    private function rateLimitKey(?Model $user, Media $media): string
    {
        return sprintf(
            'capell-media-ai:image-doctor:%s:%s',
            $user instanceof Model ? $user::class . ':' . $this->stringValue($this->modelKey($user)) : 'guest',
            $this->stringValue($this->modelKey($media)),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function dataString(array $data, string $key, string $fallback = ''): string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : $fallback;
    }

    private function mediaId(Media $media): int
    {
        $key = $media->getKey();

        if (is_int($key)) {
            return $key;
        }

        return is_string($key) && ctype_digit($key) ? (int) $key : 0;
    }

    private function modelKey(?Model $model): int|string|null
    {
        if (! $model instanceof Model) {
            return null;
        }

        $key = $model->getKey();

        return is_int($key) || is_string($key) ? $key : null;
    }

    private function stringValue(int|string|null $value): string
    {
        return $value === null ? '' : (string) $value;
    }

    private function maxAttempts(): int
    {
        $maxAttempts = config('capell-media-ai.image_doctor.rate_limit.max_attempts', 10);

        return is_numeric($maxAttempts) ? max(0, (int) $maxAttempts) : 10;
    }

    private function decaySeconds(): int
    {
        $decaySeconds = config('capell-media-ai.image_doctor.rate_limit.decay_seconds', 3600);

        return is_numeric($decaySeconds) ? max(1, (int) $decaySeconds) : 3600;
    }

    private function budgetCents(): ?int
    {
        $budgetCents = config('capell-media-ai.image_doctor.budget_cents');

        return is_numeric($budgetCents) ? max(0, (int) $budgetCents) : null;
    }

    private function model(): ?string
    {
        $model = config('capell-media-ai.image_doctor.model');

        return is_string($model) && $model !== '' ? $model : null;
    }
}
