<?php

declare(strict_types=1);

namespace Capell\Comments\Http\Controllers;

use Capell\Comments\Actions\DisableCommentAuthorReplyNotificationsAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

final class DisableCommentAuthorReplyNotificationsController
{
    public function __invoke(string $token): View|Response
    {
        $author = DisableCommentAuthorReplyNotificationsAction::run($token);

        if ($author === null) {
            return response(__('capell-comments::messages.invalid_token'), 404);
        }

        return view('capell-comments::reply-notifications-disabled');
    }
}
