<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Models\EquestrianHorseCareTask;
use Capell\EquestrianClinics\Models\EquestrianStaffMember;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, EquestrianHorseCareTask> run(?EquestrianStaffMember $staffMember = null, ?CarbonImmutable $dueBefore = null, bool $includeUnassigned = true)
 */
final class BuildStaffCareWorklistAction
{
    use AsAction;

    /**
     * @return Collection<int, EquestrianHorseCareTask>
     */
    public function handle(
        ?EquestrianStaffMember $staffMember = null,
        ?CarbonImmutable $dueBefore = null,
        bool $includeUnassigned = true,
    ): Collection {
        $dueBefore ??= CarbonImmutable::now()->endOfDay();

        return EquestrianHorseCareTask::query()
            ->with(['horseProfile', 'assignedStaffMember'])
            ->whereNull('completed_at')
            ->where('due_at', '<=', $dueBefore)
            ->when($staffMember instanceof EquestrianStaffMember, function (Builder $query) use ($staffMember, $includeUnassigned): void {
                $query->where(function (Builder $assignedQuery) use ($staffMember, $includeUnassigned): void {
                    $assignedQuery->where('assigned_staff_member_id', $staffMember->getKey());

                    if ($includeUnassigned) {
                        $assignedQuery->orWhereNull('assigned_staff_member_id');
                    }
                });
            })
            ->orderBy('due_at')
            ->get();
    }
}
