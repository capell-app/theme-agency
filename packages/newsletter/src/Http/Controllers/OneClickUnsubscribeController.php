<?php

declare(strict_types=1);

namespace Capell\Newsletter\Http\Controllers;

use Capell\Newsletter\Actions\UnsubscribeSubscriberAction;
use Capell\Newsletter\Data\ConsentEvidenceData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OneClickUnsubscribeController
{
    public function __invoke(Request $request, string $token): Response
    {
        $subscriber = UnsubscribeSubscriberAction::run($token, new ConsentEvidenceData(
            sourceType: 'list_unsubscribe_one_click',
            sourceId: hash('sha256', $token),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            url: $request->fullUrl(),
            referer: $request->headers->get('referer'),
        ));

        return response(
            $subscriber === null
                ? __('capell-newsletter::messages.invalid_token')
                : __('capell-newsletter::messages.one_click_unsubscribed'),
            $subscriber === null ? 404 : 200,
        );
    }
}
