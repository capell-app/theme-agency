<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Extenders\MediaEditActionExtender;
use Capell\Admin\Filament\Resources\Media\Pages\EditMedia;
use Capell\AIOrchestrator\Actions\RegisterAIOrchestratorModuleAction;
use Capell\AIOrchestrator\Data\AIOrchestratorRunData;
use Capell\AIOrchestrator\Support\AIOrchestratorModuleRegistry;
use Capell\Core\Models\Media as CapellMedia;
use Capell\Core\Models\Page;
use Capell\MediaAI\Contracts\ImageDoctor;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Capell\MediaAI\Data\ImageDoctorResult;
use Capell\MediaAI\Jobs\RunImageDoctorJob;
use Capell\MediaAI\Filament\MediaAIEditActionExtender;
use Capell\MediaAI\Providers\MediaAIServiceProvider;
use Capell\MediaAI\Support\AIOrchestratorImageDoctor;
use Capell\MediaAI\Support\NullImageDoctor;
use Capell\MediaAI\Tests\Fixtures\AIOrchestratorImageDoctorAction;
use Capell\MediaAI\Tests\Fixtures\AIOrchestratorImageDoctorModule;
use Capell\MediaAI\Tests\Fixtures\RecordingImageDoctor;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    if (
        ! interface_exists(MediaEditActionExtender::class)
        || ! class_exists(EditMedia::class)
    ) {
        test()->markTestSkipped('Capell Admin media edit extension points are not available in this checkout.');
    }

    test()->actingAs(createMediaAIGlobalUser());

    Queue::fake();
    Storage::fake('public');
    config()->set('capell-media-ai.enabled', true);
    config()->set('capell.media.model', CapellMedia::class);
    config()->set('media-library.media_model', CapellMedia::class);
});

function createMediaAIGlobalUser(): Authenticatable
{
    $user = new class extends Authenticatable implements FilamentUser
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        protected $table = 'users';

        public function canAccessPanel(Panel $panel): bool
        {
            return true;
        }

        public function isGlobalAdmin(): bool
        {
            return true;
        }

        public function hasRole(string $role): bool
        {
            return true;
        }

        /** @return Collection<int, int> */
        public function getAssignedSiteIds(): Collection
        {
            return collect();
        }
    };

    $user->forceFill([
        'name' => 'Media AIOrchestrator Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password'),
    ])->save();

    Relation::morphMap(['test-media-ai-admin-user' => $user::class], merge: true);

    return $user;
}

function createMediaAIReadOnlyUser(): Authenticatable
{
    $user = new class extends Authenticatable implements FilamentUser
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        protected $table = 'users';

        public function canAccessPanel(Panel $panel): bool
        {
            return true;
        }

        public function isGlobalAdmin(): bool
        {
            return false;
        }

        public function hasRole(string $role): bool
        {
            return false;
        }

        public function checkPermissionTo(string $permission): bool
        {
            return false;
        }

        /** @return Collection<int, int> */
        public function getAssignedSiteIds(): Collection
        {
            return collect();
        }
    };

    $user->forceFill([
        'name' => 'Media AIOrchestrator Read Only',
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password'),
    ])->save();

    Relation::morphMap(['test-media-ai-read-only-user' => $user::class], merge: true);

    return $user;
}

function createMediaAIImage(): CapellMedia
{
    $page = Page::factory()->create();

    /** @var CapellMedia $media */
    $media = $page
        ->addMedia(UploadedFile::fake()->image('ai-orchestrator.jpg', 32, 32))
        ->toMediaCollection('image');

    return $media;
}

it('registers a media edit action extender when enabled', function (): void {
    expect(collect(app()->tagged(MediaEditActionExtender::TAG))
        ->contains(fn (object $extender): bool => $extender instanceof MediaAIEditActionExtender))
        ->toBeTrue();
});

it('does not register a media edit action extender when disabled', function (): void {
    $registeredBefore = collect(app()->tagged(MediaEditActionExtender::TAG))
        ->filter(fn (object $extender): bool => $extender instanceof MediaAIEditActionExtender)
        ->count();

    config()->set('capell-media-ai.enabled', false);

    (new MediaAIServiceProvider(app()))->registeringPackage();

    expect(collect(app()->tagged(MediaEditActionExtender::TAG))
        ->filter(fn (object $extender): bool => $extender instanceof MediaAIEditActionExtender)
        ->count())
        ->toBe($registeredBefore);
});

it('keeps the doctor action hidden until an ai-orchestrator-backed image doctor is bound', function (): void {
    expect(resolve(ImageDoctor::class))->toBeInstanceOf(NullImageDoctor::class);

    Livewire::test(EditMedia::class, [
        'record' => createMediaAIImage()->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertActionHidden('doctor-image');
});

it('returns a localized failure result from the null image doctor', function (): void {
    $result = (new NullImageDoctor)->doctor(
        createMediaAIImage(),
        new ImageDoctorRequest(
            operation: 'improve',
            instructions: 'Try the default image doctor.',
        ),
    );

    expect($result->successful)->toBeFalse()
        ->and($result->message)->toBe(__('capell-media-ai::media-ai.not_configured'));
});

it('queues image doctor requests for the configured ai-orchestrator implementation', function (): void {
    app()->setLocale('cy');
    Queue::fake();
    config()->set('capell-media-ai.image_doctor.budget_cents', 250);
    config()->set('capell-media-ai.image_doctor.model', 'image-editor');

    $doctor = new RecordingImageDoctor;
    app()->instance(ImageDoctor::class, $doctor);

    $media = createMediaAIImage();

    Livewire::test(EditMedia::class, [
        'record' => $media->getRouteKey(),
    ])
        ->assertSuccessful()
        ->callAction('doctor-image', [
            'operation' => 'remove_background',
            'instructions' => 'Remove the background and keep the subject sharp.',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    Queue::assertPushed(RunImageDoctorJob::class, fn (RunImageDoctorJob $job): bool => $job->mediaId === $media->getKey()
        && $job->operation === 'remove_background'
        && $job->instructions === 'Remove the background and keep the subject sharp.'
        && $job->locale === 'cy'
        && $job->budgetCents === 250
        && $job->model === 'image-editor');

    expect($doctor->media)->toBeNull()
        ->and($doctor->request)->toBeNull();
});
it('runs image doctor requests through the configured ai-orchestrator capability', function (): void {
    if (! class_exists(AIOrchestratorModuleRegistry::class)) {
        test()->markTestSkipped('AI Orchestrator is not available in this checkout.');
    }

    app()->singleton(AIOrchestratorModuleRegistry::class, fn (): AIOrchestratorModuleRegistry => new AIOrchestratorModuleRegistry);
    RegisterAIOrchestratorModuleAction::run(new AIOrchestratorImageDoctorModule);
    AIOrchestratorImageDoctorAction::$lastRun = null;

    $media = createMediaAIImage();
    $result = (new AIOrchestratorImageDoctor)->doctor(
        $media,
        new ImageDoctorRequest(
            operation: 'restore',
            instructions: 'Restore scratches while preserving the original crop.',
            locale: 'fr',
            budgetCents: 300,
            model: 'restore-model',
        ),
    );
    $lastRun = AIOrchestratorImageDoctorAction::lastRun();

    expect($result->successful)->toBeTrue()
        ->and($result->message)->toBe('Doctor finished through AI Orchestrator')
        ->and($lastRun)->toBeInstanceOf(AIOrchestratorRunData::class);
    throw_unless($lastRun instanceof AIOrchestratorRunData, RuntimeException::class, 'Expected AI Orchestrator run data.');
    $mediaContext = $lastRun->context['media'] ?? null;
    throw_unless(is_array($mediaContext), RuntimeException::class, 'Expected AI Orchestrator media context.');

    expect($lastRun->moduleKey)->toBe('media-ai')
        ->and($lastRun->capabilityKey)->toBe('doctor-image')
        ->and($lastRun->context['operation'])->toBe('restore')
        ->and($lastRun->context['instructions'])->toBe('Restore scratches while preserving the original crop.')
        ->and($lastRun->context['locale'])->toBe('fr')
        ->and($lastRun->context['budget_cents'])->toBe(300)
        ->and($lastRun->context['model'])->toBe('restore-model')
        ->and($mediaContext['id'])->toBe($media->getKey());
});

it('rejects crafted image doctor operations before calling the provider', function (): void {
    $doctor = new RecordingImageDoctor;
    app()->instance(ImageDoctor::class, $doctor);

    Livewire::test(EditMedia::class, [
        'record' => createMediaAIImage()->getRouteKey(),
    ])
        ->assertSuccessful()
        ->callAction('doctor-image', [
            'operation' => 'delete_everything',
            'instructions' => 'Crafted payload.',
        ])
        ->assertHasActionErrors(['operation']);

    expect($doctor->media)->toBeNull()
        ->and($doctor->request)->toBeNull();
});

it('shows warning notifications when the image doctor reports a null-provider failure', function (): void {
    $doctor = new RecordingImageDoctor(ImageDoctorResult::failure(__('capell-media-ai::media-ai.not_configured')));
    app()->instance(ImageDoctor::class, $doctor);

    Livewire::test(EditMedia::class, [
        'record' => createMediaAIImage()->getRouteKey(),
    ])
        ->assertSuccessful()
        ->callAction('doctor-image', [
            'operation' => 'improve',
            'instructions' => 'Try the configured image doctor.',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified(
            Notification::make()
                ->title(__('capell-media-ai::media-ai.not_configured'))
                ->warning(),
        );
});

it('authorizes doctor requests against the media update policy', function (): void {
    test()->actingAs(createMediaAIReadOnlyUser());

    $action = (new MediaAIEditActionExtender)->getHeaderActions(new EditMedia)[0];

    expect($action->record(createMediaAIImage())->isAuthorized())->toBeFalse();
});
