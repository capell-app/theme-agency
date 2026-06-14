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
        $staffMemberId = $staffMember instanceof EquestrianStaffMember ? $staffMember->getKey() : null;

        return EquestrianHorseCareTask::query()
            ->with(['horseProfile', 'assignedStaffMember'])
            ->whereNull('completed_at')
            ->where('due_at', '<=', $dueBefore)
            ->when($staffMemberId !== null, function (Builder $query) use ($staffMemberId, $includeUnassigned): void {
                $query->where(function (Builder $assignedQuery) use ($staffMemberId, $includeUnassigned): void {
                    $assignedQuery->where('assigned_staff_member_id', $staffMemberId);

                    if ($includeUnassigned) {
                        $assignedQuery->orWhereNull('assigned_staff_member_id');
                    }
                });
            })
            ->orderBy('due_at')
            ->get();
    }
}
