<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\LiveChatWidgetConfigData;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildLiveChatWidgetConfigAction
{
    use AsAction;

    public function handle(): LiveChatWidgetConfigData
    {
        $widget = config('capell-live-chat.widget', []);
        $enabled = config('capell-live-chat.enabled', true) === true;

        return new LiveChatWidgetConfigData(
            enabled: $enabled,
            agentName: $this->string($widget, 'agent_name', __('capell-live-chat::generic.widget.agent_name')),
            avatarInitials: $this->string($widget, 'avatar_initials', 'LA'),
            brandName: $this->string($widget, 'brand_name', __('capell-live-chat::generic.widget.brand_name')),
            welcomeMessage: $this->string($widget, 'welcome_message', __('capell-live-chat::generic.widget.welcome_message')),
            aiDisclosure: $this->string($widget, 'ai_disclosure', __('capell-live-chat::generic.widget.ai_disclosure')),
            messagePlaceholder: $this->string($widget, 'message_placeholder', __('capell-live-chat::generic.widget.message_placeholder')),
            messageFirstLabel: $this->string($widget, 'message_first_label', __('capell-live-chat::generic.widget.message_first_label')),
            detailsFirstLabel: $this->string($widget, 'details_first_label', __('capell-live-chat::generic.widget.details_first_label')),
            handoffLabel: $this->string($widget, 'handoff_label', __('capell-live-chat::generic.widget.handoff_label')),
            statusMessage: $this->string($widget, 'offline_message', __('capell-live-chat::generic.widget.offline_message')),
            startUrl: route('capell-live-chat.conversations.store'),
            messageUrl: url('/' . trim((string) config('capell-live-chat.public_path_prefix', 'live-chat'), '/') . '/conversations/{conversation}/messages'),
            handoffUrl: url('/' . trim((string) config('capell-live-chat.public_path_prefix', 'live-chat'), '/') . '/conversations/{conversation}/handoff'),
            branding: [
                'primary' => $this->string($widget, 'primary_color', '#087765'),
                'surface' => $this->string($widget, 'surface_color', '#fcfffb'),
                'text' => $this->string($widget, 'text_color', '#101715'),
            ],
            proactiveTriggers: $this->proactiveTriggers($widget['proactive_triggers'] ?? []),
            labels: $this->labels($widget['labels'] ?? []),
        );
    }

    /**
     * @param  array<string, mixed>  $widget
     */
    private function string(array $widget, string $key, string $fallback): string
    {
        $value = $widget[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : $fallback;
    }

    /**
     * @return list<array{name: string, path: string, delay_seconds: int, message: string}>
     */
    private function proactiveTriggers(mixed $triggers): array
    {
        if (! is_array($triggers)) {
            return [];
        }

        $normalized = [];

        foreach ($triggers as $trigger) {
            if (! is_array($trigger)) {
                continue;
            }

            $name = $trigger['name'] ?? null;
            $path = $trigger['path'] ?? null;
            $message = $trigger['message'] ?? null;
            $delaySeconds = $trigger['delay_seconds'] ?? 10;

            if (! is_string($name) || ! is_string($path) || ! is_string($message)) {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'path' => $path,
                'delay_seconds' => max(1, (int) $delaySeconds),
                'message' => $message,
            ];
        }

        return $normalized;
    }

    /**
     * @return array<string, string>
     */
    private function labels(mixed $labels): array
    {
        $configuredLabels = is_array($labels) ? $labels : [];

        return [
            'close' => $this->string($configuredLabels, 'close', __('capell-live-chat::generic.widget.labels.close')),
            'name' => $this->string($configuredLabels, 'name', __('capell-live-chat::generic.widget.labels.name')),
            'email' => $this->string($configuredLabels, 'email', __('capell-live-chat::generic.widget.labels.email')),
            'phone' => $this->string($configuredLabels, 'phone', __('capell-live-chat::generic.widget.labels.phone')),
            'company' => $this->string($configuredLabels, 'company', __('capell-live-chat::generic.widget.labels.company')),
            'send' => $this->string($configuredLabels, 'send', __('capell-live-chat::generic.widget.labels.send')),
            'sending' => $this->string($configuredLabels, 'sending', __('capell-live-chat::generic.widget.labels.sending')),
            'request_failed' => $this->string($configuredLabels, 'request_failed', __('capell-live-chat::generic.widget.labels.request_failed')),
            'handoff_requires_conversation' => $this->string($configuredLabels, 'handoff_requires_conversation', __('capell-live-chat::generic.widget.labels.handoff_requires_conversation')),
            'handoff_requested' => $this->string($configuredLabels, 'handoff_requested', __('capell-live-chat::generic.widget.labels.handoff_requested')),
            'handoff_failed' => $this->string($configuredLabels, 'handoff_failed', __('capell-live-chat::generic.widget.labels.handoff_failed')),
        ];
    }
}
