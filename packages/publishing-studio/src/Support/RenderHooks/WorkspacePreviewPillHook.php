<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Support\RenderHooks;

use Capell\Frontend\Contracts\RenderHookExtensionInterface;
use Capell\Frontend\Data\RenderHookContext;

final class WorkspacePreviewPillHook implements RenderHookExtensionInterface
{
    public function render(RenderHookContext $context): string
    {
        return view('capell-publishing-studio::components.workspace-preview-pill')->render();
    }
}
