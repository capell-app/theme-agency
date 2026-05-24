<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Core\Contracts\Pageable;
use Capell\Frontend\Support\Cache\PageCacheInvalidator;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

class InvalidateCommentableCacheAction
{
    use AsAction;

    public function handle(?Model $commentable): void
    {
        if (! $commentable instanceof Pageable || ! app()->bound(PageCacheInvalidator::class)) {
            return;
        }

        resolve(PageCacheInvalidator::class)->onSaved($commentable);
    }
}
