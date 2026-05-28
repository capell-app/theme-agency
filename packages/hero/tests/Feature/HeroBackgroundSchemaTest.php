<?php

declare(strict_types=1);

use Capell\Core\Contracts\Media\MediaFieldFactory;
use Capell\Hero\Filament\Components\Forms\HeroBackgroundSchema;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

test('theme background media fields can be built with a non spatie media factory', function (): void {
    app()->bind(MediaFieldFactory::class, static fn (): MediaFieldFactory => new class implements MediaFieldFactory
    {
        public function make(string $name): Field
        {
            return TextInput::make($name . '_id');
        }
    });

    $components = HeroBackgroundSchema::theme();

    expect($components)->toHaveCount(3);
});
