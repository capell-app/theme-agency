<?php

declare(strict_types=1);

namespace Capell\LiveChat\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\LiveChat\Actions\ApplyLiveChatCorsHeadersAction;
use Capell\LiveChat\Actions\BuildLiveChatAnalyticsAction;
use Capell\LiveChat\Actions\BuildLiveChatOperatorStateAction;
use Capell\LiveChat\Actions\BuildLiveChatSuggestedReplyAction;
use Capell\LiveChat\Actions\BuildLiveChatTranscriptAction;
use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Capell\LiveChat\Actions\CloseLiveChatConversationAction;
use Capell\LiveChat\Actions\DetectLiveChatIntentAction;
use Capell\LiveChat\Actions\DetermineLiveChatEscalationAction;
use Capell\LiveChat\Actions\GenerateLiveChatSummaryAction;
use Capell\LiveChat\Actions\GuardLiveChatInstallationOriginAction;
use Capell\LiveChat\Actions\GuardLiveChatSameSiteRequestAction;
use Capell\LiveChat\Actions\IndexLiveChatKnowledgeDocumentAction;
use Capell\LiveChat\Actions\IndexLiveChatKnowledgeSourceAction;
use Capell\LiveChat\Actions\RecordLiveChatAIRunAction;
use Capell\LiveChat\Actions\RecordLiveChatKnowledgeGapAction;
use Capell\LiveChat\Actions\ReplyToLiveChatMessageAction;
use Capell\LiveChat\Actions\RequestLiveChatHandoffAction;
use Capell\LiveChat\Actions\ResolveLiveChatAvailabilityAction;
use Capell\LiveChat\Actions\ResolveLiveChatConversationForInstallationAction;
use Capell\LiveChat\Actions\ResolveLiveChatInstallationAction;
use Capell\LiveChat\Actions\SearchLiveChatKnowledgeDocumentsAction;
use Capell\LiveChat\Actions\StartLiveChatConversationAction;
use Capell\LiveChat\Actions\StoreLiveChatAttachmentsAction;
use Capell\LiveChat\Actions\StoreLiveChatMessageAction;
use Capell\LiveChat\Actions\SuggestLiveChatHumanReplyAction;
use Capell\LiveChat\Actions\SyncLiveChatConversationContactAction;
use Capell\LiveChat\Contracts\LiveChatWidgetRenderer;
use Capell\LiveChat\Enums\ResourceEnum;
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
use Capell\LiveChat\Support\RenderHooks\RegisterLiveChatWidgetHook;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

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
        BuildLiveChatAnalyticsAction::class,
        BuildLiveChatOperatorStateAction::class,
        BuildLiveChatSuggestedReplyAction::class,
        BuildLiveChatTranscriptAction::class,
        BuildLiveChatWidgetConfigAction::class,
        CloseLiveChatConversationAction::class,
        DetermineLiveChatEscalationAction::class,
        DetectLiveChatIntentAction::class,
        GenerateLiveChatSummaryAction::class,
        GuardLiveChatInstallationOriginAction::class,
        GuardLiveChatSameSiteRequestAction::class,
        IndexLiveChatKnowledgeDocumentAction::class,
        IndexLiveChatKnowledgeSourceAction::class,
        RecordLiveChatAIRunAction::class,
        RecordLiveChatKnowledgeGapAction::class,
        ReplyToLiveChatMessageAction::class,
        RequestLiveChatHandoffAction::class,
        ResolveLiveChatAvailabilityAction::class,
        ResolveLiveChatConversationForInstallationAction::class,
        ResolveLiveChatInstallationAction::class,
        SearchLiveChatKnowledgeDocumentsAction::class,
        StartLiveChatConversationAction::class,
        StoreLiveChatAttachmentsAction::class,
        StoreLiveChatMessageAction::class,
        SuggestLiveChatHumanReplyAction::class,
        SyncLiveChatConversationContactAction::class,
    ];

    /** @var list<string> */
    private const array ROUTE_NAMES = [
        'capell-live-chat.widget',
        'capell-live-chat.widget.script',
        'capell-live-chat.api.preflight',
        'capell-live-chat.api.conversations.store',
        'capell-live-chat.api.messages.store',
        'capell-live-chat.api.handoff.store',
        'capell-live-chat.conversations.store',
        'capell-live-chat.messages.store',
        'capell-live-chat.handoff.store',
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->missingTables() === []
            && $this->unregisteredMorphAliases() === []
            && $this->unresolvableActions() === []
            && $this->missingRoutes() === []
            && $this->missingAdminResources() === []
            && $this->unresolvableWidgetSurfaces() === [];
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
    public function missingRoutes(): array
    {
        return array_values(collect(self::ROUTE_NAMES)
            ->reject(static fn (string $routeName): bool => Route::has($routeName))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingAdminResources(): array
    {
        return array_values(collect(ResourceEnum::cases())
            ->map(static fn (ResourceEnum $resource): string => $resource->value)
            ->reject(static fn (string $resourceClass): bool => class_exists($resourceClass))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function unresolvableWidgetSurfaces(): array
    {
        $missing = [];

        try {
            $renderer = app()->make(LiveChatWidgetRenderer::class);
        } catch (Throwable) {
            $renderer = null;
        }

        if (! $renderer instanceof LiveChatWidgetRenderer) {
            $missing[] = LiveChatWidgetRenderer::class;
        }

        if (! class_exists(RegisterLiveChatWidgetHook::class)) {
            $missing[] = RegisterLiveChatWidgetHook::class;
        }

        foreach (['capell-live-chat::widget', 'capell-live-chat::script'] as $viewName) {
            if (! view()->exists($viewName)) {
                $missing[] = $viewName;
            }
        }

        return $missing;
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
