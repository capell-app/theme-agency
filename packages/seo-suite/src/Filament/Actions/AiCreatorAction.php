<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Actions;

use Capell\SeoSuite\Actions\GenerateAiLayoutAction;
use Capell\SeoSuite\Actions\SubmitAiCreatorDraftAction;
use Capell\SeoSuite\DataObjects\AiCreatorData;
use Capell\SeoSuite\Models\AiCreatorContext;
use Capell\SeoSuite\Models\AiCreatorSession;
use Capell\SeoSuite\Policies\AiCreatorPolicy;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Throwable;

class AiCreatorAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('ai-creator')
            ->label(__('capell-seo-suite::generic.ai_creator_action'))
            ->icon('heroicon-o-sparkles')
            ->slideOver()
            ->visible(fn (): bool => resolve(AiCreatorPolicy::class)->isEnabledFor(
                $this->resolveSiteFromRecord(),
            ))
            ->schema(fn (): array => $this->buildWizardForm())
            ->action(function (array $data): void {
                $this->runCreator($data);
            });
    }

    /**
     * @return array<array-key, mixed>
     */
    private function buildWizardForm(): array
    {
        return [
            Hidden::make('ai_session_id'),

            Wizard::make([
                Step::make(__('capell-seo-suite::generic.ai_creator_describe_step'))
                    ->label(__('capell-seo-suite::generic.ai_creator_describe_step_label'))
                    ->schema([
                        Textarea::make('intent')
                            ->label(__('capell-seo-suite::generic.ai_creator_intent'))
                            ->placeholder(__('capell-seo-suite::generic.ai_creator_intent_placeholder'))
                            ->required()
                            ->rows(4),
                        Select::make('page_count')
                            ->label(__('capell-seo-suite::generic.ai_creator_page_count'))
                            ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                            ->default(1)
                            ->visible(fn (): bool => $this->isMountedOnSiteResource()),
                    ]),

                Step::make(__('capell-seo-suite::generic.ai_creator_brand_step'))
                    ->label(__('capell-seo-suite::generic.ai_creator_brand_step_label'))
                    ->schema(fn (): array => $this->buildBrandStep())
                    ->afterValidation(function (Get $get, Set $set): void {
                        $this->generateLayout($get, $set);
                    }),

                Step::make(__('capell-seo-suite::generic.ai_creator_layout_step'))
                    ->label(__('capell-seo-suite::generic.ai_creator_layout_step_label'))
                    ->schema([
                        Repeater::make('layout_preview')
                            ->label(__('capell-seo-suite::generic.ai_creator_layout_preview'))
                            ->schema([
                                TextInput::make('section_type')->label(__('capell-seo-suite::generic.ai_creator_section_type'))->disabled(),
                                Textarea::make('fields_preview')->label(__('capell-seo-suite::generic.ai_creator_fields_preview'))->disabled()->rows(2),
                            ])
                            ->addable(false)
                            ->reorderable()
                            ->columns(2),
                    ]),

                Step::make(__('capell-seo-suite::generic.ai_creator_review_step'))
                    ->label(__('capell-seo-suite::generic.ai_creator_review_step_label'))
                    ->schema([
                        Textarea::make('review_notes')
                            ->label(__('capell-seo-suite::generic.ai_creator_review_notes'))
                            ->rows(3),
                    ]),
            ])->submitAction(
                Action::make('submit')
                    ->label(__('capell-seo-suite::generic.ai_creator_submit'))
                    ->color('primary'),
            ),
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    private function buildBrandStep(): array
    {
        $siteId = $this->resolveSiteId();
        $existingContext = $siteId !== null ? AiCreatorContext::query()->where('site_id', $siteId)->first() : null;

        return [
            Select::make('tone')
                ->label(__('capell-seo-suite::generic.ai_creator_tone'))
                ->options([
                    'professional' => __('capell-seo-suite::generic.ai_creator_tone_professional'),
                    'friendly' => __('capell-seo-suite::generic.ai_creator_tone_friendly'),
                    'playful' => __('capell-seo-suite::generic.ai_creator_tone_playful'),
                    'authoritative' => __('capell-seo-suite::generic.ai_creator_tone_authoritative'),
                ])
                ->default($existingContext->tone ?? 'professional')
                ->required(),

            TextInput::make('industry')
                ->label(__('capell-seo-suite::generic.ai_creator_industry'))
                ->default($existingContext->industry ?? '')
                ->placeholder(__('capell-seo-suite::generic.ai_creator_industry_placeholder')),

            Textarea::make('target_audience')
                ->label(__('capell-seo-suite::generic.ai_creator_target_audience'))
                ->default($existingContext->target_audience ?? '')
                ->placeholder(__('capell-seo-suite::generic.ai_creator_target_audience_placeholder'))
                ->rows(2),

            Textarea::make('brand_voice_notes')
                ->label(__('capell-seo-suite::generic.ai_creator_brand_voice_notes'))
                ->default($existingContext->brand_voice_notes ?? '')
                ->placeholder(__('capell-seo-suite::generic.ai_creator_brand_voice_notes_placeholder'))
                ->rows(2),
        ];
    }

    private function generateLayout(Get $get, Set $set): void
    {
        $siteId = $this->resolveSiteId() ?? 0;
        $userId = (int) Auth::id();

        AiCreatorContext::query()->updateOrCreate(['site_id' => $siteId], [
            'tone' => $get('tone') ?? 'professional',
            'industry' => $get('industry') ?? '',
            'target_audience' => $get('target_audience') ?? null,
            'brand_voice_notes' => $get('brand_voice_notes') ?? null,
        ]);

        try {
            $creatorData = new AiCreatorData(
                siteId: $siteId,
                userId: $userId,
                intent: (string) $get('intent'),
                pageCount: (int) ($get('page_count') ?? 1),
                tone: $get('tone') ?? null,
                industry: $get('industry') ?? null,
                targetAudience: $get('target_audience') ?? null,
                brandVoiceNotes: $get('brand_voice_notes') ?? null,
            );

            $sections = resolve(GenerateAiLayoutAction::class)->handle($creatorData);

            $session = AiCreatorSession::query()->where([
                'site_id' => $siteId,
                'user_id' => $userId,
                'status' => 'review',
            ])->latest()->first();

            throw_unless($session, RuntimeException::class, 'AI session was not created. Please try again.');

            $set('ai_session_id', $session->id);

            $previewData = array_map(fn (array $section): array => [
                'section_type' => $section['section_type'] ?? '',
                'fields_preview' => json_encode($section['fields'] ?? [], JSON_PRETTY_PRINT),
            ], $sections);

            $set('layout_preview', $previewData);
        } catch (Throwable $throwable) {
            Notification::make()
                ->title(__('capell-seo-suite::generic.ai_creator_generation_failed'))
                ->body($throwable->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private function runCreator(array $data): void
    {
        $sessionId = $data['ai_session_id'] ?? null;
        $siteId = $this->resolveSiteId();
        $userId = (int) Auth::id();
        $session = null;

        if ($sessionId) {
            $sessionQuery = AiCreatorSession::query()
                ->whereKey((int) $sessionId)
                ->where('user_id', $userId)
                ->where('status', 'review');

            if ($siteId !== null) {
                $sessionQuery->where('site_id', $siteId);
            }

            $session = $sessionQuery->first();
        }

        if ($session === null) {
            Notification::make()
                ->title(__('capell-seo-suite::generic.ai_creator_failed'))
                ->body(__('capell-seo-suite::generic.ai_creator_missing_session'))
                ->danger()
                ->send();

            return;
        }

        try {
            resolve(SubmitAiCreatorDraftAction::class)->handle($session, $userId, $siteId);

            Notification::make()
                ->title(__('capell-seo-suite::generic.ai_creator_submitted'))
                ->body(__('capell-seo-suite::generic.ai_creator_submitted_body'))
                ->success()
                ->send();
        } catch (Throwable $throwable) {
            Notification::make()
                ->title(__('capell-seo-suite::generic.ai_creator_failed'))
                ->body($throwable->getMessage())
                ->danger()
                ->send();
        }
    }

    private function resolveSiteFromRecord(): object
    {
        $record = $this->getRecord();

        if (is_object($record) && method_exists($record, 'getSite')) {
            return $record->getSite();
        }

        return (object) ['ai_creator_enabled' => null];
    }

    private function resolveSiteId(): ?int
    {
        $record = $this->getRecord();

        if ($record === null) {
            return null;
        }

        if (is_object($record) && method_exists($record, 'getSiteId')) {
            return $record->getSiteId();
        }

        if (! $record instanceof Model) {
            return null;
        }

        $recordSiteId = $record->getAttribute('site_id');
        if ($recordSiteId !== null) {
            return (int) $recordSiteId;
        }

        if ($record->getKey() !== null && str_contains($record::class, 'Site')) {
            return (int) $record->getKey();
        }

        return null;
    }

    private function isMountedOnSiteResource(): bool
    {
        $record = $this->getRecord();

        return $record instanceof Model && str_contains($record::class, 'Site');
    }
}
