<?php

declare(strict_types=1);

namespace Capell\MediaAI\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Media;
use Capell\Core\Models\Translation;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Capell\MediaAI\Data\ImageDoctorResult;
use Lorisleiva\Actions\Concerns\AsAction;

final class ApplyImageDoctorMetadataAction
{
    use AsAction;

    public function handle(Media $media, ImageDoctorRequest $request, ImageDoctorResult $result): bool
    {
        if ($result->altText === null && $result->caption === null) {
            return false;
        }

        $language = $this->language($request->locale);

        if (! $language instanceof Language) {
            return false;
        }

        $translation = $media->translations()->firstOrNew([
            'language_id' => $language->getKey(),
        ]);

        if (! $translation instanceof Translation) {
            return false;
        }

        $meta = is_array($translation->meta) ? $translation->meta : [];

        if ($result->altText !== null) {
            $meta['alt'] = $result->altText;
        }

        if ($result->caption !== null) {
            $meta['caption'] = $result->caption;
        }

        $translation->forceFill([
            'language_id' => $language->getKey(),
            'meta' => $meta,
        ])->save();

        return true;
    }

    private function language(?string $locale): ?Language
    {
        $code = $locale !== null && $locale !== '' ? $locale : app()->getLocale();

        return Language::query()
            ->where('code', $code)
            ->first()
            ?? Language::query()->default()->ordered()->first()
            ?? Language::query()->ordered()->first();
    }
}
