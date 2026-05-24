<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

class CreateCommentData extends Data
{
    public function __construct(
        public Model $commentable,
        public string $body,
        public ?int $siteId = null,
        public ?int $languageId = null,
        public ?string $authorName = null,
        public ?string $authorEmail = null,
        public ?Model $user = null,
        public ?string $parentPublicId = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public ?string $url = null,
    ) {}
}
