<?php

declare(strict_types=1);

use Capell\PublicActions\Data\PublicActionMetadataData;
use Capell\PublicActions\Data\PublicActionPayloadData;
use Capell\PublicActions\Data\PublicActionSubmissionData;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionSubmission;
use Capell\PublicActions\Policies\PublicActionPolicy;
use Capell\PublicActions\Support\PublicActionDestinationAdapterRegistry;
use Capell\PublicActions\Support\PublicActionHandlerRegistry;
use Capell\PublicActions\Tests\Fakes\FakePublicActionDestinationAdapter;
use Capell\PublicActions\Tests\Fakes\FakePublicActionHandler;
use Capell\PublicActions\Tests\Fixtures\PublicActionPolicyTestUser;

it('resolves registered public action handlers from objects and classes', function (): void {
    $registry = new PublicActionHandlerRegistry;
    $objectHandler = new FakePublicActionHandler;

    $registry->register('object', $objectHandler);
    $registry->register('class', FakePublicActionHandler::class);

    $submission = new PublicActionSubmissionData(
        actionKey: 'preview-access',
        payload: new PublicActionPayloadData(['email' => 'person@example.test']),
        metadata: new PublicActionMetadataData(ipHash: 'hash'),
    );

    expect($registry->resolve('object'))->toBe($objectHandler)
        ->and($registry->resolve('class'))->toBeInstanceOf(FakePublicActionHandler::class)
        ->and($registry->resolve('missing'))->toBeNull()
        ->and($registry->resolve('class')?->handle($submission)->message)->toBe('preview-access')
        ->and($registry->all())->toHaveKeys(['object', 'class']);
});

it('rejects invalid public action handlers', function (): void {
    $registry = new PublicActionHandlerRegistry;

    expect(fn (): null => $registry->register('invalid', stdClass::class))
        ->toThrow(InvalidArgumentException::class);
});

it('resolves registered public action destination adapters from objects and classes', function (): void {
    $registry = new PublicActionDestinationAdapterRegistry;
    $objectAdapter = new FakePublicActionDestinationAdapter;

    $registry->register('object', $objectAdapter);
    $registry->register('class', FakePublicActionDestinationAdapter::class);

    $destination = PublicActionDestination::factory()->create(['adapter' => 'http_webhook']);
    $submission = PublicActionSubmission::factory()->create();

    expect($registry->resolve('object'))->toBe($objectAdapter)
        ->and($registry->resolve('class'))->toBeInstanceOf(FakePublicActionDestinationAdapter::class)
        ->and($registry->resolve('missing'))->toBeNull()
        ->and($registry->resolve('class')?->dispatch($destination, $submission)->responseStatus)->toBe(202)
        ->and($registry->all())->toHaveKeys(['object', 'class']);
});

it('rejects invalid public action destination adapters', function (): void {
    $registry = new PublicActionDestinationAdapterRegistry;

    expect(fn (): null => $registry->register('invalid', stdClass::class))
        ->toThrow(InvalidArgumentException::class);
});

it('binds registries in the container', function (): void {
    expect(resolve(PublicActionHandlerRegistry::class))->toBeInstanceOf(PublicActionHandlerRegistry::class)
        ->and(resolve(PublicActionDestinationAdapterRegistry::class))->toBeInstanceOf(PublicActionDestinationAdapterRegistry::class)
        ->and(resolve(PublicActionHandlerRegistry::class))->toBe(resolve(PublicActionHandlerRegistry::class))
        ->and(resolve(PublicActionDestinationAdapterRegistry::class))->toBe(resolve(PublicActionDestinationAdapterRegistry::class));
});

it('authorizes public action resources by permissions and assigned site scope', function (): void {
    $policy = new PublicActionPolicy;
    $assignedUser = new PublicActionPolicyTestUser(
        permissions: ['View:PublicAction', 'Update:PublicAction', 'Delete:PublicAction'],
        assignedSiteIds: [7],
    );
    $superAdmin = new PublicActionPolicyTestUser(superAdmin: true);
    $unassignedUser = new PublicActionPolicyTestUser(
        permissions: ['View:PublicAction', 'Update:PublicAction'],
        assignedSiteIds: [9],
    );

    $siteAction = new PublicAction(['site_id' => 7]);
    $otherSiteAction = new PublicAction(['site_id' => 11]);

    expect($policy->viewAny($assignedUser))->toBeTrue()
        ->and($policy->view($assignedUser, $siteAction))->toBeTrue()
        ->and($policy->update($assignedUser, $siteAction))->toBeTrue()
        ->and($policy->delete($assignedUser, $siteAction))->toBeTrue()
        ->and($policy->view($unassignedUser, $siteAction))->toBeFalse()
        ->and($policy->update($unassignedUser, $otherSiteAction))->toBeFalse()
        ->and($policy->view($superAdmin, $otherSiteAction))->toBeTrue()
        ->and($policy->forceDeleteAny($superAdmin))->toBeTrue();
});

it('resolves public action policy site scope from related action records', function (): void {
    $policy = new PublicActionPolicy;
    $user = new PublicActionPolicyTestUser(
        permissions: ['View:PublicAction', 'Restore:PublicAction', 'ForceDelete:PublicAction'],
        assignedSiteIds: [12],
    );
    $submission = new PublicActionSubmission;
    $submission->setRelation('action', new PublicAction(['site_id' => '12']));

    $destination = new PublicActionDestination;
    $destination->setRelation('action', new PublicAction(['site_id' => 12]));

    $dispatchAttempt = new PublicActionDestination;
    $dispatchAttempt->setRelation('destination', $destination);

    expect($policy->view($user, $submission))->toBeTrue()
        ->and($policy->restore($user, $destination))->toBeTrue()
        ->and($policy->forceDelete($user, $dispatchAttempt))->toBeTrue()
        ->and($policy->create(new PublicActionPolicyTestUser))->toBeFalse();
});
