<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class StoreLiveChatAttachmentsAction
{
    use AsAction;

    /**
     * @param  list<UploadedFile>  $files
     * @return list<array{name: string, disk: string, path: string, mime: string|null, size: int}>
     */
    public function handle(array $files, string $conversationUuid): array
    {
        $disk = $this->disk();
        $attachments = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
            $path = sprintf(
                'live-chat/%s/%s.%s',
                $conversationUuid,
                (string) Str::uuid(),
                $extension,
            );

            $contents = file_get_contents($file->getRealPath());

            if (! is_string($contents)) {
                continue;
            }

            Storage::disk($disk)->put($path, $contents);

            $attachments[] = [
                'name' => $file->getClientOriginalName(),
                'disk' => $disk,
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $attachments;
    }

    private function disk(): string
    {
        $disk = config('capell-live-chat.attachments.disk');

        return is_string($disk) && $disk !== '' ? $disk : 'local';
    }
}
