<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Actions;

use Capell\FrontendOptimizer\Data\PruneRenderProfilesResultData;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PruneRenderProfilesResultData run(int $retentionDays = 30, ?int $limit = null, bool $dryRun = false)
 */
final class PruneRenderProfilesAction
{
    use AsAction;

    public function __construct(
        private readonly FilesystemFactory $filesystems,
    ) {}

    public function handle(int $retentionDays = 30, ?int $limit = null, bool $dryRun = false): PruneRenderProfilesResultData
    {
        $retentionDays = max(1, $retentionDays);
        $limit = $limit === null ? null : max(1, $limit);
        $cutoff = CarbonImmutable::now()->subDays($retentionDays);
        $profiles = $this->profilesOlderThan($cutoff, $limit)->get();
        $matchedProfiles = $profiles->count();
        $deletedProfiles = 0;
        $deletedFiles = 0;

        if (! $dryRun) {
            foreach ($profiles as $profile) {
                if (! $profile instanceof FrontendRenderProfile) {
                    continue;
                }

                $deletedFiles += $this->deleteProfileFiles($profile);
                $profile->delete();
                $deletedProfiles++;
            }
        }

        return new PruneRenderProfilesResultData(
            retentionDays: $retentionDays,
            cutoff: $cutoff,
            matchedProfiles: $matchedProfiles,
            deletedProfiles: $deletedProfiles,
            deletedFiles: $deletedFiles,
            dryRun: $dryRun,
        );
    }

    /**
     * @return Builder<FrontendRenderProfile>
     */
    private function profilesOlderThan(CarbonImmutable $cutoff, ?int $limit): Builder
    {
        $query = FrontendRenderProfile::query()
            ->where('updated_at', '<', $cutoff)
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query;
    }

    private function deleteProfileFiles(FrontendRenderProfile $profile): int
    {
        $paths = array_filter([
            $this->manifestPath($profile),
            $profile->critical_css_path,
        ], static fn (?string $path): bool => is_string($path) && $path !== '');

        $deletedFiles = 0;

        foreach (array_unique($paths) as $path) {
            if ($this->filesystems->disk('local')->exists($path)) {
                $this->filesystems->disk('local')->delete($path);
                $deletedFiles++;
            }
        }

        return $deletedFiles;
    }

    private function manifestPath(FrontendRenderProfile $profile): ?string
    {
        $path = $profile->manifest['path'] ?? null;

        return is_string($path) ? $path : null;
    }
}
