<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Core\Contracts\Extensions\RegistersExtensionFrontendComponent;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\LiveChat\Actions\BuildLiveChatAnalyticsAction;
use Capell\LiveChat\Actions\BuildLiveChatTranscriptAction;
use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Capell\LiveChat\Actions\CloseLiveChatConversationAction;
use Capell\LiveChat\Actions\IndexLiveChatKnowledgeDocumentAction;
use Capell\LiveChat\Actions\IndexLiveChatKnowledgeSourceAction;
use Capell\LiveChat\Actions\RecordLiveChatAIRunAction;
use Capell\LiveChat\Actions\RecordLiveChatKnowledgeGapAction;
use Capell\LiveChat\Actions\RequestLiveChatHandoffAction;
use Capell\LiveChat\Actions\SearchLiveChatKnowledgeDocumentsAction;
use Capell\LiveChat\Actions\StartLiveChatConversationAction;
use Capell\LiveChat\Actions\StoreLiveChatMessageAction;
use Capell\LiveChat\Actions\SyncLiveChatConversationContactAction;
use Capell\LiveChat\Filament\Resources\AvailabilityWindows\AvailabilityWindowResource;
use Capell\LiveChat\Filament\Resources\Conversations\ConversationResource;
use Capell\LiveChat\Filament\Resources\EscalationRules\EscalationRuleResource;
use Capell\LiveChat\Filament\Resources\Installations\InstallationResource;
use Capell\LiveChat\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;
use Capell\LiveChat\Health\LiveChatHealthCheck;
use Capell\LiveChat\Manifest\LiveChatAdminResourcesContribution;
use Capell\LiveChat\Manifest\LiveChatFrontendRoutesContribution;
use Capell\LiveChat\Manifest\LiveChatModelsContribution;
use Capell\LiveChat\Manifest\LiveChatWidgetContribution;
use Capell\LiveChat\Providers\LiveChatServiceProvider;
use Illuminate\Support\Facades\File;

/**
 * @return array<string, mixed>
 */
function live_chat_json_file_array(string $path): array
{
    $decoded = json_decode(File::get($path), associative: true, flags: JSON_THROW_ON_ERROR);

    throw_unless(is_array($decoded), RuntimeException::class, sprintf('JSON file [%s] did not decode to an array.', $path));

    $items = [];

    foreach ($decoded as $key => $value) {
        throw_unless(is_string($key), RuntimeException::class, sprintf('JSON file [%s] must decode to an object.', $path));

        $items[$key] = $value;
    }

    return $items;
}

/**
 * @param  array<string, mixed>  $data
 * @return array<string, mixed>
 */
function live_chat_array(array $data, string $key): array
{
    $value = $data[$key] ?? [];

    throw_unless(is_array($value), RuntimeException::class, sprintf('Manifest key [%s] must be an array.', $key));

    $items = [];

    foreach ($value as $itemKey => $itemValue) {
        throw_unless(is_string($itemKey), RuntimeException::class, sprintf('Manifest key [%s] must be an object.', $key));

        $items[$itemKey] = $itemValue;
    }

    return $items;
}

/**
 * @param  array<string, mixed>  $data
 * @return list<array<string, mixed>>
 */
function live_chat_array_list(array $data, string $key): array
{
    $value = $data[$key] ?? [];

    throw_unless(is_array($value), RuntimeException::class, sprintf('Manifest key [%s] must be an array list.', $key));

    $items = [];

    foreach ($value as $item) {
        throw_unless(is_array($item), RuntimeException::class, sprintf('Manifest key [%s] must contain only arrays.', $key));

        $arrayItem = [];

        foreach ($item as $itemKey => $itemValue) {
            throw_unless(is_string($itemKey), RuntimeException::class, sprintf('Manifest key [%s] must contain only objects.', $key));

            $arrayItem[$itemKey] = $itemValue;
        }

        $items[] = $arrayItem;
    }

    return $items;
}

/**
 * @param  array<string, mixed>  $data
 * @return list<string>
 */
function live_chat_string_list(array $data, string $key): array
{
    $value = $data[$key] ?? [];

    throw_unless(is_array($value), RuntimeException::class, sprintf('Manifest key [%s] must be a string list.', $key));

    $items = [];

    foreach ($value as $item) {
        throw_unless(is_string($item), RuntimeException::class, sprintf('Manifest key [%s] must contain only strings.', $key));

        $items[] = $item;
    }

    return $items;
}

/**
 * @param  array<string, mixed>  $data
 */
function live_chat_string(array $data, string $key): string
{
    $value = $data[$key] ?? null;

    throw_unless(is_string($value), RuntimeException::class, sprintf('Manifest key [%s] must be a string.', $key));

    return $value;
}

it('declares the live chat package manifest contract', function (): void {
    $manifest = live_chat_json_file_array(__DIR__ . '/../../capell.json');
    $composer = live_chat_json_file_array(__DIR__ . '/../../composer.json');
    $contributions = collect(live_chat_array_list($manifest, 'contributes'));
    $composerRequire = live_chat_array($composer, 'require');
    $dependencies = live_chat_array($manifest, 'dependencies');
    $providers = live_chat_array($manifest, 'providers');
    $database = live_chat_array($manifest, 'database');
    $contributionTraceability = live_chat_array($manifest, 'contributionTraceability');
    $composerRequirements = array_values(array_filter(
        array_keys($composerRequire),
        static fn (int|string $packageName): bool => is_string($packageName) && str_starts_with($packageName, 'capell-app/'),
    ));
    $manifestRequirements = live_chat_string_list($dependencies, 'requires');

    sort($composerRequirements);
    sort($manifestRequirements);

    expect($manifest)
        ->toHaveKey('manifest-version', 3)
        ->toHaveKey('name', 'capell-app/live-chat')
        ->toHaveKey('namespace', 'Capell\\LiveChat')
        ->and($manifestRequirements)->toBe($composerRequirements)
        ->and(live_chat_string_list($providers, 'runtime'))->toContain(LiveChatServiceProvider::class)
        ->and($database['migrations'] ?? null)->toBeTrue()
        ->and(live_chat_string_list($database, 'requiredTables'))->toBe([
            'live_chat_conversations',
            'live_chat_installations',
            'live_chat_messages',
            'live_chat_ai_runs',
            'live_chat_availability_windows',
            'live_chat_availability_exceptions',
            'live_chat_escalation_rules',
            'live_chat_knowledge_documents',
            'live_chat_knowledge_gaps',
            'live_chat_knowledge_sources',
        ])
        ->and($contributions->contains(static fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === LiveChatAdminResourcesContribution::class
            && ($contribution['resourceClasses'] ?? []) === [
                InstallationResource::class,
                ConversationResource::class,
                AvailabilityWindowResource::class,
                EscalationRuleResource::class,
                KnowledgeSourceResource::class,
            ]))->toBeTrue()
        ->and(live_chat_array_list($manifest, 'contributes'))->toContain([
            'type' => 'model',
            'class' => LiveChatModelsContribution::class,
            'modelClasses' => [
                'Capell\\LiveChat\\Models\\LiveChatInstallation',
                'Capell\\LiveChat\\Models\\LiveChatConversation',
                'Capell\\LiveChat\\Models\\LiveChatMessage',
                'Capell\\LiveChat\\Models\\LiveChatAIRun',
                'Capell\\LiveChat\\Models\\LiveChatAvailabilityWindow',
                'Capell\\LiveChat\\Models\\LiveChatAvailabilityException',
                'Capell\\LiveChat\\Models\\LiveChatEscalationRule',
                'Capell\\LiveChat\\Models\\LiveChatKnowledgeDocument',
                'Capell\\LiveChat\\Models\\LiveChatKnowledgeGap',
                'Capell\\LiveChat\\Models\\LiveChatKnowledgeSource',
            ],
        ])
        ->and(class_implements(LiveChatFrontendRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(LiveChatWidgetContribution::class))->toContain(RegistersExtensionFrontendComponent::class)
        ->and(live_chat_array($manifest, 'actions'))->toMatchArray([
            'buildLiveChatAnalytics' => BuildLiveChatAnalyticsAction::class,
            'buildLiveChatTranscript' => BuildLiveChatTranscriptAction::class,
            'buildLiveChatWidgetConfig' => BuildLiveChatWidgetConfigAction::class,
            'closeLiveChatConversation' => CloseLiveChatConversationAction::class,
            'indexLiveChatKnowledgeDocument' => IndexLiveChatKnowledgeDocumentAction::class,
            'indexLiveChatKnowledgeSource' => IndexLiveChatKnowledgeSourceAction::class,
            'recordLiveChatAIRun' => RecordLiveChatAIRunAction::class,
            'recordLiveChatKnowledgeGap' => RecordLiveChatKnowledgeGapAction::class,
            'requestLiveChatHandoff' => RequestLiveChatHandoffAction::class,
            'searchLiveChatKnowledgeDocuments' => SearchLiveChatKnowledgeDocumentsAction::class,
            'startLiveChatConversation' => StartLiveChatConversationAction::class,
            'storeLiveChatMessage' => StoreLiveChatMessageAction::class,
            'syncLiveChatConversationContact' => SyncLiveChatConversationContactAction::class,
        ])
        ->and(live_chat_string_list($manifest, 'capabilities'))->toContain(
            'live-chat-widget',
            'live-chat-message-first',
            'live-chat-details-first',
            'live-chat-contacts-sync',
            'live-chat-human-handoff',
            'live-chat-file-uploads',
            'live-chat-ai-run-audit',
            'live-chat-knowledge-documents',
            'live-chat-knowledge-gaps',
        )
        ->and($contributionTraceability['deferredContributions'] ?? null)->toBe([]);
});

it('declares committed marketplace assets and screenshot fallbacks', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = live_chat_json_file_array($packagePath . '/capell.json');
    $screenshotContract = live_chat_json_file_array($packagePath . '/docs/screenshots.json');
    $marketplace = live_chat_array($manifest, 'marketplace');

    foreach (live_chat_array_list($marketplace, 'screenshots') as $screenshot) {
        $path = live_chat_string($screenshot, 'path');

        expect($path)->toBeString()
            ->and(File::exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim(live_chat_string($screenshot, 'alt'))))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim(live_chat_string($screenshot, 'caption'))))->toBeGreaterThanOrEqual(12);
    }

    expect($screenshotContract['generatedFor'] ?? null)->toBe('deployment-screenshot-runner')
        ->and(live_chat_string_list($screenshotContract, 'composerRequires'))->toContain('capell-app/live-chat');

    foreach (live_chat_array_list($screenshotContract, 'entries') as $entry) {
        $fallbackAsset = live_chat_string($entry, 'fallbackAsset');

        expect($fallbackAsset)->toBeString()
            ->and(File::exists($packagePath . '/' . str_replace('packages/live-chat/', '', $fallbackAsset)))->toBeTrue();
    }
});

it('checks live chat health dependencies are discoverable', function (): void {
    $this->createLiveChatSite();

    $healthCheck = new LiveChatHealthCheck;

    expect(LiveChatHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($healthCheck->missingTables())->toBe([])
        ->and($healthCheck->unregisteredMorphAliases())->toBe([])
        ->and($healthCheck->unresolvableActions())->toBe([])
        ->and($healthCheck->passes())->toBeTrue();
});
