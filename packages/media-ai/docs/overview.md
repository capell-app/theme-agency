# Media AI

The Media AI package adds provider-backed image actions to Capell's existing media resource. It does not replace the media backend, crop system, or localized metadata model.

Status: `Optional` · Tier: `Premium` · Bundle: `media` · Surface: `Admin` · Depends on: `capell-app/admin`, `capell-app/core`

![Media AI Doctor image action surface in the media library](images/screenshots/media-ai-doctor-image.png)

## What It Adds

- A `Doctor image` action on image records in the Media resource.
- A small form for the editor to choose the image operation and add instructions.
- A `Capell\MediaAI\Contracts\ImageDoctor` contract that a provider package can bind to the real image-editing implementation, directly or through AI Orchestrator.
- A safe default `NullImageDoctor`, so installing the package never exposes a broken action before an AI provider is configured.

## Marketplace Positioning

Media AI is a premium Capell Media add-on to the free Media Library workflow. Today it should be sold as the admin-safe provider seam for image operations - improve, remove background, remove object, restore, and upscale - rather than as a full AI media suite. The `media` bundle can become a true bundle once additional paid media packages or a first-party AI Orchestrator image provider ship; until then, pair Media AI with Media Library for the editing surface and AI Orchestrator for provider governance.

## Editor Flow

1. Upload or open an image in Admin > Media.
2. Select `Doctor image`.
3. Choose the operation, such as background removal or cleanup.
4. Add short instructions.
5. Submit the action.

When the action runs, the editor sees a Filament notification carrying the `ImageDoctorResult` message — a success notification when the request succeeds, or a warning when it does not. The result object currently carries only `successful` and an optional `message`; the original media record is never mutated by this package.

## Integration Contract

AIOrchestrator-backed packages should bind the contract in their service provider:

```php
use Capell\MediaAI\Contracts\ImageDoctor;

$this->app->bind(ImageDoctor::class, AIOrchestratorImageDoctor::class);
```

The implementation receives the current media record and an `ImageDoctorRequest` (a validated `operation` plus free-text `instructions`). It returns an `ImageDoctorResult` reporting `successful` and an optional human-readable `message`. The `message` is rendered verbatim in the editor notification, so providers must return a translated, credential-free string.

## Boundaries

- Cropping remains owned by Curator when `capell.media.backend` is `curator`.
- Spatie installs use Capell's fallback focal-point and crop-preset UI.
- Localized alt text, captions, credits, and decorative flags are stored in the shared `translations.meta` JSON column.
