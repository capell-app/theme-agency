<?php

declare(strict_types=1);

namespace Capell\LiveChat\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\LiveChat\Actions\ApplyLiveChatCorsHeadersAction;
use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Capell\LiveChat\Actions\CloseLiveChatConversationAction;
use Capell\LiveChat\Actions\GuardLiveChatInstallationOriginAction;
use Capell\LiveChat\Actions\RequestLiveChatHandoffAction;
use Capell\LiveChat\Actions\ResolveLiveChatConversationForInstallationAction;
use Capell\LiveChat\Actions\ResolveLiveChatInstallationAction;
use Capell\LiveChat\Actions\StartLiveChatConversationAction;
use Capell\LiveChat\Actions\StoreLiveChatMessageAction;
use Capell\LiveChat\Actions\SyncLiveChatConversationContactAction;
use Capell\LiveChat\Models\LiveChatAIRun;
use Capell\LiveChat\Models\LiveChatAvailabilityException;
use Capell\LiveChat\Models\LiveChatAvailabilityWindow;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatEscalationRule;
use Capell\LiveChat\Models\LiveChatInstallation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatKnowledgeGap;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;

final class LiveChatHealthCheck implements ChecksExtensionHealth
{
    /** @var array<string, class-string> */
    private const array MODELS_BY_MORPH_ALIAS = [
        'live_chat_conversation' => LiveChatConversation::class,
        'live_chat_message' => LiveChatMessage::class,
        'live_chat_ai_run' => LiveChatAIRun::class,
        'live_chat_availability_window' => LiveChatAvailabilityWindow::class,
        'live_chat_availability_exception' => LiveChatAvailabilityException::class,
        'live_chat_escalation_rule' => LiveChatEscalationRule::class,
        'live_chat_installation' => LiveChatInstallation::class,
        'live_chat_knowledge_document' => LiveChatKnowledgeDocument::class,
        'live_chat_knowledge_gap' => LiveChatKnowledgeGap::class,
        'live_chat_knowledge_source' => LiveChatKnowledgeSource::class,
    ];

    /** @var list<class-string> */
    private const array ACTIONS = [
        ApplyLiveChatCorsHeadersAction::class,
        BuildLiveChatWidgetConfigAction::class,
        CloseLiveChatConversationAction::class,
        GuardLiveChatInstallationOriginAction::class,
        RequestLiveChatHandoffAction::class,
        ResolveLiveChatConversationForInstallationAction::class,
        ResolveLiveChatInstallationAction::class,
        StartLiveChatConversationAction::class,
        StoreLiveChatMessageAction::class,
        SyncLiveChatConversationContactAction::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->missingTables() === []
            && $this->unregisteredMorphAliases() === []
            && $this->unresolvableActions() === [];
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect($this->requiredTableNames())
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function unregisteredMorphAliases(): array
    {
        return array_values(collect(self::MODELS_BY_MORPH_ALIAS)
            ->reject(static fn (string $modelClass, string $morphAlias): bool => Relation::getMorphedModel($morphAlias) === $modelClass)
            ->keys()
            ->values()
            ->all());
    }

    /**
     * @return list<class-string>
     */
    public function unresolvableActions(): array
    {
        $actions = [];

        foreach (self::ACTIONS as $actionClass) {
            if (! app()->make($actionClass) instanceof $actionClass) {
                $actions[] = $actionClass;
            }
        }

        return $actions;
    }

    /**
     * @return list<string>
     */
    private function requiredTableNames(): array
    {
        $tables = config('capell-live-chat.tables', []);

        $resolve = static function (string $key, string $fallback) use ($tables): string {
            $value = is_array($tables) ? ($tables[$key] ?? null) : null;

            return is_string($value) && $value !== '' ? $value : $fallback;
        };

        return [
            $resolve('conversations', 'live_chat_conversations'),
            $resolve('installations', 'live_chat_installations'),
            $resolve('messages', 'live_chat_messages'),
            $resolve('ai_runs', 'live_chat_ai_runs'),
            $resolve('availability_windows', 'live_chat_availability_windows'),
            $resolve('availability_exceptions', 'live_chat_availability_exceptions'),
            $resolve('escalation_rules', 'live_chat_escalation_rules'),
            $resolve('knowledge_documents', 'live_chat_knowledge_documents'),
            $resolve('knowledge_gaps', 'live_chat_knowledge_gaps'),
            $resolve('knowledge_sources', 'live_chat_knowledge_sources'),
        ];
    }
}
