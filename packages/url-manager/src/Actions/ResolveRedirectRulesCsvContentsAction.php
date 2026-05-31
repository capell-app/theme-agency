<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveRedirectRulesCsvContentsAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, int $maxKilobytes = 2048): string
    {
        $inlineContents = $data['csv_contents'] ?? null;

        if (is_string($inlineContents) && trim($inlineContents) !== '') {
            $this->ensureAllowedCsvSize(strlen($inlineContents), $maxKilobytes);

            return $inlineContents;
        }

        $path = $data['csv'] ?? null;

        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            return '';
        }

        $this->ensureAllowedCsvSize(Storage::disk('local')->size($path), $maxKilobytes);

        $contents = Storage::disk('local')->get($path);

        return is_string($contents) ? $contents : '';
    }

    private function ensureAllowedCsvSize(int $bytes, int $maxKilobytes): void
    {
        if ($maxKilobytes <= 0 || $bytes <= $maxKilobytes * 1024) {
            return;
        }

        throw ValidationException::withMessages([
            'csv' => [__('capell-url-manager::validation.import_file_too_large', ['max' => $maxKilobytes])],
        ]);
    }
}
