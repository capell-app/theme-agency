<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Spatie\LaravelData\Data;

final class SeoSuiteDoctorCheckData extends Data
{
    public function __construct(
        public string $area,
        public string $status,
        public string $message,
        public ?string $detail = null,
    ) {}
}
