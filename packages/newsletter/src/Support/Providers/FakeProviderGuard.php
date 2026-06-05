<?php

declare(strict_types=1);

namespace Capell\Newsletter\Support\Providers;

final class FakeProviderGuard
{
    public static function isAllowed(): bool
    {
        if ((bool) config('capell-newsletter.providers.allow_fake_provider', false)) {
            return true;
        }

        if ((bool) config('capell-newsletter.webhooks.allow_fake_provider', false)) {
            return true;
        }

        return app()->environment('local', 'testing');
    }
}
