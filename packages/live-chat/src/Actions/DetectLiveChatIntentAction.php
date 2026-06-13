<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Enums\LiveChatIntent;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class DetectLiveChatIntentAction
{
    use AsAction;

    public function handle(string $message): LiveChatIntent
    {
        $body = Str::lower($message);

        if (Str::contains($body, ['urgent', 'emergency', 'data breach', 'legal', 'solicitor'])) {
            return LiveChatIntent::Urgent;
        }

        if (Str::contains($body, ['complaint', 'refund', 'chargeback', 'unhappy'])) {
            return LiveChatIntent::Complaint;
        }

        if (Str::contains($body, ['invoice', 'billing', 'payment', 'paid', 'subscription'])) {
            return LiveChatIntent::Billing;
        }

        if (Str::contains($body, ['bug', 'error', 'broken', 'technical', 'not working'])) {
            return LiveChatIntent::TechnicalIssue;
        }

        if (Str::contains($body, ['price', 'pricing', 'quote', 'demo', 'buy', 'sales'])) {
            return LiveChatIntent::Sales;
        }

        if (Str::contains($body, ['help', 'support', 'account'])) {
            return LiveChatIntent::Support;
        }

        return LiveChatIntent::General;
    }
}
