<?php

declare(strict_types=1);

use Capell\DocumentLifecycle\Filament\Resources\Documents\DocumentResource;

it('keeps controlled documents under workspace navigation', function (): void {
    expect(DocumentResource::getNavigationGroup())->toBe((string) __('capell-admin::navigation.group_workflow'))
        ->and(DocumentResource::getNavigationSort())->toBe(50);
});
