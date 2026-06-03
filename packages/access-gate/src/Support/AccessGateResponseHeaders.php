<?php

declare(strict_types=1);

namespace Capell\AccessGate\Support;

use Symfony\Component\HttpFoundation\Response;

final class AccessGateResponseHeaders
{
    /**
     * @template TResponse of Response
     *
     * @param  TResponse  $response
     * @return TResponse
     */
    public static function noStore(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
