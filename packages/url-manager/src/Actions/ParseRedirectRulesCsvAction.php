<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class ParseRedirectRulesCsvAction
{
    use AsAction;

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(string $csv): array
    {
        $stream = fopen('php://temp', 'r+');

        throw_if(
            $stream === false,
            InvalidArgumentException::class,
            __('capell-url-manager::validation.csv_temp_stream_failed'),
        );

        fwrite($stream, $csv);
        rewind($stream);

        $headers = fgetcsv($stream);

        if ($headers === false) {
            fclose($stream);

            return [];
        }

        $normalizedHeaders = array_map(
            static fn (?string $header): string => trim((string) $header),
            $headers,
        );
        $rows = [];

        while (($values = fgetcsv($stream)) !== false) {
            if ($values === [null]) {
                continue;
            }

            $row = [];

            foreach ($normalizedHeaders as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $row[$header] = $values[$index] ?? null;
            }

            $rows[] = $row;
        }

        fclose($stream);

        return $rows;
    }
}
