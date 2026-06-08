<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Media as CapellMedia;
use Capell\Core\Models\Page;
use Capell\MediaAI\Actions\ApplyImageDoctorMetadataAction;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Capell\MediaAI\Data\ImageDoctorResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('writes generated alt text and captions to localized media metadata', function (): void {
    Storage::fake('public');
    config()->set('capell.media.model', CapellMedia::class);
    config()->set('media-library.media_model', CapellMedia::class);

    $language = Language::query()->create([
        'name' => 'English',
        'code' => 'en',
        'default' => true,
        'status' => true,
    ]);

    $media = Page::factory()
        ->create()
        ->addMedia(UploadedFile::fake()->image('doctor.jpg', 120, 80))
        ->toMediaCollection('default', 'public');

    $updated = ApplyImageDoctorMetadataAction::run(
        $media,
        new ImageDoctorRequest(
            operation: 'improve',
            instructions: 'Generate metadata.',
            locale: 'en',
        ),
        new ImageDoctorResult(
            successful: true,
            message: 'Metadata generated.',
            altText: 'A product image on a clean background.',
            caption: 'Product photography prepared for the catalogue.',
        ),
    );

    $translation = $media->translations()->where('language_id', $language->getKey())->first();

    expect($updated)->toBeTrue()
        ->and($translation?->meta['alt'])->toBe('A product image on a clean background.')
        ->and($translation?->meta['caption'])->toBe('Product photography prepared for the catalogue.');
});
