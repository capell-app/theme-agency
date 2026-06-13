<?php

declare(strict_types=1);

namespace Capell\Comments\Support\RenderHooks;

use Capell\Comments\Livewire\CommentThreadComponent;
use Capell\Frontend\Contracts\RenderHookExtensionInterface;
use Capell\Frontend\Data\MainContentRenderHookData;
use Capell\Frontend\Data\RenderHookContext;
use Illuminate\Database\Eloquent\Model;

class CommentThreadRenderHook implements RenderHookExtensionInterface
{
    public function render(RenderHookContext $context): string
    {
        $item = $context->item ?? null;

        if (! $item instanceof MainContentRenderHookData || ! $item->page instanceof Model) {
            return '';
        }

        $threadKey = CommentThreadComponent::threadKeyFor($item->page);

        return view('capell-comments::livewire.thread-shell', [
            'threadKey' => $threadKey,
        ])->render();
    }
}
