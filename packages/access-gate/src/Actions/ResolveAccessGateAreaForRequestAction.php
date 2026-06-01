<?php

declare(strict_types=1);

namespace Capell\AccessGate\Actions;

use Capell\AccessGate\Models\Area;
use Capell\Core\Actions\LoadSiteDomainFromUrlAction;
use Capell\Core\Models\SiteDomain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Area run(Request $request, string $areaKey)
 */
final class ResolveAccessGateAreaForRequestAction
{
    use AsAction;

    public function handle(Request $request, string $areaKey): Area
    {
        $siteScopeEnabled = $this->siteScopeEnabled();
        $siteId = $siteScopeEnabled ? $this->siteId($request) : null;

        return Area::query()
            ->where('key', $areaKey)
            ->when($siteScopeEnabled, function (Builder $query) use ($siteId): void {
                $query->where(function (Builder $query) use ($siteId): void {
                    $query->whereNull('site_id');

                    if ($siteId !== null) {
                        $query->orWhere('site_id', $siteId);
                    }
                });
            })
            ->orderByRaw('site_id is null')
            ->firstOrFail();
    }

    private function siteScopeEnabled(): bool
    {
        return Schema::hasColumn((new Area)->getTable(), 'site_id');
    }

    private function siteId(Request $request): ?int
    {
        if (! Schema::hasTable('sites') || ! Schema::hasTable('site_domains')) {
            return null;
        }

        $resolved = LoadSiteDomainFromUrlAction::run($request->fullUrl());
        $siteDomain = is_array($resolved) ? ($resolved[0] ?? null) : null;

        return $siteDomain instanceof SiteDomain ? $siteDomain->site_id : null;
    }
}
