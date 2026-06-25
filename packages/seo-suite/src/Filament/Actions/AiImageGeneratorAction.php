<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Actions;

use Capell\SeoSuite\Actions\GenerateAiImageAction;
use Capell\SeoSuite\DataObjects\AiImageData;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Override;
use Throwable;

class AiImageGeneratorAction extends Action
{
    /**
     * @param  array<string, string>  $contextFieldKeys  Keys of sibling Filament fields to read as context
     */
    #[Override]
    public static function make(?string $name = null, array $contextFieldKeys = []): static
    {
        return parent::make($name ?? 'generate-ai-image')
            ->label(__('capell-seo-suite::generic.ai_image_generator_action'))
            ->icon('heroicon-o-sparkles')
            ->modalHeading(__('capell-seo-suite::generic.ai_image_generator_modal_heading'))
            ->modalSubmitActionLabel(__('capell-seo-suite::generic.ai_image_generator_accept'))
            ->schema(function (Get $get) use ($contextFieldKeys): array {
                $contextParts = [];
                foreach ($contextFieldKeys as $key => $label) {
                    $value = $get($key);
                    if (filled($value)) {
                        $contextParts[] = sprintf('%s: %s', $label, $value);
                    }
                }

                $autoPrompt = implode('. ', $contextParts);

                return [
                    Textarea::make('prompt')
                        ->label(__('capell-seo-suite::generic.ai_image_generator_prompt'))
                        ->default($autoPrompt)
                        ->required()
                        ->rows(3)
                        ->helperText(__('capell-seo-suite::generic.ai_image_generator_prompt_helper')),

                    Actions::make([
                        Action::make('generate_preview')
                            ->label(__('capell-seo-suite::generic.ai_image_generator_generate'))
                            ->color('gray')
                            ->action(function (array $state, Set $set): void {
                                try {
                                    $data = new AiImageData(
                                        prompt: $state['prompt'],
                                        size: config('capell-ai-orchestrator.prism.image_size', '1024x1024'),
                                    );

                                    $url = resolve(GenerateAiImageAction::class)->handle($data);
                                    $set('preview_url', $url);
                                } catch (Throwable $throwable) {
                                    Notification::make()
                                        ->title(__('capell-seo-suite::generic.ai_image_generator_failed'))
                                        ->body($throwable->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }),
                    ]),

                    ViewField::make('preview_url')
                        ->view('capell-seo-suite::filament.fields.image-preview')
                        ->visible(fn (Get $get): bool => filled($get('preview_url'))),
                ];
            })
            ->action(function (array $data, Set $set) use ($name): void {
                $url = $data['preview_url'] ?? null;

                if (! $url) {
                    Notification::make()
                        ->title(__('capell-seo-suite::generic.ai_image_generator_missing_image'))
                        ->warning()
                        ->send();

                    return;
                }

                $set('../../' . $name, $url);

                Notification::make()
                    ->title(__('capell-seo-suite::generic.ai_image_generator_applied'))
                    ->success()
                    ->send();
            });
    }
}
