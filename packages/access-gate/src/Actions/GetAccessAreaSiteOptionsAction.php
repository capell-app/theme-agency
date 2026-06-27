<?php

declare(strict_types=1);

namespace Capell\AccessGate\Actions;

use Capell\AccessGate\Support\AccessGateSiteScope;
use Capell\Core\Models\Site;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<int, string> run()
 */
final class GetAccessAreaSiteOptionsAction
{
    use AsAction;

    /**
     * Site options (name keyed by id) for the access-area site selector,
     * scoped to the sites the current admin may assign areas to.
     *
     * @return array<int, string>
     */
    public function handle(): array
    {
        return AccessGateSiteScope::applyAreaOptionsScope(
            Site::query()->select(['name', 'id'])->ordered(),
        )->get()
            ->mapWithKeys(static fn (Site $site): array => [$site->id => $site->name])
            ->all();
    }
}
