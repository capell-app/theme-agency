<?php

declare(strict_types=1);

use Capell\PublicActions\Filament\Resources\Destinations\PublicActionDestinationResource;
use Capell\PublicActions\Filament\Resources\DispatchAttempts\PublicActionDispatchAttemptResource;
use Capell\PublicActions\Filament\Resources\IntegrationTokens\Pages\ListPublicActionIntegrationTokens;
use Capell\PublicActions\Filament\Resources\IntegrationTokens\PublicActionIntegrationTokenResource;
use Capell\PublicActions\Filament\Resources\PublicActions\PublicActionResource;
use Capell\PublicActions\Filament\Resources\Submissions\PublicActionSubmissionResource;
use Capell\PublicActions\Health\PublicActionsHealthCheck;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionIntegrationToken;
use Capell\PublicActions\Models\PublicActionSubmission;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

it('declares public action resource forms, models, and pages', function (): void {
    $actionComponents = PublicActionResource::form(Schema::make())->getComponents();
    $destinationComponents = PublicActionDestinationResource::form(Schema::make())->getComponents();

    expect($actionComponents)->toHaveCount(13)
        ->and($actionComponents[0])->toBeInstanceOf(TextInput::class)
        ->and($actionComponents[2])->toBeInstanceOf(Select::class)
        ->and($actionComponents[10])->toBeInstanceOf(Toggle::class)
        ->and($actionComponents[12])->toBeInstanceOf(KeyValue::class)
        ->and($destinationComponents)->toHaveCount(8)
        ->and($destinationComponents[0])->toBeInstanceOf(Select::class)
        ->and($destinationComponents[6])->toBeInstanceOf(KeyValue::class)
        ->and(PublicActionResource::getModel())->toBe(PublicAction::class)
        ->and(PublicActionDestinationResource::getModel())->toBe(PublicActionDestination::class)
        ->and(PublicActionResource::getPages())->toHaveKeys(['index', 'create', 'edit'])
        ->and(PublicActionDestinationResource::getPages())->toHaveKeys(['index', 'create', 'edit']);
});

it('declares read-only public action resource models and pages', function (): void {
    expect(PublicActionSubmissionResource::form(Schema::make())->getComponents())->toBe([])
        ->and(PublicActionDispatchAttemptResource::form(Schema::make())->getComponents())->toBe([])
        ->and(PublicActionIntegrationTokenResource::form(Schema::make())->getComponents())->toBe([])
        ->and(PublicActionSubmissionResource::getModel())->toBe(PublicActionSubmission::class)
        ->and(PublicActionDispatchAttemptResource::getModel())->toBe(PublicActionDispatchAttempt::class)
        ->and(PublicActionIntegrationTokenResource::getModel())->toBe(PublicActionIntegrationToken::class)
        ->and(PublicActionSubmissionResource::getPages())->toHaveKey('index')
        ->and(PublicActionDispatchAttemptResource::getPages())->toHaveKey('index')
        ->and(PublicActionIntegrationTokenResource::getPages())->toHaveKey('index')
        ->and(PublicActionsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('builds the integration token header action schema', function (): void {
    $page = new ListPublicActionIntegrationTokens;
    $actions = Closure::bind(
        static fn (): array => $page->getHeaderActions(),
        null,
        ListPublicActionIntegrationTokens::class,
    )();

    $action = $actions[0] ?? null;

    expect($actions)->toHaveCount(1)
        ->and($action)->toBeInstanceOf(Action::class);

    throw_unless($action instanceof Action, RuntimeException::class, 'Expected integration token header action to be a Filament action.');

    expect($action->getName())->toBe('createToken');
});
