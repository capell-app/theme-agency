<?php

declare(strict_types=1);

namespace Capell\Comments\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RenderCommentThreadController
{
    public function __invoke(Request $request): Response
    {
        $threadKey = $request->string('thread')->toString();

        return response()
            ->view('capell-comments::livewire.thread-livewire', [
                'threadKey' => $threadKey,
            ])
            ->header('Cache-Control', 'no-store, private');
    }
}
