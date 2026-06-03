<?php

declare(strict_types=1);

use Capell\Core\Contracts\Media\MediaFieldFactory;
use Capell\Hero\Filament\Components\Forms\HeroBackgroundSchema;
use Capell\Hero\Filament\Extenders\HeroBackgroundThemeSchemaExtender;
use Capell\Hero\Filament\Extenders\HeroBackgroundWidgetAssetSchemaExtender;
use Capell\Hero\Filament\Extenders\HeroBackgroundWidgetSchemaExtender;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

test('theme background media fields can be built with a non spatie media factory', function (): void {
    app()->bind(MediaFieldFactory::class, static fn (): MediaFieldFactory => new class implements MediaFieldFactory
    {
        public function make(string $name): TextInput
        {
            return TextInput::make($name . '_id');
        }
    });

    $components = HeroBackgroundSchema::theme();

    expect($components)->toHaveCount(3);
});

it('builds theme widget and asset hero schema groups', function (): void {
    expect(HeroBackgroundSchema::theme())
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Fieldset::class)
        ->and(HeroBackgroundSchema::widget())
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Fieldset::class)
        ->and(HeroBackgroundSchema::asset())
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Fieldset::class);
});

it('appends hero schema components through registered extenders', function (): void {
    $schema = Schema::make();
    $baseComponents = [TextInput::make('existing')];

    expect((new HeroBackgroundThemeSchemaExtender)->extendSettingsComponents($schema, $baseComponents))
        ->toHaveCount(4)
        ->and((new HeroBackgroundWidgetSchemaExtender)->extendDisplayComponents($schema, $baseComponents))
        ->toHaveCount(4)
        ->and((new HeroBackgroundWidgetAssetSchemaExtender)->extendAssetComponents($schema, $baseComponents))
        ->toHaveCount(4)
        ->and((new HeroBackgroundWidgetAssetSchemaExtender)->extendRepeaterComponents($baseComponents))
        ->toHaveCount(4);
});
