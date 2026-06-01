<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Capell\Comments\Enums\CommentIdentityMode;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentVerificationFlow;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CommentSettingsSchema implements HasSchema
{
    public static function make(Schema $configurator): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('enabled')
                        ->label(__('capell-comments::settings.enabled')),
                    Select::make('identity_mode')
                        ->label(__('capell-comments::settings.identity_mode'))
                        ->options(CommentIdentityMode::class)
                        ->required(),
                    Select::make('publication_policy')
                        ->label(__('capell-comments::settings.publication_policy'))
                        ->options(CommentPublicationPolicy::class)
                        ->required(),
                    Select::make('verification_flow')
                        ->label(__('capell-comments::settings.verification_flow'))
                        ->options(CommentVerificationFlow::class)
                        ->required(),
                    Toggle::make('require_email_verification')
                        ->label(__('capell-comments::settings.require_email_verification')),
                    Toggle::make('auto_inject')
                        ->label(__('capell-comments::settings.auto_inject')),
                    TextInput::make('max_depth')
                        ->label(__('capell-comments::settings.max_depth'))
                        ->integer()
                        ->minValue(0)
                        ->required(),
                    TextInput::make('token_expiry_hours')
                        ->label(__('capell-comments::settings.token_expiry_hours'))
                        ->integer()
                        ->minValue(1)
                        ->required(),
                    Repeater::make('site_overrides')
                        ->label(__('capell-comments::settings.site_overrides'))
                        ->schema(self::overrideSchema('site_id'))
                        ->defaultItems(0)
                        ->collapsible()
                        ->columnSpanFull(),
                    Repeater::make('commentable_type_overrides')
                        ->label(__('capell-comments::settings.commentable_type_overrides'))
                        ->schema(self::overrideSchema('commentable_type'))
                        ->defaultItems(0)
                        ->collapsible()
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    private static function overrideSchema(string $scopeField): array
    {
        $scopeInput = $scopeField === 'site_id'
            ? TextInput::make('site_id')
                ->label(__('capell-comments::settings.override_site_id'))
                ->integer()
                ->required()
            : Select::make('commentable_type')
                ->label(__('capell-comments::settings.override_commentable_type'))
                ->options([
                    'page' => __('capell-comments::generic.page'),
                    'article' => __('capell-comments::generic.article'),
                ])
                ->required();

        return [
            Grid::make(2)
                ->schema([
                    $scopeInput,
                    Toggle::make('enabled')
                        ->label(__('capell-comments::settings.enabled')),
                    Select::make('identity_mode')
                        ->label(__('capell-comments::settings.identity_mode'))
                        ->options(CommentIdentityMode::class),
                    Select::make('publication_policy')
                        ->label(__('capell-comments::settings.publication_policy'))
                        ->options(CommentPublicationPolicy::class),
                    Select::make('verification_flow')
                        ->label(__('capell-comments::settings.verification_flow'))
                        ->options(CommentVerificationFlow::class),
                    Toggle::make('require_email_verification')
                        ->label(__('capell-comments::settings.require_email_verification')),
                    TextInput::make('max_depth')
                        ->label(__('capell-comments::settings.max_depth'))
                        ->integer()
                        ->minValue(0),
                ]),
        ];
    }
}
