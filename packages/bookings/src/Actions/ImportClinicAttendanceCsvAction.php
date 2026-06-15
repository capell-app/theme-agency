<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingGroupSession;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingGroupSession run(BookingGroupSession $groupSession, string $csv)
 */
class ImportClinicAttendanceCsvAction
{
    use AsAction;

    public function handle(BookingGroupSession $groupSession, string $csv): BookingGroupSession
    {
        $rows = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $csv) ?: []));
        $attendance = [];

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 0 && str_contains(strtolower($row), 'email')) {
                continue;
            }

            $columns = str_getcsv($row);
            $email = strtolower(trim((string) ($columns[0] ?? '')));

            if ($email === '') {
                continue;
            }

            $attendance[$email] = [
                'email' => $email,
                'name' => trim((string) ($columns[1] ?? '')),
                'status' => trim((string) ($columns[2] ?? 'attended')) ?: 'attended',
            ];
        }

        $groupSession->forceFill([
            'social_attendance' => array_values($attendance),
        ])->save();

        return $groupSession->refresh();
    }
}
