<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Data;

use Spatie\LaravelData\Data;

class EmailAttachmentData extends Data
{
    public function __construct(
        public string $disk,
        public string $path,
        public string $name,
        public string $mime,
    ) {}
}
