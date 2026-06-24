<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Data\AiCreatorPreviewData;
use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Capell\AiCreator\Models\AiCreatorSession;
use Lorisleiva\Actions\Concerns\AsAction;

final class PreviewAiCreatorSessionAction
{
    use AsAction;

    public function handle(AiCreatorSession $session): AiCreatorPreviewData
    {
        $preview = BuildAiCreatorSessionPreviewAction::make()->handle($session);

        $session->forceFill([
            'preview_output' => $preview->toPayload(),
            'status' => AiCreatorSessionStatus::Previewed,
        ])->save();

        return $preview;
    }
}
