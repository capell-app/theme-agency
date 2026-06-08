<?php

declare(strict_types=1);

namespace Capell\MediaAI\Actions;

use Capell\Core\Models\Media;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Capell\MediaAI\Jobs\RunImageDoctorJob;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class QueueBatchImageDoctorRequestsAction
{
    use AsAction;

    public function handle(
        string $operation = 'improve',
        string $instructions = '',
        ?string $locale = null,
        ?int $limit = null,
        bool $missingAltOnly = true,
        ?int $budgetCents = null,
        ?string $model = null,
        ?string $notifiableClass = null,
        int|string|null $notifiableKey = null,
    ): int {
        new ImageDoctorRequest(
            operation: $operation,
            instructions: $instructions,
            locale: $locale,
            budgetCents: $budgetCents,
            model: $model,
        );

        $queued = 0;
        $boundedLimit = max(1, $limit ?? $this->defaultLimit());

        $this->query($missingAltOnly, $locale)
            ->limit($boundedLimit)
            ->get(['id'])
            ->each(function (Media $media) use (
                &$queued,
                $operation,
                $instructions,
                $locale,
                $budgetCents,
                $model,
                $notifiableClass,
                $notifiableKey,
            ): void {
                dispatch(new RunImageDoctorJob(mediaId: (int) $media->getKey(), operation: $operation, instructions: $instructions, locale: $locale, budgetCents: $budgetCents, model: $model, notifiableClass: $notifiableClass, notifiableKey: $notifiableKey));

                $queued++;
            });

        return $queued;
    }

    /**
     * @return Builder<Media>
     */
    private function query(bool $missingAltOnly, ?string $locale): Builder
    {
        return Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->when($missingAltOnly, fn (Builder $query): Builder => $this->missingAltQuery($query, $locale))
            ->oldest('id');
    }

    /**
     * @param  Builder<Media>  $query
     * @return Builder<Media>
     */
    private function missingAltQuery(Builder $query, ?string $locale): Builder
    {
        $languageCode = $locale !== null && $locale !== '' ? $locale : app()->getLocale();

        return $query->whereDoesntHave('translations', function (Builder $query) use ($languageCode): void {
            $query
                ->whereHas('language', fn (Builder $query): Builder => $query->where('code', $languageCode))
                ->whereNotNull('meta->alt')
                ->where('meta->alt', '!=', '');
        });
    }

    private function defaultLimit(): int
    {
        $limit = config('capell-media-ai.image_doctor.batch.limit', 50);

        return is_numeric($limit) ? max(1, (int) $limit) : 50;
    }
}
