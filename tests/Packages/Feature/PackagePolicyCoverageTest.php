<?php

declare(strict_types=1);

use Capell\Address\Models\Address;
use Capell\Address\Models\Country;
use Capell\Address\Policies\AddressPolicy;
use Capell\Address\Policies\CountryPolicy;
use Capell\Blog\Models\Article;
use Capell\Blog\Policies\ArticlePolicy;
use Capell\ContentSections\Models\Section;
use Capell\ContentSections\Policies\SectionPolicy;
use Capell\Core\Models\Site;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventVenue;
use Capell\Events\Policies\EventPolicy;
use Capell\Events\Policies\EventVenuePolicy;
use Capell\Tags\Models\Tag;
use Capell\Tags\Policies\TagPolicy;
use Capell\Tests\Packages\Fixtures\PackagePolicyCoverageUser;
use Illuminate\Database\Eloquent\Model;

it('enforces permissions and site scope for package content policies', function (object $policy, Model $record): void {
    $record->forceFill(['site_id' => 10]);

    if ($record instanceof Address) {
        $record->setRelation('sites', collect([
            Site::factory()->make(['id' => 10]),
        ]));
    }

    $globalUser = new PackagePolicyCoverageUser(global: true);
    $scopedUser = new PackagePolicyCoverageUser(assignedSiteIds: [10], permissionResult: true);
    $wrongSiteUser = new PackagePolicyCoverageUser(assignedSiteIds: [99], permissionResult: true);
    $deniedUser = new PackagePolicyCoverageUser(assignedSiteIds: [10], permissionResult: false);

    expect(packagePolicyCoverageCall($policy, 'viewAny', $globalUser))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'create', $globalUser))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'deleteAny', $globalUser))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'restoreAny', $globalUser))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'forceDeleteAny', $globalUser))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'reorder', $globalUser))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'view', $scopedUser, $record))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'update', $scopedUser, $record))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'delete', $scopedUser, $record))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'restore', $scopedUser, $record))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'forceDelete', $scopedUser, $record))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'replicate', $scopedUser, $record))->toBeTrue()
        ->and(packagePolicyCoverageCall($policy, 'create', $deniedUser))->toBeFalse();

    if ($policy instanceof CountryPolicy) {
        expect(packagePolicyCoverageCall($policy, 'view', $wrongSiteUser, $record))->toBeTrue();

        return;
    }

    expect(packagePolicyCoverageCall($policy, 'view', $wrongSiteUser, $record))->toBeFalse()
        ->and(packagePolicyCoverageCall($policy, 'update', $wrongSiteUser, $record))->toBeFalse();
})->with([
    'article' => [new ArticlePolicy, new Article],
    'section' => [new SectionPolicy, new Section],
    'tag' => [new TagPolicy, new Tag],
    'address' => [new AddressPolicy, new Address],
    'country' => [new CountryPolicy, new Country],
    'event' => [new EventPolicy, new Event],
    'event venue' => [new EventVenuePolicy, new EventVenue],
]);

function packagePolicyCoverageCall(object $policy, string $method, mixed ...$arguments): bool
{
    $result = (new ReflectionMethod($policy, $method))->invoke($policy, ...$arguments);

    throw_unless(is_bool($result), RuntimeException::class, sprintf('Expected %s::%s() to return bool.', $policy::class, $method));

    return $result;
}
