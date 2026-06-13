<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\Response;

final class ApplyLiveChatCorsHeadersAction
{
    use AsAction;

    public function handle(Response $response, ?string $origin): Response
    {
        if (is_string($origin) && trim($origin) !== '') {
            $response->headers->set('Access-Control-Allow-Origin', trim($origin));
            $response->headers->set('Vary', 'Origin', false);
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept');
        $response->headers->set('Access-Control-Max-Age', '600');

        return $response;
    }

    public function preflight(?string $origin): Response
    {
        $response = response('', 204);

        return $this->handle($response, $origin);
    }
}
