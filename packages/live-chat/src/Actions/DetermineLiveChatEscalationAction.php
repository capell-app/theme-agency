<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\LiveChatEscalationDecisionData;
use Capell\LiveChat\Data\LiveChatResponseData;
use Capell\LiveChat\Enums\EscalationReason;
use Capell\LiveChat\Enums\EscalationTriggerType;
use Capell\LiveChat\Enums\LiveChatIntent;
use Capell\LiveChat\Enums\LiveChatPriority;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatEscalationRule;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class DetermineLiveChatEscalationAction
{
    use AsAction;

    public function handle(
        LiveChatConversation $conversation,
        LiveChatMessage $message,
        LiveChatResponseData $response,
        bool $available,
    ): LiveChatEscalationDecisionData {
        if (! $available) {
            return new LiveChatEscalationDecisionData(
                shouldEscalate: true,
                reason: EscalationReason::AfterHours,
                priority: LiveChatPriority::Normal,
                routeTo: $this->defaultQueue(),
                message: $this->configString('capell-live-chat.widget.offline_message', 'We are currently out of the office.'),
            );
        }

        $rule = $this->matchingRule($conversation, $message, $response);

        if ($rule instanceof LiveChatEscalationRule) {
            return new LiveChatEscalationDecisionData(
                shouldEscalate: true,
                reason: $this->reasonForRule($rule),
                priority: $rule->priority,
                routeTo: $rule->route_to ?? $this->defaultQueue(),
                message: null,
            );
        }

        if ($response->confidence < $this->configFloat('capell-live-chat.low_confidence_threshold', 0.55)) {
            return new LiveChatEscalationDecisionData(
                shouldEscalate: true,
                reason: EscalationReason::LowConfidence,
                priority: LiveChatPriority::High,
                routeTo: $this->defaultQueue(),
                message: null,
            );
        }

        if ($response->intent === LiveChatIntent::Urgent || $response->intent === LiveChatIntent::Complaint) {
            return new LiveChatEscalationDecisionData(
                shouldEscalate: true,
                reason: EscalationReason::Sensitive,
                priority: LiveChatPriority::Urgent,
                routeTo: $this->defaultQueue(),
                message: null,
            );
        }

        return new LiveChatEscalationDecisionData(shouldEscalate: false);
    }

    private function matchingRule(
        LiveChatConversation $conversation,
        LiveChatMessage $message,
        LiveChatResponseData $response,
    ): ?LiveChatEscalationRule {
        return LiveChatEscalationRule::query()
            ->where('is_active', true)
            ->where(static function (Builder $query) use ($conversation): void {
                $query->where('site_id', $conversation->site_id)->orWhereNull('site_id');
            })
            ->orderByRaw('site_id is null')
            ->get()
            ->first(function (LiveChatEscalationRule $rule) use ($message, $response): bool {
                return match ($rule->trigger_type) {
                    EscalationTriggerType::Keyword => $this->keywordMatches($message->body, $rule->trigger_value),
                    EscalationTriggerType::Intent => $rule->trigger_value === $response->intent->value,
                    EscalationTriggerType::LowConfidence => $response->confidence < $this->thresholdForRule($rule),
                    EscalationTriggerType::Manual, EscalationTriggerType::AfterHours => false,
                };
            });
    }

    private function keywordMatches(string $message, ?string $keyword): bool
    {
        if (! is_string($keyword) || trim($keyword) === '') {
            return false;
        }

        return Str::contains(Str::lower($message), Str::lower(trim($keyword)));
    }

    private function reasonForRule(LiveChatEscalationRule $rule): EscalationReason
    {
        return match ($rule->trigger_type) {
            EscalationTriggerType::Keyword => EscalationReason::Keyword,
            EscalationTriggerType::LowConfidence => EscalationReason::LowConfidence,
            EscalationTriggerType::AfterHours => EscalationReason::AfterHours,
            EscalationTriggerType::Manual => EscalationReason::Manual,
            EscalationTriggerType::Intent => EscalationReason::Sensitive,
        };
    }

    private function defaultQueue(): string
    {
        return $this->configString('capell-live-chat.escalation.default_queue', 'support');
    }

    private function thresholdForRule(LiveChatEscalationRule $rule): float
    {
        if (is_string($rule->trigger_value) && is_numeric($rule->trigger_value)) {
            return (float) $rule->trigger_value;
        }

        return $this->configFloat('capell-live-chat.low_confidence_threshold', 0.55);
    }

    private function configFloat(string $key, float $fallback): float
    {
        $value = config($key);

        return is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))
            ? (float) $value
            : $fallback;
    }

    private function configString(string $key, string $fallback): string
    {
        $value = config($key);

        return is_string($value) && $value !== '' ? $value : $fallback;
    }
}
