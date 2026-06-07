<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Models\Subscriber;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<string, string> run(Subscriber $subscriber)
 */
class BuildListUnsubscribeHeadersAction
{
    use AsAction;

    /**
     * @return array<string, string>
     */
    public function handle(Subscriber $subscriber): array
    {
        $token = CreateUnsubscribeTokenAction::run($subscriber);

        $oneClickUrl = route('capell-newsletter.unsubscribe.one-click', ['token' => $token]);
        $manualUrl = route('capell-newsletter.unsubscribe', ['token' => $token]);

        return [
            'List-Unsubscribe' => sprintf('<%s>, <%s>', $oneClickUrl, $manualUrl),
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ];
    }
}
