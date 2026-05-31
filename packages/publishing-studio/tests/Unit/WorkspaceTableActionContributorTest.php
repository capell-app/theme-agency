<?php

declare(strict_types=1);

use Capell\PublishingStudio\Contracts\WorkspaceTableActionContributor;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\Tables\PublishingStudioTable;
use Filament\Actions\Action;

it('defines the workspace table action contributor tag', function (): void {
    expect(WorkspaceTableActionContributor::TAG)
        ->toBe('capell.publishing-studio.table_action_contributors');
});

it('inserts tagged contributor actions after preview and before validate', function (): void {
    $contributorAbstract = WorkspaceTableActionContributor::class . '.test';

    app()->bind($contributorAbstract, fn (): WorkspaceTableActionContributor => new class implements WorkspaceTableActionContributor
    {
        public function actions(): array
        {
            return [
                Action::make('peekless-preview'),
            ];
        }
    });

    app()->tag([$contributorAbstract], WorkspaceTableActionContributor::TAG);

    $tableReflection = new ReflectionClass(PublishingStudioTable::class);
    $recordActionsMethod = $tableReflection->getMethod('getRecordActions');

    $actionNames = capell_test_collect($recordActionsMethod->invoke(null))
        ->filter(fn (mixed $action): bool => is_object($action) && method_exists($action, 'getName'))
        ->map(fn (object $action): string => $action->getName())
        ->values();

    $previewIndex = $actionNames->search('preview');
    $peeklessPreviewIndex = $actionNames->search('peekless-preview');
    $validateIndex = $actionNames->search('validate');

    expect($previewIndex)->toBeInt()
        ->and($peeklessPreviewIndex)->toBeInt()
        ->and($validateIndex)->toBeInt();

    throw_if(! is_int($previewIndex) || ! is_int($peeklessPreviewIndex) || ! is_int($validateIndex), RuntimeException::class, 'Expected preview, peekless preview, and validate actions to be present.');

    expect($actionNames->all())
        ->toContain('preview')
        ->toContain('peekless-preview')
        ->toContain('validate')
        ->and($peeklessPreviewIndex)->toBeGreaterThan($previewIndex)
        ->and($peeklessPreviewIndex)->toBeLessThan($validateIndex);
});
