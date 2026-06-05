<?php

declare(strict_types=1);

namespace Capell\Inertia\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Symfony\Component\HttpFoundation\Response render(string $component, array<string, mixed> $props = [], ?int $status = null)
 */
class CapellInertia extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'capell-inertia';
    }
}
