<?php

declare(strict_types=1);

namespace Capell\AccessGate\Support\RenderHooks;

use Capell\AccessGate\Actions\ResolveAccessGateAnnouncementBarAction;
use Capell\Frontend\Contracts\RenderHookExtensionInterface;
use Capell\Frontend\Data\RenderHookContext;
use Illuminate\Contracts\View\View;

final class RegisterAnnouncementBarHook implements RenderHookExtensionInterface
{
    public function render(RenderHookContext $context): string
    {
        $announcement = ResolveAccessGateAnnouncementBarAction::run(request());

        if ($announcement === null) {
            return '';
        }

        $view = view('capell-access-gate::components.announcement-bar', [
            'announcement' => $announcement,
        ]);

        return $view instanceof View ? $view->render() : (string) $view;
    }
}
