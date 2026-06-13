<?php

declare(strict_types=1);

namespace Capell\LiveChat\Support\RenderHooks;

use Capell\Frontend\Contracts\RenderHookExtensionInterface;
use Capell\Frontend\Data\RenderHookContext;
use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Capell\LiveChat\Actions\ResolveLiveChatInstallationAction;
use Illuminate\Support\Str;

final class RegisterLiveChatWidgetHook implements RenderHookExtensionInterface
{
    public function render(RenderHookContext $context): string
    {
        if (! self::shouldRenderForCurrentRequest()) {
            return '';
        }

        $installation = ResolveLiveChatInstallationAction::run();

        return view('capell-live-chat::widget', [
            'config' => BuildLiveChatWidgetConfigAction::run($installation),
        ])->render();
    }

    private static function shouldRenderForCurrentRequest(): bool
    {
        if (config('capell-live-chat.enabled', true) !== true) {
            return false;
        }

        $path = '/' . trim(request()->path(), '/');
        $ignoredPaths = config('capell-live-chat.ignored_paths', []);

        if (! is_array($ignoredPaths)) {
            return true;
        }

        foreach ($ignoredPaths as $ignoredPath) {
            if (! is_string($ignoredPath) || trim($ignoredPath) === '') {
                continue;
            }

            if (Str::is('/' . trim($ignoredPath, '/'), $path)) {
                return false;
            }
        }

        return true;
    }
}
