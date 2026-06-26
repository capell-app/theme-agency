<?php

declare(strict_types=1);

use Capell\SeoSuite\Filament\Actions\AiCreatorAction;
use Filament\Schemas\Components\Wizard;

beforeEach(function (): void {
    test()->registerAndMigrateSettings(
        ['2026_05_10_190871_01_create_ai-orchestrator_settings'],
        dirname(__DIR__, 4) . '/ai-orchestrator/database/settings',
    );
});

it('builds the ai creator wizard form schema', function (): void {
    $action = AiCreatorAction::make();
    $buildWizardForm = new ReflectionMethod(AiCreatorAction::class, 'buildWizardForm');

    $schema = $buildWizardForm->invoke($action);

    expect($action->getName())->toBe('ai-creator')
        ->and($schema)->toHaveCount(2)
        ->and($schema[0]->getName())->toBe('ai_session_id')
        ->and($schema[1])->toBeInstanceOf(Wizard::class);
});
