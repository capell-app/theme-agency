<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\PublicActions\Actions\CreatePublicActionIntegrationTokenAction;
use Capell\PublicActions\Filament\Resources\Destinations\PublicActionDestinationResource;
use Capell\PublicActions\Filament\Resources\DispatchAttempts\PublicActionDispatchAttemptResource;
use Capell\PublicActions\Filament\Resources\IntegrationTokens\PublicActionIntegrationTokenResource;
use Capell\PublicActions\Filament\Resources\PublicActions\PublicActionResource;
use Capell\PublicActions\Filament\Resources\Submissions\PublicActionSubmissionResource;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionIntegrationToken;
use Capell\PublicActions\Models\PublicActionSubmission;
use Capell\Tests\Fixtures\Models\User;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('requires view permissions for public actions admin resources', function (string $resourceClass, string $permission): void {
    test()->actingAsUser();

    /** @var class-string<resource> $resourceClass */
    expect($resourceClass::canAccess())->toBeFalse()
        ->and($resourceClass::canViewAny())->toBeFalse();

    Permission::findOrCreate($permission);
    test()->actingAs(test()->createUserWithPermission($permission));

    expect($resourceClass::canAccess())->toBeTrue()
        ->and($resourceClass::canViewAny())->toBeTrue();
})->with([
    'public actions' => [PublicActionResource::class, 'ViewAny:PublicAction'],
    'destinations' => [PublicActionDestinationResource::class, 'ViewAny:PublicActionDestination'],
    'submissions' => [PublicActionSubmissionResource::class, 'ViewAny:PublicActionSubmission'],
    'dispatch attempts' => [PublicActionDispatchAttemptResource::class, 'ViewAny:PublicActionDispatchAttempt'],
    'integration tokens' => [PublicActionIntegrationTokenResource::class, 'ViewAny:PublicActionIntegrationToken'],
]);

it('requires update permission to mutate public action integration tokens', function (): void {
    $site = Site::factory()->create();
    $token = PublicActionIntegrationToken::factory()->create(['site_id' => $site->getKey()]);

    Permission::findOrCreate('ViewAny:PublicActionIntegrationToken');
    Permission::findOrCreate('Update:PublicActionIntegrationToken');

    $viewer = test()->createUserWithPermission('ViewAny:PublicActionIntegrationToken');
    assignPublicActionSiteRole($viewer, (int) $site->getKey());
    test()->actingAs($viewer);

    expect(PublicActionIntegrationTokenResource::canEdit($token))->toBeFalse();

    $editor = test()->createUserWithPermission('Update:PublicActionIntegrationToken');
    assignPublicActionSiteRole($editor, (int) $site->getKey());
    test()->actingAs($editor);

    expect(PublicActionIntegrationTokenResource::canEdit($token))->toBeTrue();
});

it('scopes public action operation resources to the actor assigned sites', function (): void {
    $assignedSite = Site::factory()->create();
    $otherSite = Site::factory()->create();
    $assignedAction = PublicAction::factory()->create([
        'site_id' => $assignedSite->getKey(),
        'site_scope_key' => 'site:' . $assignedSite->getKey(),
    ]);
    $otherAction = PublicAction::factory()->create([
        'site_id' => $otherSite->getKey(),
        'site_scope_key' => 'site:' . $otherSite->getKey(),
    ]);
    $assignedDestination = PublicActionDestination::factory()->create(['public_action_id' => $assignedAction->getKey()]);
    $otherDestination = PublicActionDestination::factory()->create(['public_action_id' => $otherAction->getKey()]);
    $assignedSubmission = PublicActionSubmission::factory()->create([
        'public_action_id' => $assignedAction->getKey(),
        'site_id' => $assignedSite->getKey(),
    ]);
    $otherSubmission = PublicActionSubmission::factory()->create([
        'public_action_id' => $otherAction->getKey(),
        'site_id' => $otherSite->getKey(),
    ]);
    $assignedAttempt = PublicActionDispatchAttempt::factory()->create([
        'public_action_submission_id' => $assignedSubmission->getKey(),
        'public_action_destination_id' => $assignedDestination->getKey(),
    ]);
    PublicActionDispatchAttempt::factory()->create([
        'public_action_submission_id' => $otherSubmission->getKey(),
        'public_action_destination_id' => $otherDestination->getKey(),
    ]);
    $assignedToken = PublicActionIntegrationToken::factory()->create(['site_id' => $assignedSite->getKey()]);
    PublicActionIntegrationToken::factory()->create(['site_id' => $otherSite->getKey()]);

    Permission::findOrCreate('ViewAny:PublicActionIntegrationToken');
    Permission::findOrCreate('ViewAny:PublicActionDestination');
    Permission::findOrCreate('ViewAny:PublicActionDispatchAttempt');

    $user = test()->createUserWithPermission([
        'ViewAny:PublicActionIntegrationToken',
        'ViewAny:PublicActionDestination',
        'ViewAny:PublicActionDispatchAttempt',
    ]);
    assignPublicActionSiteRole($user, (int) $assignedSite->getKey());
    test()->actingAs($user);

    expect(PublicActionIntegrationTokenResource::getEloquentQuery()->pluck('id')->all())->toBe([(int) $assignedToken->getKey()])
        ->and(PublicActionDestinationResource::getEloquentQuery()->pluck('id')->all())->toBe([(int) $assignedDestination->getKey()])
        ->and(PublicActionDispatchAttemptResource::getEloquentQuery()->pluck('id')->all())->toBe([(int) $assignedAttempt->getKey()]);
});

it('denies public action policy mutations for records outside the actor assigned sites', function (): void {
    $assignedSite = Site::factory()->create();
    $otherSite = Site::factory()->create();
    $otherToken = PublicActionIntegrationToken::factory()->create(['site_id' => $otherSite->getKey()]);

    Permission::findOrCreate('Update:PublicActionIntegrationToken');

    $user = test()->createUserWithPermission('Update:PublicActionIntegrationToken');
    assignPublicActionSiteRole($user, (int) $assignedSite->getKey());
    test()->actingAs($user);

    expect(PublicActionIntegrationTokenResource::canEdit($otherToken))->toBeFalse();
});

it('normalizes and authorizes public action site scope form data', function (): void {
    $assignedSite = Site::factory()->create();
    $otherSite = Site::factory()->create();

    Permission::findOrCreate('Create:PublicAction');

    $user = test()->createUserWithPermission('Create:PublicAction');
    assignPublicActionSiteRole($user, (int) $assignedSite->getKey());
    test()->actingAs($user);

    expect(PublicActionResource::prepareFormDataForPersistence([
        'site_id' => $assignedSite->getKey(),
        'site_scope_key' => 'global',
    ]))->toMatchArray([
        'site_id' => $assignedSite->getKey(),
        'site_scope_key' => 'site:' . $assignedSite->getKey(),
    ]);

    expect(fn (): array => PublicActionResource::prepareFormDataForPersistence([
        'site_id' => $otherSite->getKey(),
        'site_scope_key' => 'site:' . $assignedSite->getKey(),
    ]))->toThrow(AuthorizationException::class);

    expect(fn (): array => PublicActionResource::prepareFormDataForPersistence([
        'site_id' => null,
        'site_scope_key' => 'global',
    ]))->toThrow(AuthorizationException::class);
});

it('requires site-scoped actors to create integration tokens for assigned sites only', function (): void {
    $assignedSite = Site::factory()->create();
    $otherSite = Site::factory()->create();

    Permission::findOrCreate('Create:PublicActionIntegrationToken');

    $user = test()->createUserWithPermission('Create:PublicActionIntegrationToken');
    assignPublicActionSiteRole($user, (int) $assignedSite->getKey());

    expect(fn (): mixed => CreatePublicActionIntegrationTokenAction::run(
        name: 'Invalid global token',
        actor: $user,
    ))->toThrow(AuthorizationException::class);

    expect(fn (): mixed => CreatePublicActionIntegrationTokenAction::run(
        name: 'Invalid site token',
        siteId: (int) $otherSite->getKey(),
        actor: $user,
    ))->toThrow(AuthorizationException::class);

    $created = CreatePublicActionIntegrationTokenAction::run(
        name: 'Assigned site token',
        siteId: (int) $assignedSite->getKey(),
        actor: $user,
    );

    expect($created->token->site_id)->toBe((int) $assignedSite->getKey());
});

function assignPublicActionSiteRole(User $user, int $siteId): void
{
    $role = Role::findOrCreate('public-actions-site-member');

    DB::table('model_has_roles')->insert([
        'role_id' => $role->getKey(),
        'model_type' => $user->getMorphClass(),
        'model_id' => $user->getKey(),
        'team_id' => $siteId,
    ]);
}
