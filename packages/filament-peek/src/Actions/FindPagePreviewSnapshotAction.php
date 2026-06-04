<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Actions;

use Capell\FilamentPeek\Concerns\ResolvesPreviewContext;
use Capell\FilamentPeek\Data\PagePreviewSnapshotData;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

final class FindPagePreviewSnapshotAction
{
    use AsAction;
    use ResolvesPreviewContext;

    public function handle(string $token): ?PagePreviewSnapshotData
    {
        $payload = Cache::store($this->previewCacheStore())->get($this->snapshotCacheKey($token));

        if (! is_array($payload)) {
            return null;
        }

        return PagePreviewSnapshotData::from($payload);
    }
}
