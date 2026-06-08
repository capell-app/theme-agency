<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Contracts;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Layout;
use Illuminate\Contracts\Auth\Authenticatable;

interface StoresLayoutBuilderPreviewState
{
    /**
     * @param  array<string, mixed>|null  $containers
     * @param  array<string, mixed>  $assets
     */
    public function handle(
        Pageable $page,
        Layout $layout,
        ?array $containers,
        array $assets = [],
    ): void;

    public function clear(Pageable $page, ?Authenticatable $user = null): void;
}
