<?php

declare(strict_types=1);

namespace Capell\PublicActions\Http\Controllers;

use Capell\PublicActions\Actions\SubmitPublicActionAction;
use Capell\PublicActions\Actions\VerifyTrustedPublicActionSubmissionRequestAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SubmitTrustedPublicActionController
{
    public function __construct(
        private readonly SubmitPublicActionAction $submitPublicAction,
        private readonly VerifyTrustedPublicActionSubmissionRequestAction $verifyTrustedRequest,
    ) {}

    public function __invoke(Request $request, string $action): JsonResponse
    {
        $publicAction = $this->submitPublicAction->resolve($action, $request);

        if (! $this->verifyTrustedRequest->handle($publicAction, $request)) {
            return response()->json([
                'message' => __('capell-public-actions::generic.invalid_signature'),
            ], 401);
        }

        $result = $this->submitPublicAction->handle($publicAction, [
            ...$request->all(),
            'source_type' => 'trusted_webhook',
        ], $request, trusted: true);

        return response()->json([
            'success' => $result->success,
            'message' => $result->message,
            'redirect_url' => $result->redirectUrl,
            'created_model_type' => $result->createdModelType,
            'created_model_id' => $result->createdModelId,
        ], $result->success ? 200 : 422);
    }
}
