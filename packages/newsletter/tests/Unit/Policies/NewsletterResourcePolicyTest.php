<?php

declare(strict_types=1);

use Capell\Newsletter\Filament\Resources\ProviderConnections\Pages\EditProviderConnection;
use Capell\Newsletter\Models\FormMapping;
use Capell\Newsletter\Models\ImportBatch;
use Capell\Newsletter\Models\ProviderAudience;
use Capell\Newsletter\Models\ProviderConnection;
use Capell\Newsletter\Models\ProviderInterestMapping;
use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Models\SyncAttempt;
use Capell\Newsletter\Policies\FormMappingPolicy;
use Capell\Newsletter\Policies\ImportBatchPolicy;
use Capell\Newsletter\Policies\ProviderAudiencePolicy;
use Capell\Newsletter\Policies\ProviderConnectionPolicy;
use Capell\Newsletter\Policies\ProviderInterestMappingPolicy;
use Capell\Newsletter\Policies\SegmentPolicy;
use Capell\Newsletter\Policies\SubscriberPolicy;
use Capell\Newsletter\Policies\SyncAttemptPolicy;
use Capell\Newsletter\Support\NewsletterAdminAccess;
use Capell\Newsletter\Tests\Fixtures\NewsletterPolicyTestUser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

it('registers explicit policies for newsletter-owned admin models', function (): void {
    expect(Gate::getPolicyFor(FormMapping::class))->toBeInstanceOf(FormMappingPolicy::class)
        ->and(Gate::getPolicyFor(ImportBatch::class))->toBeInstanceOf(ImportBatchPolicy::class)
        ->and(Gate::getPolicyFor(ProviderAudience::class))->toBeInstanceOf(ProviderAudiencePolicy::class)
        ->and(Gate::getPolicyFor(ProviderConnection::class))->toBeInstanceOf(ProviderConnectionPolicy::class)
        ->and(Gate::getPolicyFor(ProviderInterestMapping::class))->toBeInstanceOf(ProviderInterestMappingPolicy::class)
        ->and(Gate::getPolicyFor(Segment::class))->toBeInstanceOf(SegmentPolicy::class)
        ->and(Gate::getPolicyFor(Subscriber::class))->toBeInstanceOf(SubscriberPolicy::class)
        ->and(Gate::getPolicyFor(SyncAttempt::class))->toBeInstanceOf(SyncAttemptPolicy::class);
});

it('enforces permissions and resolved site scope for newsletter policies', function (object $policy, Model $record): void {
    $globalUser = new NewsletterPolicyTestUser(global: true);
    $scopedUser = new NewsletterPolicyTestUser(assignedSiteIds: [10], permissionResult: true);
    $wrongSiteUser = new NewsletterPolicyTestUser(assignedSiteIds: [99], permissionResult: true);
    $deniedUser = new NewsletterPolicyTestUser(assignedSiteIds: [10], permissionResult: false);

    expect(newsletterPolicyCall($policy, 'viewAny', $globalUser))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'create', $globalUser))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'deleteAny', $globalUser))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'restoreAny', $globalUser))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'forceDeleteAny', $globalUser))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'reorder', $globalUser))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'view', $scopedUser, $record))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'update', $scopedUser, $record))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'delete', $scopedUser, $record))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'restore', $scopedUser, $record))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'forceDelete', $scopedUser, $record))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'replicate', $scopedUser, $record))->toBeTrue()
        ->and(newsletterPolicyCall($policy, 'create', $deniedUser))->toBeFalse()
        ->and(newsletterPolicyCall($policy, 'view', $wrongSiteUser, $record))->toBeFalse()
        ->and(newsletterPolicyCall($policy, 'update', $wrongSiteUser, $record))->toBeFalse();
})->with([
    'form mapping' => [new FormMappingPolicy, newsletterPolicyModel(new FormMapping)],
    'import batch' => [new ImportBatchPolicy, newsletterPolicyModel(new ImportBatch)],
    'provider connection' => [new ProviderConnectionPolicy, newsletterPolicyProviderConnection()],
    'provider audience' => [new ProviderAudiencePolicy, newsletterPolicyProviderAudience()],
    'provider interest mapping' => [new ProviderInterestMappingPolicy, newsletterPolicyProviderInterestMapping()],
    'segment' => [new SegmentPolicy, newsletterPolicyModel(new Segment)],
    'subscriber' => [new SubscriberPolicy, newsletterPolicyModel(new Subscriber)],
    'sync attempt' => [new SyncAttemptPolicy, newsletterPolicySyncAttempt()],
]);

it('rejects tampered admin site ids for newsletter bulk actions', function (): void {
    $assignedSite = $this->createNewsletterSite('Assigned newsletter site');
    $otherSite = $this->createNewsletterSite('Other newsletter site');

    $this->actingAs(new NewsletterPolicyTestUser(assignedSiteIds: [(int) $assignedSite->getKey()], permissionResult: true));

    NewsletterAdminAccess::authorizeSiteId((int) $assignedSite->getKey());

    expect(fn () => NewsletterAdminAccess::authorizeSiteId((int) $otherSite->getKey()))
        ->toThrow(AuthorizationException::class);
});

it('does not hydrate provider credentials into the edit form and preserves them when left blank', function (): void {
    $page = new EditProviderConnection;

    $fill = (new ReflectionMethod($page, 'mutateFormDataBeforeFill'))->invoke($page, [
        'name' => 'Mailchimp',
        'credentials' => ['api_key' => 'secret-us1'],
    ]);

    $blankSave = (new ReflectionMethod($page, 'mutateFormDataBeforeSave'))->invoke($page, [
        'name' => 'Mailchimp',
        'credentials' => [],
    ]);

    $changedSave = (new ReflectionMethod($page, 'mutateFormDataBeforeSave'))->invoke($page, [
        'name' => 'Mailchimp',
        'credentials' => ['api_key' => 'replacement-us1'],
    ]);

    expect($fill['credentials'])->toBe([])
        ->and($blankSave)->not->toHaveKey('credentials')
        ->and($changedSave['credentials'])->toBe(['api_key' => 'replacement-us1']);
});

function newsletterPolicyCall(object $policy, string $method, mixed ...$arguments): bool
{
    $result = (new ReflectionMethod($policy, $method))->invoke($policy, ...$arguments);

    expect($result)->toBeBool();

    return $result;
}

function newsletterPolicyModel(Model $record): Model
{
    return $record->forceFill(['site_id' => 10]);
}

function newsletterPolicyProviderConnection(): ProviderConnection
{
    return (new ProviderConnection)->forceFill(['site_id' => 10]);
}

function newsletterPolicyProviderAudience(): ProviderAudience
{
    $audience = new ProviderAudience;
    $audience->setRelation('providerConnection', newsletterPolicyProviderConnection());

    return $audience;
}

function newsletterPolicyProviderInterestMapping(): ProviderInterestMapping
{
    $mapping = new ProviderInterestMapping;
    $mapping->setRelation('providerAudience', newsletterPolicyProviderAudience());

    return $mapping;
}

function newsletterPolicySyncAttempt(): SyncAttempt
{
    $attempt = new SyncAttempt;
    $attempt->setRelation('providerConnection', newsletterPolicyProviderConnection());

    return $attempt;
}
