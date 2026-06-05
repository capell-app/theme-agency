<?php

declare(strict_types=1);

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\PrivacyCenter\Actions\BuildPrivacyCenterOverviewStatsAction;
use Capell\PrivacyCenter\Enums\ConsentDecision;
use Capell\PrivacyCenter\Enums\CookieCategory;
use Capell\PrivacyCenter\Enums\PolicyType;
use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Enums\PrivacyRequestType;
use Capell\PrivacyCenter\Enums\ResourceEnum;
use Capell\PrivacyCenter\Enums\RetentionAction;
use Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\ConsentPolicyResource;
use Capell\PrivacyCenter\Filament\Resources\ConsentRecords\ConsentRecordResource;
use Capell\PrivacyCenter\Filament\Resources\PolicyAcceptances\PolicyAcceptanceResource;
use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\Pages\EditPrivacyRequest;
use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\PrivacyRequestResource;
use Capell\PrivacyCenter\Filament\Resources\RetentionRules\RetentionRuleResource;
use Capell\PrivacyCenter\Filament\Widgets\PrivacyCenterOverviewWidget;
use Capell\PrivacyCenter\Models\ConsentPolicy;
use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Models\PolicyAcceptance;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Models\RetentionRule;
use Capell\PrivacyCenter\Tests\PrivacyCenterTestCase;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

require_once dirname(__DIR__) . '/autoload.php';

uses(PrivacyCenterTestCase::class);

it('declares admin resources for privacy center records', function (): void {
    expect(ResourceEnum::cases())->toHaveCount(5)
        ->and(ConsentPolicyResource::getModel())->toBe(ConsentPolicy::class)
        ->and(ConsentRecordResource::getModel())->toBe(ConsentRecord::class)
        ->and(PolicyAcceptanceResource::getModel())->toBe(PolicyAcceptance::class)
        ->and(PrivacyRequestResource::getModel())->toBe(PrivacyRequest::class)
        ->and(RetentionRuleResource::getModel())->toBe(RetentionRule::class)
        ->and(ConsentPolicyResource::getNavigationLabel())->toBe(__('capell-privacy-center::privacy.admin.resources.consent_policies'))
        ->and(ConsentRecordResource::getNavigationLabel())->toBe(__('capell-privacy-center::privacy.admin.resources.consent_records'))
        ->and(PolicyAcceptanceResource::getNavigationLabel())->toBe(__('capell-privacy-center::privacy.admin.resources.policy_acceptances'))
        ->and(PrivacyRequestResource::getNavigationLabel())->toBe(__('capell-privacy-center::privacy.admin.resources.privacy_requests'))
        ->and(RetentionRuleResource::getNavigationLabel())->toBe(__('capell-privacy-center::privacy.admin.resources.retention_rules'));
});

it('exposes expected admin resource pages', function (): void {
    expect(array_keys(ConsentPolicyResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(ConsentRecordResource::getPages()))->toBe(['index'])
        ->and(array_keys(PolicyAcceptanceResource::getPages()))->toBe(['index'])
        ->and(array_keys(PrivacyRequestResource::getPages()))->toBe(['index', 'edit'])
        ->and(array_keys(RetentionRuleResource::getPages()))->toBe(['index', 'create', 'edit']);
});

it('builds privacy center resource forms with configured fields and enum labels', function (): void {
    expect(privacyCenterAdminFormComponentClasses(ConsentPolicyResource::form(Schema::make())))->toBe([
        TextInput::class,
        TextInput::class,
        TextInput::class,
        TextInput::class,
        Select::class,
        TextInput::class,
        DateTimePicker::class,
        DateTimePicker::class,
        DateTimePicker::class,
        KeyValue::class,
    ])
        ->and(privacyCenterAdminFormComponentClasses(ConsentRecordResource::form(Schema::make())))->toBe([
            TextInput::class,
            TextInput::class,
            TextInput::class,
            TextInput::class,
            Select::class,
            Select::class,
            TextInput::class,
            DateTimePicker::class,
            DateTimePicker::class,
            DateTimePicker::class,
            KeyValue::class,
        ])
        ->and(privacyCenterAdminFormComponentClasses(PolicyAcceptanceResource::form(Schema::make())))->toBe([
            TextInput::class,
            TextInput::class,
            TextInput::class,
            Select::class,
            TextInput::class,
            TextInput::class,
            TextInput::class,
            DateTimePicker::class,
            KeyValue::class,
        ])
        ->and(privacyCenterAdminFormComponentClasses(PrivacyRequestResource::form(Schema::make())))->toBe([
            TextInput::class,
            Select::class,
            Select::class,
            TextInput::class,
            DateTimePicker::class,
            DateTimePicker::class,
            DateTimePicker::class,
            DateTimePicker::class,
            DateTimePicker::class,
            TextInput::class,
            KeyValue::class,
            KeyValue::class,
        ])
        ->and(privacyCenterAdminFormComponentClasses(RetentionRuleResource::form(Schema::make())))->toBe([
            TextInput::class,
            TextInput::class,
            TextInput::class,
            TextInput::class,
            Select::class,
            TextInput::class,
            Toggle::class,
            KeyValue::class,
        ])
        ->and(PolicyType::DataProcessing->getLabel())->toBe(__('capell-privacy-center::privacy.policy_types.data_processing'))
        ->and(CookieCategory::Functional->getLabel())->toBe(__('capell-privacy-center::privacy.cookie_categories.functional'))
        ->and(ConsentDecision::Expired->getLabel())->toBe(__('capell-privacy-center::privacy.consent_decisions.expired'))
        ->and(PrivacyRequestType::Object->getLabel())->toBe(__('capell-privacy-center::privacy.privacy_request_types.object'))
        ->and(PrivacyRequestStatus::Cancelled->getLabel())->toBe(__('capell-privacy-center::privacy.privacy_request_statuses.cancelled'))
        ->and(RetentionAction::Delete->getLabel())->toBe(__('capell-privacy-center::privacy.retention_actions.delete'));
});

it('builds privacy center resource tables with expected columns', function (
    string $resourceClass,
    array $expectedColumnNames,
    array $expectedColumnClasses,
): void {
    $table = $resourceClass::table(privacyCenterAdminTableForCoverage());
    $columns = $table->getColumns();

    expect(array_keys($columns))->toBe($expectedColumnNames)
        ->and(array_map(static fn (object $column): string => $column::class, array_values($columns)))->toBe($expectedColumnClasses);
})->with([
    'consent policies' => [
        ConsentPolicyResource::class,
        ['title', 'key', 'version', 'type', 'published_at', 'retired_at'],
        [TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class],
    ],
    'consent records' => [
        ConsentRecordResource::class,
        ['category', 'decision', 'policy_version', 'jurisdiction', 'decided_at', 'expires_at'],
        [TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class],
    ],
    'policy acceptances' => [
        PolicyAcceptanceResource::class,
        ['policy_key', 'policy_version', 'policy_type', 'context', 'accepted_at'],
        [TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class],
    ],
    'privacy requests' => [
        PrivacyRequestResource::class,
        ['reference', 'type', 'status', 'submitted_at', 'due_at', 'fulfilled_at'],
        [TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class],
    ],
    'retention rules' => [
        RetentionRuleResource::class,
        ['data_domain', 'record_type', 'retention_days', 'action', 'is_active'],
        [TextColumn::class, TextColumn::class, TextColumn::class, TextColumn::class, IconColumn::class],
    ],
]);

it('exposes privacy request edit workflow actions', function (): void {
    $page = new EditPrivacyRequest;

    expect(privacyCenterAdminActionNames(PrivacyRequestResource::privacyRequestWorkflowActions()))->toBe([
        'mark_verified',
        'mark_fulfilled',
        'reject',
    ])->and(privacyCenterAdminActionNames(privacyCenterAdminEditRequestHeaderActions($page)))->toBe([
        'mark_verified',
        'mark_fulfilled',
        'reject',
    ]);
});

it('builds privacy center overview widget stats from package-owned records', function (): void {
    $siteId = $this->createPrivacyCenterSite();
    $now = now();

    ConsentRecord::query()->create([
        'site_id' => $siteId,
        'category' => CookieCategory::Analytics,
        'decision' => ConsentDecision::Granted,
        'decided_at' => $now,
    ]);
    ConsentRecord::query()->create([
        'site_id' => $siteId,
        'category' => CookieCategory::Marketing,
        'decision' => ConsentDecision::Denied,
        'decided_at' => $now,
    ]);
    PrivacyRequest::query()->create([
        'site_id' => $siteId,
        'reference' => 'PR-20260601-0001',
        'type' => PrivacyRequestType::Export,
        'status' => PrivacyRequestStatus::Submitted,
        'submitted_at' => $now,
    ]);
    PrivacyRequest::query()->create([
        'site_id' => $siteId,
        'reference' => 'PR-20260601-0002',
        'type' => PrivacyRequestType::Delete,
        'status' => PrivacyRequestStatus::Fulfilled,
        'submitted_at' => $now,
        'fulfilled_at' => $now,
    ]);
    RetentionRule::query()->create([
        'site_id' => $siteId,
        'data_domain' => 'privacy-center',
        'record_type' => ConsentRecord::class,
        'retention_days' => 365,
        'action' => RetentionAction::Anonymize,
        'is_active' => true,
    ]);
    RetentionRule::query()->create([
        'site_id' => $siteId,
        'data_domain' => 'analytics',
        'record_type' => null,
        'retention_days' => 90,
        'action' => RetentionAction::Review,
        'is_active' => false,
    ]);

    expect(BuildPrivacyCenterOverviewStatsAction::run())->toBe([
        'consent_records' => 2,
        'granted_consents' => 1,
        'open_privacy_requests' => 1,
        'active_retention_rules' => 1,
    ])->and(class_implements(PrivacyCenterOverviewWidget::class))->toContain(CapellWidgetContract::class);
});

/**
 * @param  array<array-key, mixed>  $actions
 * @return array<int, string>
 */
function privacyCenterAdminActionNames(array $actions): array
{
    return collect($actions)
        ->map(fn (mixed $action): string => is_object($action) && method_exists($action, 'getName') ? (string) $action->getName() : '')
        ->filter(fn (string $name): bool => $name !== '')
        ->values()
        ->all();
}

/**
 * @return array<array-key, mixed>
 */
function privacyCenterAdminEditRequestHeaderActions(EditPrivacyRequest $page): array
{
    $method = new ReflectionMethod(EditPrivacyRequest::class, 'getHeaderActions');

    return $method->invoke($page);
}

/**
 * @return list<class-string>
 */
function privacyCenterAdminFormComponentClasses(Schema $schema): array
{
    return array_map(
        static fn (object $component): string => $component::class,
        $schema->getComponents(),
    );
}

function privacyCenterAdminTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}
