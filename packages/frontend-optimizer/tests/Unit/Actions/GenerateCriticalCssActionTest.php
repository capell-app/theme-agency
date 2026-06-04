<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Actions\GenerateCriticalCssAction;
use Capell\FrontendOptimizer\Actions\InvalidateGeneratedCriticalCssCacheAction;
use Capell\FrontendOptimizer\Contracts\CriticalCssGenerator;
use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Capell\FrontendOptimizer\Enums\OptimizationStatus;
use Capell\FrontendOptimizer\Jobs\GenerateCriticalCssJob;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Storage;

it('records critical css generation runs for successful and failed profile updates', function (): void {
    $profile = FrontendRenderProfile::query()->create([
        'hash' => 'profile-success',
        'scope' => OptimizationScope::Layout->value,
        'label' => 'Landing',
        'signature' => ['assets' => []],
        'status' => OptimizationStatus::Pending->value,
    ]);
    $generator = new class implements CriticalCssGenerator
    {
        public function generate(FrontendRenderProfile $profile, string $url): string
        {
            expect($profile->hash)->toBe('profile-success')
                ->and($url)->toBe('https://example.test/landing');

            return 'capell/frontend-optimizer/critical-css/profile-success.css';
        }
    };

    $cacheInvalidator = new class extends InvalidateGeneratedCriticalCssCacheAction
    {
        /** @var list<string> */
        public array $invalidatedProfileHashes = [];

        public function handle(FrontendRenderProfile $profile): void
        {
            $this->invalidatedProfileHashes[] = $profile->hash;
        }
    };

    $path = (new GenerateCriticalCssAction($generator, $cacheInvalidator))->handle($profile, 'https://example.test/landing');

    $profile->refresh();
    $run = $profile->runs()->firstOrFail();

    expect($path)->toBe('capell/frontend-optimizer/critical-css/profile-success.css')
        ->and($profile->critical_css_path)->toBe($path)
        ->and($profile->status)->toBe(OptimizationStatus::Generated->value)
        ->and($profile->generated_at)->not->toBeNull()
        ->and($run->status)->toBe(OptimizationStatus::Generated->value)
        ->and($run->message)->toBeNull()
        ->and($run->started_at)->not->toBeNull()
        ->and($run->finished_at)->not->toBeNull()
        ->and($cacheInvalidator->invalidatedProfileHashes)->toBe(['profile-success']);

    $failedProfile = FrontendRenderProfile::query()->create([
        'hash' => 'profile-failure',
        'scope' => OptimizationScope::Layout->value,
        'label' => 'Broken',
        'signature' => ['assets' => []],
        'status' => OptimizationStatus::Pending->value,
    ]);
    $failingGenerator = new class implements CriticalCssGenerator
    {
        public function generate(FrontendRenderProfile $profile, string $url): string
        {
            unset($profile, $url);

            throw new RuntimeException('Renderer failed');
        }
    };

    expect(fn (): string => (new GenerateCriticalCssAction($failingGenerator, $cacheInvalidator))->handle($failedProfile, 'https://example.test/broken'))
        ->toThrow(RuntimeException::class, 'Renderer failed');

    $failedProfile->refresh();
    $failedRun = $failedProfile->runs()->firstOrFail();

    expect($failedProfile->status)->toBe(OptimizationStatus::Failed->value)
        ->and($failedRun->status)->toBe(OptimizationStatus::Failed->value)
        ->and($failedRun->message)->toBe('Renderer failed')
        ->and($failedRun->finished_at)->not->toBeNull()
        ->and($cacheInvalidator->invalidatedProfileHashes)->toBe(['profile-success']);
});

it('does not rerun a critical css job for a profile that already has generated css', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('capell/frontend-optimizer/critical-css/profile-generated.css', 'body{color:#111}');

    $profile = FrontendRenderProfile::query()->create([
        'hash' => 'profile-generated',
        'scope' => OptimizationScope::Layout->value,
        'label' => 'Generated',
        'signature' => ['assets' => []],
        'critical_css_path' => 'capell/frontend-optimizer/critical-css/profile-generated.css',
        'status' => OptimizationStatus::Generated->value,
    ]);

    $generator = new class implements CriticalCssGenerator
    {
        public function generate(FrontendRenderProfile $profile, string $url): string
        {
            unset($profile, $url);

            throw new RuntimeException('Generator should not run.');
        }
    };

    (new GenerateCriticalCssJob((int) $profile->getKey(), 'https://example.test/generated'))
        ->handle(new GenerateCriticalCssAction($generator, new InvalidateGeneratedCriticalCssCacheAction));

    expect($profile->runs()->count())->toBe(0);
});

it('serializes critical css jobs by render profile', function (): void {
    $middleware = (new GenerateCriticalCssJob(42, 'https://example.test'))->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(WithoutOverlapping::class);
});
