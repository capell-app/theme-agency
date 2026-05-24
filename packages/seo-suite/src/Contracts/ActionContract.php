<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Contracts;

interface ActionContract
{
    public function handle(mixed ...$args): mixed;

    /**
     * @param  array<array-key, mixed>  $input
     */
    public function validate(array $input): bool;
}
