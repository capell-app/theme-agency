<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class ValidateLiveChatAttachmentAction
{
    use AsAction;

    public static function disk(): string
    {
        $disk = config('capell-live-chat.attachments.disk');

        return is_string($disk) && $disk !== '' ? $disk : 'local';
    }

    /**
     * @return list<string>
     */
    public static function allowedMimeTypes(): array
    {
        $mimeTypes = config('capell-live-chat.attachments.mime_types', []);

        if (! is_array($mimeTypes)) {
            return self::defaultMimeTypes();
        }

        $configuredMimeTypes = array_values(array_filter(
            $mimeTypes,
            static fn (mixed $mimeType): bool => is_string($mimeType) && $mimeType !== '',
        ));

        return $configuredMimeTypes === [] ? self::defaultMimeTypes() : $configuredMimeTypes;
    }

    public static function maximumKilobytes(): int
    {
        $value = config('capell-live-chat.attachments.max_kilobytes');

        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : 10240;
    }

    public function handle(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'attachments' => __('capell-live-chat::generic.validation.attachments.invalid'),
            ]);
        }

        if (! in_array((string) $file->getMimeType(), self::allowedMimeTypes(), true)) {
            throw ValidationException::withMessages([
                'attachments' => __('capell-live-chat::generic.validation.attachments.mime_type'),
            ]);
        }

        $maximumKilobytes = self::maximumKilobytes();
        $size = $file->getSize();

        if (is_int($size) && $size > ($maximumKilobytes * 1024)) {
            throw ValidationException::withMessages([
                'attachments' => __('capell-live-chat::generic.validation.attachments.max', [
                    'max' => $maximumKilobytes,
                ]),
            ]);
        }

        $this->assertConfiguredDisk(self::disk());
    }

    /**
     * @return list<string>
     */
    private static function defaultMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'application/pdf',
            'text/plain',
            'text/csv',
            'application/csv',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
    }

    private function assertConfiguredDisk(string $disk): void
    {
        $disks = config('filesystems.disks', []);

        if (! is_array($disks) || ! array_key_exists($disk, $disks)) {
            throw ValidationException::withMessages([
                'attachments' => __('capell-live-chat::generic.validation.attachments.disk'),
            ]);
        }

        Storage::disk($disk);
    }
}
