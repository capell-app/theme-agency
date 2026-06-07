<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Enums\LeadStatus;
use Capell\Contacts\Models\Lead;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpdateLeadStatusAction
{
    use AsAction;

    public function handle(Lead $lead, LeadStatus $status): Lead
    {
        $lead->status = $status;

        if ($status === LeadStatus::Qualified && $lead->qualified_at === null) {
            $lead->qualified_at = CarbonImmutable::now();
        }

        if (in_array($status, [LeadStatus::Won, LeadStatus::Lost, LeadStatus::Archived], true) && $lead->closed_at === null) {
            $lead->closed_at = CarbonImmutable::now();
        }

        $lead->save();

        return $lead;
    }
}
