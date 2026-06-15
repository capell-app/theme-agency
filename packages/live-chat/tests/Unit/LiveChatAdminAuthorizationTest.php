<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\LiveChat\Enums\EscalationTriggerType;
use Capell\LiveChat\Enums\KnowledgeSourceStatus;
use Capell\LiveChat\Enums\KnowledgeSourceType;
use Capell\LiveChat\Enums\LiveChatPriority;
use Capell\LiveChat\Filament\Resources\AvailabilityWindows\AvailabilityWindowResource;
use Capell\LiveChat\Filament\Resources\Conversations\ConversationResource;
use Capell\LiveChat\Filament\Resources\EscalationRules\EscalationRuleResource;
use Capell\LiveChat\Filament\Resources\Installations\InstallationResource;
use Capell\LiveChat\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;
use Capell\LiveChat\Models\LiveChatAIRun;
use Capell\LiveChat\Models\LiveChatAvailabilityException;
use Capell\LiveChat\Models\LiveChatAvailabilityWindow;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatEscalationRule;
use Capell\LiveChat\Models\LiveChatInstallation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatKnowledgeGap;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Capell\LiveChat\Policies\AbstractLiveChatResourcePolicy;
use Capell\LiveChat\Policies\LiveChatAIRunPolicy;
use Capell\LiveChat\Policies\LiveChatAvailabilityExceptionPolicy;
use Capell\LiveChat\Policies\LiveChatAvailabilityWindowPolicy;
use Capell\LiveChat\Policies\LiveChatConversationPolicy;
use Capell\LiveChat\Policies\LiveChatEscalationRulePolicy;
use Capell\LiveChat\Policies\LiveChatInstallationPolicy;
use Capell\LiveChat\Policies\LiveChatKnowledgeDocumentPolicy;
use Capell\LiveChat\Policies\LiveChatKnowledgeGapPolicy;
use Capell\LiveChat\Policies\LiveChatKnowledgeSourcePolicy;
use Capell\LiveChat\Tests\Fixtures\LiveChatPolicyTestUser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

/**
 * @param  list<string>  $permissions
 * @param  list<string>  $roles
 * @param  SupportCollection<int, int>|null  $assignedSiteIds
 */
function liveChatAdminActor(array $permissions = [], array $roles = [], ?SupportCollection $assignedSiteIds = null): LiveChatPolicyTestUser
{
    $siteIds = $assignedSiteIds === null
        ? [10]
        : array_values($assignedSiteIds->all());

    return new LiveChatPolicyTestUser(
        permissions: $permissions,
        assignedSiteIds: $siteIds,
        isGlobal: in_array('super_admin', $roles, true),
    );
}

function liveChatPolicyRecord(string $modelClass, int $siteId): Model
{
    if (! is_a($modelClass, Model::class, true)) {
        throw new RuntimeException('Live chat policy test records must be Eloquent models.');
    }

    $record = new $modelClass;

    if ($record instanceof LiveChatAIRun) {
        return $record->setRelation('installation', new LiveChatInstallation(['site_id' => $siteId]));
    }

    if ($record instanceof LiveChatKnowledgeGap) {
        return $record->setRelation('conversation', new LiveChatConversation(['site_id' => $siteId]));
    }

    $record->setAttribute('site_id', $siteId);

    return $record;
}

function liveChatModelKey(Model $model): int
{
    $modelKey = $model->getKey();

    if (is_int($modelKey)) {
        return $modelKey;
    }

    if (is_string($modelKey) && ctype_digit($modelKey)) {
        return (int) $modelKey;
    }

    throw new RuntimeException('Live chat admin authorization tests require integer model keys.');
}

function liveChatShieldPermission(string $ability, string $subject): string
{
    $method = new ReflectionMethod(AbstractLiveChatResourcePolicy::class, 'permission');
    $method->setAccessible(true);

    $permission = $method->invoke(null, $ability, $subject);

    if (! is_string($permission)) {
        throw new RuntimeException('Live chat policy permission resolver returned a non-string value.');
    }

    return $permission;
}

it('requires explicit permissions for live chat admin resources', function (string $policyClass, string $modelClass, string $subject): void {
    /** @var AbstractLiveChatResourcePolicy $policy */
    $policy = new $policyClass;
    $record = liveChatPolicyRecord($modelClass, 10);

    expect($policy->viewAny(liveChatAdminActor()))->toBeFalse()
        ->and($policy->viewAny(liveChatAdminActor([liveChatShieldPermission('view_any', $subject)])))->toBeTrue()
        ->and($policy->viewAny(liveChatAdminActor([liveChatShieldPermission('view', $subject)])))->toBeTrue()
        ->and($policy->update(liveChatAdminActor([liveChatShieldPermission('view_any', $subject)]), $record))->toBeFalse()
        ->and($policy->update(liveChatAdminActor([liveChatShieldPermission('update', $subject)]), $record))->toBeTrue();
})->with([
    'AI runs' => [LiveChatAIRunPolicy::class, LiveChatAIRun::class, 'LiveChatAIRun'],
    'availability exceptions' => [LiveChatAvailabilityExceptionPolicy::class, LiveChatAvailabilityException::class, 'LiveChatAvailabilityException'],
    'availability windows' => [LiveChatAvailabilityWindowPolicy::class, LiveChatAvailabilityWindow::class, 'LiveChatAvailabilityWindow'],
    'conversations' => [LiveChatConversationPolicy::class, LiveChatConversation::class, 'LiveChatConversation'],
    'escalation rules' => [LiveChatEscalationRulePolicy::class, LiveChatEscalationRule::class, 'LiveChatEscalationRule'],
    'installations' => [LiveChatInstallationPolicy::class, LiveChatInstallation::class, 'LiveChatInstallation'],
    'knowledge documents' => [LiveChatKnowledgeDocumentPolicy::class, LiveChatKnowledgeDocument::class, 'LiveChatKnowledgeDocument'],
    'knowledge gaps' => [LiveChatKnowledgeGapPolicy::class, LiveChatKnowledgeGap::class, 'LiveChatKnowledgeGap'],
    'knowledge sources' => [LiveChatKnowledgeSourcePolicy::class, LiveChatKnowledgeSource::class, 'LiveChatKnowledgeSource'],
]);

it('denies live chat record mutations outside the actor assigned sites', function (string $policyClass, string $modelClass, string $subject): void {
    /** @var AbstractLiveChatResourcePolicy $policy */
    $policy = new $policyClass;
    $hiddenRecord = liveChatPolicyRecord($modelClass, 20);

    expect($policy->view(liveChatAdminActor([liveChatShieldPermission('view_any', $subject)], assignedSiteIds: collect([10])), $hiddenRecord))->toBeFalse()
        ->and($policy->update(liveChatAdminActor([liveChatShieldPermission('update', $subject)], assignedSiteIds: collect([10])), $hiddenRecord))->toBeFalse()
        ->and($policy->update(liveChatAdminActor(roles: ['super_admin'], assignedSiteIds: collect([])), $hiddenRecord))->toBeTrue();
})->with([
    'AI runs' => [LiveChatAIRunPolicy::class, LiveChatAIRun::class, 'LiveChatAIRun'],
    'availability exceptions' => [LiveChatAvailabilityExceptionPolicy::class, LiveChatAvailabilityException::class, 'LiveChatAvailabilityException'],
    'availability windows' => [LiveChatAvailabilityWindowPolicy::class, LiveChatAvailabilityWindow::class, 'LiveChatAvailabilityWindow'],
    'conversations' => [LiveChatConversationPolicy::class, LiveChatConversation::class, 'LiveChatConversation'],
    'escalation rules' => [LiveChatEscalationRulePolicy::class, LiveChatEscalationRule::class, 'LiveChatEscalationRule'],
    'installations' => [LiveChatInstallationPolicy::class, LiveChatInstallation::class, 'LiveChatInstallation'],
    'knowledge documents' => [LiveChatKnowledgeDocumentPolicy::class, LiveChatKnowledgeDocument::class, 'LiveChatKnowledgeDocument'],
    'knowledge gaps' => [LiveChatKnowledgeGapPolicy::class, LiveChatKnowledgeGap::class, 'LiveChatKnowledgeGap'],
    'knowledge sources' => [LiveChatKnowledgeSourcePolicy::class, LiveChatKnowledgeSource::class, 'LiveChatKnowledgeSource'],
]);

it('keeps live chat conversations read-only for admin users', function (): void {
    $policy = new LiveChatConversationPolicy;
    $conversation = new LiveChatConversation(['site_id' => 10]);
    $actor = liveChatAdminActor([
        liveChatShieldPermission('create', 'LiveChatConversation'),
        liveChatShieldPermission('delete', 'LiveChatConversation'),
    ]);

    expect($policy->create($actor))->toBeFalse()
        ->and($policy->delete($actor, $conversation))->toBeFalse();
});

it('scopes live chat admin resources and form persistence to assigned sites', function (): void {
    DB::table('sites')->insert([
        ['id' => 10],
        ['id' => 20],
    ]);

    $assignedInstallation = $this->createLiveChatInstallation(siteId: 10);
    $this->createLiveChatInstallation(siteId: 20);

    $assignedConversation = LiveChatConversation::query()->create([
        'site_id' => 10,
        'locale' => 'en',
        'timezone' => 'Europe/London',
    ]);
    LiveChatConversation::query()->create([
        'site_id' => 20,
        'locale' => 'en',
        'timezone' => 'Europe/London',
    ]);

    $assignedWindow = LiveChatAvailabilityWindow::query()->create([
        'site_id' => 10,
        'day_of_week' => 1,
        'opens_at' => '09:00',
        'closes_at' => '17:00',
        'timezone' => 'Europe/London',
        'is_active' => true,
    ]);
    LiveChatAvailabilityWindow::query()->create([
        'site_id' => 20,
        'day_of_week' => 1,
        'opens_at' => '09:00',
        'closes_at' => '17:00',
        'timezone' => 'Europe/London',
        'is_active' => true,
    ]);

    $assignedRule = LiveChatEscalationRule::query()->create([
        'site_id' => 10,
        'name' => 'Assigned rule',
        'trigger_type' => EscalationTriggerType::Keyword,
        'trigger_value' => 'pricing',
        'priority' => LiveChatPriority::High,
        'is_active' => true,
    ]);
    LiveChatEscalationRule::query()->create([
        'site_id' => 20,
        'name' => 'Other rule',
        'trigger_type' => EscalationTriggerType::Keyword,
        'trigger_value' => 'pricing',
        'priority' => LiveChatPriority::High,
        'is_active' => true,
    ]);

    $assignedSource = LiveChatKnowledgeSource::query()->create([
        'site_id' => 10,
        'type' => KnowledgeSourceType::Website,
        'source_key' => 'assigned',
        'title' => 'Assigned',
        'status' => KnowledgeSourceStatus::Active,
    ]);
    LiveChatKnowledgeSource::query()->create([
        'site_id' => 20,
        'type' => KnowledgeSourceType::Website,
        'source_key' => 'other',
        'title' => 'Other',
        'status' => KnowledgeSourceStatus::Active,
    ]);

    auth()->setUser(liveChatAdminActor(assignedSiteIds: collect([10])));

    expect(InstallationResource::getEloquentQuery()->pluck('id')->all())->toBe([liveChatModelKey($assignedInstallation)])
        ->and(ConversationResource::getEloquentQuery()->pluck('id')->all())->toBe([liveChatModelKey($assignedConversation)])
        ->and(AvailabilityWindowResource::getEloquentQuery()->pluck('id')->all())->toBe([liveChatModelKey($assignedWindow)])
        ->and(EscalationRuleResource::getEloquentQuery()->pluck('id')->all())->toBe([liveChatModelKey($assignedRule)])
        ->and(KnowledgeSourceResource::getEloquentQuery()->pluck('id')->all())->toBe([liveChatModelKey($assignedSource)]);

    expect(InstallationResource::prepareFormDataForPersistence(['site_id' => 10]))->toMatchArray(['site_id' => 10])
        ->and(fn (): array => InstallationResource::prepareFormDataForPersistence(['site_id' => 20]))->toThrow(AuthorizationException::class)
        ->and(fn (): array => EscalationRuleResource::prepareFormDataForPersistence(['site_id' => null]))->toThrow(AuthorizationException::class);

    auth()->setUser(liveChatAdminActor(roles: ['super_admin'], assignedSiteIds: collect([])));

    expect(EscalationRuleResource::prepareFormDataForPersistence(['site_id' => null]))->toMatchArray(['site_id' => null]);
});
