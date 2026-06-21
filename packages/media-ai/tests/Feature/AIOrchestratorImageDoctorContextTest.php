<?php

declare(strict_types=1);

use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Capell\MediaAI\Support\AIOrchestratorImageDoctor;
use Illuminate\Support\Facades\Storage;

/**
 * Invoke the private context() builder via reflection. The method is the unit
 * under test for the M6 data-exposure fix; the surrounding doctor() entrypoint
 * short-circuits when the AI orchestrator package is absent, so the smallest
 * meaningful assertion targets context() directly.
 *
 * @return array<string, mixed>
 */
function buildImageDoctorContext(Media $media): array
{
    $doctor = new AIOrchestratorImageDoctor;

    $reflection = new ReflectionMethod($doctor, 'context');

    $context = $reflection->invoke(
        $doctor,
        $media,
        new ImageDoctorRequest(
            operation: 'improve',
            instructions: 'Tidy up the image.',
        ),
    );

    expect($context)->toBeArray();
    throw_unless(is_array($context), RuntimeException::class, 'Expected image doctor context array.');

    $normalized = [];

    foreach ($context as $key => $value) {
        if (is_string($key)) {
            $normalized[$key] = $value;
        }
    }

    return $normalized;
}

it('never sends raw storage disk or path for any media', function (): void {
    Storage::fake('public');

    $media = Media::factory()->model(Page::factory()->create())->create(['disk' => 'public']);

    $context = buildImageDoctorContext($media);

    expect($context['media'])
        ->not->toHaveKey('disk')
        ->not->toHaveKey('path')
        ->not->toHaveKey('file_name')
        ->toHaveKey('name')
        ->toHaveKey('url');
});

it('hands a short-lived temporary url and no permanent public url for private media', function (): void {
    Storage::fake('private', ['visibility' => 'private']);

    $media = Media::factory()->model(Page::factory()->create())->create(['disk' => 'private', 'conversions_disk' => 'private']);

    Storage::disk('private')->put($media->getPathRelativeToRoot(), 'binary-image-bytes');

    $context = buildImageDoctorContext($media);
    $mediaContext = $context['media'] ?? null;

    throw_unless(is_array($mediaContext), RuntimeException::class, 'Expected media context array.');

    $url = $mediaContext['url'] ?? null;
    $permanentUrl = $media->getFullUrl();

    // Either a temporary/signed URL is issued, or the URL is omitted entirely.
    // In no case may the permanent public URL be handed to the orchestrator.
    if (is_string($url) && $url !== '') {
        expect($url)->not->toBe($permanentUrl);
    } else {
        expect($url)->toBeNull();
    }
});

it('sends a normal full url for public media', function (): void {
    Storage::fake('public');

    $media = Media::factory()->model(Page::factory()->create())->create(['disk' => 'public', 'conversions_disk' => 'public']);

    $context = buildImageDoctorContext($media);
    $mediaContext = $context['media'] ?? null;

    throw_unless(is_array($mediaContext), RuntimeException::class, 'Expected media context array.');

    expect($mediaContext['url'] ?? null)->toBe($media->getFullUrl());
});
