<?php

declare(strict_types=1);

namespace Capell\Comments\Http\Controllers;

use Capell\Comments\Actions\VerifyCommentAuthorEmailAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class VerifyCommentAuthorEmailController
{
    public function show(string $token): View
    {
        return view('capell-comments::verify-email', [
            'token' => $token,
        ]);
    }

    public function store(string $token): Response|RedirectResponse
    {
        $author = VerifyCommentAuthorEmailAction::run($token);

        if ($author === null) {
            return response(__('capell-comments::messages.invalid_token'), 404);
        }

        return to_route('capell-comments.verify', ['token' => $token])
            ->with('capell_comments_verified', true);
    }
}
