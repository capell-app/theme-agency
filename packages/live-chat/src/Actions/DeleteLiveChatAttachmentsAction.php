<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

final class DeleteLiveChatAttachmentsAction
{
    use AsAction;

    /**
     * @param  list<array{name?: string, disk?: string, path?: string, url?: string, mime?: string|null, size?: int|null}>  $attachments
     */
    public function handle(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $disk = $attachment['disk'] ?? null;
            $path = $attachment['path'] ?? null;

            if (! is_string($disk) || $disk === '' || ! is_string($path) || $path === '') {
                continue;
            }

            Storage::disk($disk)->delete($path);
        }
    }
}
