<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\LiveChatWidgetConfigData;
use Capell\LiveChat\Models\LiveChatInstallation;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildLiveChatWidgetConfigAction
{
    use AsAction;

    public function handle(?LiveChatInstallation $installation = null): LiveChatWidgetConfigData
    {
        $widget = $this->widgetSettings($installation);
        $enabled = config('capell-live-chat.enabled', true) === true;
        $publicKey = $installation?->public_key;

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
            startUrl: $this->conversationUrl($publicKey),
            messageUrl: $this->messageUrl($publicKey),
            handoffUrl: $this->handoffUrl($publicKey),
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
     * @return array<string, mixed>
     */
    private function widgetSettings(?LiveChatInstallation $installation): array
    {
        $widget = config('capell-live-chat.widget', []);
        $configuredWidget = is_array($widget) ? $widget : [];

        if ($installation === null || ! is_array($installation->widget_settings)) {
            return $configuredWidget;
        }

        return array_replace_recursive($configuredWidget, $installation->widget_settings);
    }

    private function conversationUrl(?string $publicKey): string
    {
        if (is_string($publicKey) && $publicKey !== '') {
            return route('capell-live-chat.api.conversations.store', ['public_key' => $publicKey]);
        }

        return route('capell-live-chat.conversations.store');
    }

    private function messageUrl(?string $publicKey): string
    {
        if (is_string($publicKey) && $publicKey !== '') {
            return url('/' . $this->prefix() . '/api/' . rawurlencode($publicKey) . '/conversations/{conversation}/messages');
        }

        return url('/' . $this->prefix() . '/conversations/{conversation}/messages');
    }

    private function handoffUrl(?string $publicKey): string
    {
        if (is_string($publicKey) && $publicKey !== '') {
            return url('/' . $this->prefix() . '/api/' . rawurlencode($publicKey) . '/conversations/{conversation}/handoff');
        }

        return url('/' . $this->prefix() . '/conversations/{conversation}/handoff');
    }

    private function prefix(): string
    {
        return trim($this->configString('capell-live-chat.public_path_prefix', 'live-chat'), '/');
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
            $delaySeconds = $trigger['delay_seconds'] ?? null;

            if (! is_string($name) || ! is_string($path) || ! is_string($message)) {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'path' => $path,
                'delay_seconds' => max(1, is_numeric($delaySeconds) ? (int) $delaySeconds : 10),
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

    private function configString(string $key, string $fallback): string
    {
        $value = config($key);

        return is_string($value) && trim($value) !== '' ? $value : $fallback;
    }
}
