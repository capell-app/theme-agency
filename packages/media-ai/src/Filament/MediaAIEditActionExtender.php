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

                    RunImageDoctorJob::dispatch(
                        mediaId: (int) $record->getKey(),
                        operation: (string) $data['operation'],
                        instructions: (string) $data['instructions'],
                        locale: app()->getLocale(),
                        notifiableClass: $user instanceof Model ? $user::class : null,
                        notifiableKey: $user instanceof Model ? $user->getKey() : null,
                    );

                    Notification::make('capell_media_ai_image_doctor_queued')
                        ->title(__('capell-media-ai::media-ai.queued'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
