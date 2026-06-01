<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Jobs;

use Capell\FrontendOptimizer\Actions\GenerateCriticalCssAction;
use Capell\FrontendOptimizer\Enums\OptimizationStatus;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateCriticalCssJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $renderProfileId,
        public string $url,
    ) {}

    public function handle(GenerateCriticalCssAction $action): void
    {
        $profile = FrontendRenderProfile::query()->findOrFail($this->renderProfileId);

        if ($this->hasGeneratedCriticalCss($profile)) {
            return;
        }

        $action->handle($profile, $this->url);
    }

    /**
     * @return array<int, WithoutOverlapping>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('capell-frontend-optimizer:critical-css:' . $this->renderProfileId))
                ->expireAfter(900),
        ];
    }

    private function hasGeneratedCriticalCss(FrontendRenderProfile $profile): bool
    {
        if ($profile->status !== OptimizationStatus::Generated->value) {
            return false;
        }

        if (! is_string($profile->critical_css_path) || $profile->critical_css_path === '') {
            return false;
        }

        return Storage::disk('local')->exists($profile->critical_css_path);
    }
}
