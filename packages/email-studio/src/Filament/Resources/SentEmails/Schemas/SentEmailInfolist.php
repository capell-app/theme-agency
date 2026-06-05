<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\SentEmails\Schemas;

use Capell\EmailStudio\Models\SentEmail;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SentEmailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-email-studio::mail_tracker.sections.overview'))
                ->columns(2)
                ->schema([
                    TextEntry::make('subject')
                        ->label(__('capell-email-studio::mail_tracker.fields.subject'))
                        ->columnSpanFull(),
                    TextEntry::make('sender_email')
                        ->label(__('capell-email-studio::mail_tracker.fields.sender')),
                    TextEntry::make('recipient_email')
                        ->label(__('capell-email-studio::mail_tracker.fields.recipient')),
                    TextEntry::make('opens')
                        ->label(__('capell-email-studio::mail_tracker.fields.opens')),
                    TextEntry::make('clicks')
                        ->label(__('capell-email-studio::mail_tracker.fields.clicks')),
                    TextEntry::make('opened_at')
                        ->label(__('capell-email-studio::mail_tracker.fields.opened_at'))
                        ->dateTime()
                        ->placeholder(__('capell-email-studio::mail_tracker.placeholders.empty')),
                    TextEntry::make('clicked_at')
                        ->label(__('capell-email-studio::mail_tracker.fields.clicked_at'))
                        ->dateTime()
                        ->placeholder(__('capell-email-studio::mail_tracker.placeholders.empty')),
                    TextEntry::make('message_id')
                        ->label(__('capell-email-studio::mail_tracker.fields.message_id'))
                        ->copyable()
                        ->placeholder(__('capell-email-studio::mail_tracker.placeholders.empty')),
                    TextEntry::make('created_at')
                        ->label(__('capell-email-studio::mail_tracker.fields.created_at'))
                        ->dateTime(),
                ]),
            Section::make(__('capell-email-studio::mail_tracker.sections.preview'))
                ->schema([
                    ViewEntry::make('content')
                        ->view('capell-email-studio::filament.sent-emails.content-preview')
                        ->state(fn (SentEmail $record): string => $record->content ?? ''),
                ])
                ->visible(fn (SentEmail $record): bool => $record->content !== null && $record->content !== ''),
            Section::make(__('capell-email-studio::mail_tracker.sections.headers'))
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextEntry::make('headers')
                        ->label(__('capell-email-studio::mail_tracker.fields.headers'))
                        ->fontFamily('mono')
                        ->copyable()
                        ->columnSpanFull(),
                ])
                ->visible(fn (SentEmail $record): bool => $record->headers !== null && $record->headers !== ''),
            Section::make(__('capell-email-studio::mail_tracker.sections.clicks'))
                ->collapsible()
                ->schema([
                    ViewEntry::make('clickRows')
                        ->view('capell-email-studio::filament.sent-emails.click-rows')
                        ->state(fn (SentEmail $record): array => [
                            'clickRows' => $record->clickRows()->latest('updated_at')->get(),
                        ]),
                ]),
        ]);
    }
}
