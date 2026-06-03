<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Tests\Fixtures;

use Capell\Core\Models\Page;
use Capell\FoundationTheme\Livewire\Assets\Table\PageAssets;
use Illuminate\Database\Eloquent\Builder;

final class FoundationThemeFinalPageAssetsHarness extends PageAssets
{
    /**
     * @return Builder<Page>
     */
    public function exposeTableQuery(): Builder
    {
        return $this->getTableQuery();
    }

    public function exposeShouldPersistTableFiltersInSession(): bool
    {
        return $this->shouldPersistTableFiltersInSession();
    }
}
