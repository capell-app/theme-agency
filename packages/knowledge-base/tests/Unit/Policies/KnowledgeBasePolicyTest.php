<?php

declare(strict_types=1);

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Support\Utils;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Policies\KnowledgeBaseArticlePolicy;
use Capell\KnowledgeBase\Policies\KnowledgeBaseCollectionPolicy;
use Capell\KnowledgeBase\Tests\Fixtures\Models\KnowledgeBasePolicyTestUser;
use Capell\KnowledgeBase\Tests\KnowledgeBaseTestCase;
use Illuminate\Database\Eloquent\Model;

require_once dirname(__DIR__, 2) . '/KnowledgeBaseTestCase.php';

uses(KnowledgeBaseTestCase::class);

it('denies knowledge base resources to users without shield permissions', function (object $policy, Model $record): void {
    $user = new KnowledgeBasePolicyTestUser;

    expect(knowledgeBasePolicyCall($policy, 'viewAny', $user))->toBeFalse()
        ->and(knowledgeBasePolicyCall($policy, 'create', $user))->toBeFalse()
        ->and(knowledgeBasePolicyCall($policy, 'deleteAny', $user))->toBeFalse()
        ->and(knowledgeBasePolicyCall($policy, 'view', $user, $record))->toBeFalse()
        ->and(knowledgeBasePolicyCall($policy, 'update', $user, $record))->toBeFalse()
        ->and(knowledgeBasePolicyCall($policy, 'delete', $user, $record))->toBeFalse();
})->with([
    'collections' => [new KnowledgeBaseCollectionPolicy, new KnowledgeBaseCollection],
    'articles' => [new KnowledgeBaseArticlePolicy, new KnowledgeBaseArticle],
]);

it('allows knowledge base resources through shield permissions', function (object $policy, Model $record, string $subject): void {
    $user = new KnowledgeBasePolicyTestUser(permissions: [
        knowledgeBasePolicyPermission('view_any', $subject),
        knowledgeBasePolicyPermission('create', $subject),
        knowledgeBasePolicyPermission('update', $subject),
        knowledgeBasePolicyPermission('delete', $subject),
        knowledgeBasePolicyPermission('delete_any', $subject),
    ]);

    expect(knowledgeBasePolicyCall($policy, 'viewAny', $user))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'create', $user))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'deleteAny', $user))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'view', $user, $record))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'update', $user, $record))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'delete', $user, $record))->toBeTrue();
})->with([
    'collections' => [new KnowledgeBaseCollectionPolicy, new KnowledgeBaseCollection, 'KnowledgeBaseCollection'],
    'articles' => [new KnowledgeBaseArticlePolicy, new KnowledgeBaseArticle, 'KnowledgeBaseArticle'],
]);

it('falls back from view any to view permission for knowledge base navigation', function (object $policy, string $subject): void {
    $user = new KnowledgeBasePolicyTestUser(permissions: [
        knowledgeBasePolicyPermission('view', $subject),
    ]);

    expect(knowledgeBasePolicyCall($policy, 'viewAny', $user))->toBeTrue();
})->with([
    'collections' => [new KnowledgeBaseCollectionPolicy, 'KnowledgeBaseCollection'],
    'articles' => [new KnowledgeBaseArticlePolicy, 'KnowledgeBaseArticle'],
]);

it('honours record site scope when a knowledge base record has a site id', function (object $policy, Model $record, string $subject): void {
    $record->forceFill(['site_id' => 10]);
    $assignedUser = new KnowledgeBasePolicyTestUser(
        assignedSiteIds: [10],
        permissions: [knowledgeBasePolicyPermission('update', $subject)],
    );
    $wrongSiteUser = new KnowledgeBasePolicyTestUser(
        assignedSiteIds: [99],
        permissions: [knowledgeBasePolicyPermission('update', $subject)],
    );

    expect(knowledgeBasePolicyCall($policy, 'update', $assignedUser, $record))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'update', $wrongSiteUser, $record))->toBeFalse();
})->with([
    'collections' => [new KnowledgeBaseCollectionPolicy, new KnowledgeBaseCollection, 'KnowledgeBaseCollection'],
    'articles' => [new KnowledgeBaseArticlePolicy, new KnowledgeBaseArticle, 'KnowledgeBaseArticle'],
]);

it('allows global admins to use knowledge base resource policies', function (object $policy, Model $record): void {
    $user = new KnowledgeBasePolicyTestUser(global: true);

    expect(knowledgeBasePolicyCall($policy, 'viewAny', $user))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'create', $user))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'deleteAny', $user))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'view', $user, $record))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'update', $user, $record))->toBeTrue()
        ->and(knowledgeBasePolicyCall($policy, 'delete', $user, $record))->toBeTrue();
})->with([
    'collections' => [new KnowledgeBaseCollectionPolicy, new KnowledgeBaseCollection],
    'articles' => [new KnowledgeBaseArticlePolicy, new KnowledgeBaseArticle],
]);

function knowledgeBasePolicyCall(object $policy, string $method, mixed ...$arguments): bool
{
    $result = (new ReflectionMethod($policy, $method))->invoke($policy, ...$arguments);

    expect($result)->toBeBool();
    throw_unless(is_bool($result), RuntimeException::class, 'Expected policy call to return a boolean.');

    return $result;
}

function knowledgeBasePolicyPermission(string $ability, string $subject): string
{
    $permissions = Utils::getConfig()->permissions;

    return FilamentShield::defaultPermissionKeyBuilder(
        affix: $ability,
        separator: $permissions->separator,
        subject: $subject,
        case: $permissions->case,
    );
}
