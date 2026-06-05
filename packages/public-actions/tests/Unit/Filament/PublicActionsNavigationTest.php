<?php

declare(strict_types=1);

use Capell\PublicActions\Filament\Resources\Destinations\PublicActionDestinationResource;
use Capell\PublicActions\Filament\Resources\DispatchAttempts\PublicActionDispatchAttemptResource;
use Capell\PublicActions\Filament\Resources\IntegrationTokens\PublicActionIntegrationTokenResource;
use Capell\PublicActions\Filament\Resources\PublicActions\PublicActionResource;
use Capell\PublicActions\Filament\Resources\Submissions\PublicActionSubmissionResource;

it('keeps public actions contained under system navigation', function (): void {
    $navigationGroup = (string) __('capell-admin::navigation.group_system');
    $parentItem = (string) __('capell-public-actions::filament.navigation_group');

    expect(PublicActionResource::getNavigationGroup())->toBe($navigationGroup)
        ->and(PublicActionResource::getNavigationLabel())->toBe($parentItem)
        ->and(PublicActionResource::getNavigationSort())->toBe(40)
        ->and(PublicActionResource::getNavigationParentItem())->toBeNull()
        ->and(PublicActionSubmissionResource::getNavigationGroup())->toBe($navigationGroup)
        ->and(PublicActionSubmissionResource::getNavigationParentItem())->toBe($parentItem)
        ->and(PublicActionSubmissionResource::getNavigationSort())->toBe(41)
        ->and(PublicActionDestinationResource::getNavigationParentItem())->toBe($parentItem)
        ->and(PublicActionDestinationResource::getNavigationSort())->toBe(42)
        ->and(PublicActionDispatchAttemptResource::getNavigationParentItem())->toBe($parentItem)
        ->and(PublicActionDispatchAttemptResource::getNavigationSort())->toBe(43)
        ->and(PublicActionIntegrationTokenResource::getNavigationParentItem())->toBe($parentItem)
        ->and(PublicActionIntegrationTokenResource::getNavigationSort())->toBe(44);
});
