<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Core\Contracts\Extensions\RegistersExtensionFrontendComponent;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\LiveChat\Actions\BuildLiveChatAnalyticsAction;
use Capell\LiveChat\Actions\BuildLiveChatTranscriptAction;
use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Capell\LiveChat\Actions\CloseLiveChatConversationAction;
use Capell\LiveChat\Actions\RequestLiveChatHandoffAction;
use Capell\LiveChat\Actions\StartLiveChatConversationAction;
use Capell\LiveChat\Actions\StoreLiveChatMessageAction;
use Capell\LiveChat\Actions\SyncLiveChatConversationContactAction;
use Capell\LiveChat\Filament\Resources\AvailabilityWindows\AvailabilityWindowResource;
use Capell\LiveChat\Filament\Resources\Conversations\ConversationResource;
use Capell\LiveChat\Filament\Resources\EscalationRules\EscalationRuleResource;
use Capell\LiveChat\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;
use Capell\LiveChat\Health\LiveChatHealthCheck;
use Capell\LiveChat\Manifest\LiveChatAdminResourcesContribution;
use Capell\LiveChat\Manifest\LiveChatFrontendRoutesContribution;
use Capell\LiveChat\Manifest\LiveChatModelsContribution;
use Capell\LiveChat\Manifest\LiveChatWidgetContribution;
use Capell\LiveChat\Providers\LiveChatServiceProvider;
use Illuminate\Support\Facades\File;

it('declares the live chat package manifest contract', function (): void {
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
    $composer = json_decode(
        File::get(__DIR__ . '/../../composer.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
    $contributions = collect($manifest['contributes']);
    $composerRequirements = array_values(array_filter(
        array_keys($composer['require'] ?? []),
        static fn (int|string $packageName): bool => is_string($packageName) && str_starts_with($packageName, 'capell-app/'),
    ));
    $manifestRequirements = $manifest['dependencies']['requires'] ?? [];

    sort($composerRequirements);
    sort($manifestRequirements);

    expect($manifest)
        ->toHaveKey('manifest-version', 3)
        ->toHaveKey('name', 'capell-app/live-chat')
        ->toHaveKey('namespace', 'Capell\\LiveChat')
        ->and($manifestRequirements)->toBe($composerRequirements)
        ->and($manifest['providers']['runtime'])->toContain(LiveChatServiceProvider::class)
        ->and($manifest['database']['migrations'])->toBeTrue()
        ->and($manifest['database']['requiredTables'])->toBe([
            'live_chat_conversations',
            'live_chat_messages',
            'live_chat_availability_windows',
            'live_chat_availability_exceptions',
            'live_chat_escalation_rules',
            'live_chat_knowledge_sources',
        ])
        ->and($contributions->contains(static fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === LiveChatAdminResourcesContribution::class
            && ($contribution['resourceClasses'] ?? []) === [
                ConversationResource::class,
                AvailabilityWindowResource::class,
                EscalationRuleResource::class,
                KnowledgeSourceResource::class,
            ]))->toBeTrue()
        ->and($manifest['contributes'])->toContain([
            'type' => 'model',
            'class' => LiveChatModelsContribution::class,
            'modelClasses' => [
                'Capell\\LiveChat\\Models\\LiveChatConversation',
                'Capell\\LiveChat\\Models\\LiveChatMessage',
                'Capell\\LiveChat\\Models\\LiveChatAvailabilityWindow',
                'Capell\\LiveChat\\Models\\LiveChatAvailabilityException',
                'Capell\\LiveChat\\Models\\LiveChatEscalationRule',
                'Capell\\LiveChat\\Models\\LiveChatKnowledgeSource',
            ],
        ])
        ->and(class_implements(LiveChatFrontendRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(LiveChatWidgetContribution::class))->toContain(RegistersExtensionFrontendComponent::class)
        ->and($manifest['actions'])->toMatchArray([
            'buildLiveChatAnalytics' => BuildLiveChatAnalyticsAction::class,
            'buildLiveChatTranscript' => BuildLiveChatTranscriptAction::class,
            'buildLiveChatWidgetConfig' => BuildLiveChatWidgetConfigAction::class,
            'closeLiveChatConversation' => CloseLiveChatConversationAction::class,
            'requestLiveChatHandoff' => RequestLiveChatHandoffAction::class,
            'startLiveChatConversation' => StartLiveChatConversationAction::class,
            'storeLiveChatMessage' => StoreLiveChatMessageAction::class,
            'syncLiveChatConversationContact' => SyncLiveChatConversationContactAction::class,
        ])
        ->and($manifest['capabilities'])->toContain(
            'live-chat-widget',
            'live-chat-message-first',
            'live-chat-details-first',
            'live-chat-contacts-sync',
            'live-chat-human-handoff',
            'live-chat-file-uploads',
        )
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([]);
});

it('declares committed marketplace assets and screenshot fallbacks', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = json_decode(File::get($packagePath . '/capell.json'), true, flags: JSON_THROW_ON_ERROR);
    $screenshotContract = json_decode(File::get($packagePath . '/docs/screenshots.json'), true, flags: JSON_THROW_ON_ERROR);

    foreach ($manifest['marketplace']['screenshots'] as $screenshot) {
        expect($screenshot['path'])->toBeString()
            ->and(File::exists($packagePath . '/' . $screenshot['path']))->toBeTrue()
            ->and(strlen(trim((string) $screenshot['alt'])))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim((string) $screenshot['caption'])))->toBeGreaterThanOrEqual(12);
    }

    expect($screenshotContract['generatedFor'])->toBe('deployment-screenshot-runner')
        ->and($screenshotContract['composerRequires'])->toContain('capell-app/live-chat');

    foreach ($screenshotContract['entries'] as $entry) {
        expect($entry['fallbackAsset'])->toBeString()
            ->and(File::exists($packagePath . '/' . str_replace('packages/live-chat/', '', $entry['fallbackAsset'])))->toBeTrue();
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
