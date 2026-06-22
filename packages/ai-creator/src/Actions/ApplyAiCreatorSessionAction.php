<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Capell\AiCreator\Models\AiCreatorSession;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

final class ApplyAiCreatorSessionAction
{
    use AsAction;

    public function handle(AiCreatorSession $session): AiCreatorSession
    {
        return DB::transaction(function () use ($session): AiCreatorSession {
            $session->forceFill([
                'status' => AiCreatorSessionStatus::Applied,
                'applied_at' => now(),
            ])->save();

            return $session->refresh();
        });
    }
}
