<?php

declare(strict_types=1);

namespace Capell\AccessGate\Actions;

use Capell\AccessGate\Enums\EventType;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\Event;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;
use RuntimeException;

/**
 * @method static string run(?string $areaKey = null, EventType|string|null $type = null, ?CarbonInterface $from = null, ?CarbonInterface $to = null, ?int $limit = null)
 */
final class ExportAccessGateAuditCsvAction
{
    use AsObject;

    public function handle(
        ?string $areaKey = null,
        EventType|string|null $type = null,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
        ?int $limit = null,
    ): string {
        $query = Event::query()
            ->with(['area', 'registration', 'grant'])
            ->latest('occurred_at')
            ->orderByDesc('id');

        if (is_string($areaKey) && trim($areaKey) !== '') {
            $query->whereHas('area', function (Builder $query) use ($areaKey): void {
                $query->where('key', trim($areaKey));
            });
        }

        if ($type instanceof EventType) {
            $query->where('type', $type->value);
        } elseif (is_string($type) && trim($type) !== '') {
            $query->where('type', trim($type));
        }

        if ($from instanceof CarbonInterface) {
            $query->where('occurred_at', '>=', $from);
        }

        if ($to instanceof CarbonInterface) {
            $query->where('occurred_at', '<=', $to);
        }

        $rows = [[
            'occurred_at',
            'event_id',
            'type',
            'area_id',
            'area_key',
            'area_name',
            'registration_id',
            'registration_email',
            'grant_id',
            'grant_email',
            'claim_token_id',
            'browser_token_id',
            'user_id',
            'subject_type',
            'subject_id',
            'payload',
            'metadata',
        ]];
        $exported = 0;

        $query->chunk(500, function (Collection $events) use (&$rows, &$exported, $limit): false|null {
            foreach ($events as $event) {
                if (! $event instanceof Event) {
                    continue;
                }

                if (is_int($limit) && $limit > 0 && $exported >= $limit) {
                    return false;
                }

                $area = $event->area;

                $rows[] = [
                    $event->occurred_at->toIso8601String(),
                    (string) $event->id,
                    $event->type->value,
                    $event->access_area_id === null ? '' : (string) $event->access_area_id,
                    $area instanceof Area ? $area->key : '',
                    $area instanceof Area ? $area->name : '',
                    $event->registration_id === null ? '' : (string) $event->registration_id,
                    $event->registration->email ?? '',
                    $event->grant_id === null ? '' : (string) $event->grant_id,
                    $event->grant->email ?? '',
                    $event->claim_token_id === null ? '' : (string) $event->claim_token_id,
                    $event->browser_token_id === null ? '' : (string) $event->browser_token_id,
                    $event->user_id === null ? '' : (string) $event->user_id,
                    $event->subject_type ?? '',
                    $event->subject_id === null ? '' : (string) $event->subject_id,
                    $this->json($event->payload ?? []),
                    $this->json($event->metadata ?? []),
                ];
                $exported++;
            }

            return null;
        });

        return $this->toCsv($rows);
    }

    /**
     * @param  array<array-key, mixed>  $value
     */
    private function json(array $value): string
    {
        if ($value === []) {
            return '';
        }

        $json = json_encode($value, JSON_UNESCAPED_SLASHES);

        throw_if($json === false, RuntimeException::class, 'Unable to encode Access Gate audit payload.');

        return $json;
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function toCsv(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        throw_if($stream === false, RuntimeException::class, 'Unable to open temporary CSV stream.');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);

        $csv = stream_get_contents($stream);
        fclose($stream);

        throw_if($csv === false, RuntimeException::class, 'Unable to read temporary CSV stream.');

        return $csv;
    }
}
