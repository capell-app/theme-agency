<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Concerns;

use Capell\Admin\Support\SiteScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait ScopesNewsletterResourcesToAssignedSites
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected static function applyNewsletterSiteScope(Builder $query, string $column = 'site_id'): Builder
    {
        return SiteScope::applyForCurrentActor($query, $column);
    }
}
