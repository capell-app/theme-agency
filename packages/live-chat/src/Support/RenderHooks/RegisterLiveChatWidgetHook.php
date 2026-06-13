<?php

declare(strict_types=1);

namespace Capell\LiveChat\Support\RenderHooks;

use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Illuminate\Support\Str;

final class RegisterLiveChatWidgetHook
{
    public function __construct(private readonly RenderHookRegistry $registry) {}

    public function register(): void
    {
        $this->registry->register(
            RenderHookLocation::BodyEnd,
            static fn (): string => self::shouldRenderForCurrentRequest()
                ? view('capell-live-chat::widget', [
                    'config' => BuildLiveChatWidgetConfigAction::run(),
                ])->render()
                : '',
        );
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
