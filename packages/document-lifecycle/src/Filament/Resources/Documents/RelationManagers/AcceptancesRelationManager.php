<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Filament\Resources\Documents\RelationManagers;

use BackedEnum;
use Capell\DocumentLifecycle\Actions\BuildDocumentAcceptanceCertificateAction;
use Capell\DocumentLifecycle\Actions\BuildDocumentAcceptanceEvidenceCsvAction;
use Capell\DocumentLifecycle\Actions\BuildOutstandingDocumentAcceptancesCsvAction;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AcceptancesRelationManager extends RelationManager
{
    protected static string|BackedEnum|null $icon = 'heroicon-o-check-badge';

    protected static string $relationship = 'acceptances';

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-document-lifecycle::navigation.relations.acceptances');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('accepted_at')->latest('id'))
            ->headerActions([
                Action::make('export_acceptance_evidence')
                    ->label(__('capell-document-lifecycle::navigation.actions.export_acceptance_evidence'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->schema([
                        Select::make('publication_id')
                            ->label(__('capell-document-lifecycle::navigation.fields.version'))
                            ->options(fn (): array => $this->publicationOptions())
                            ->placeholder(__('capell-document-lifecycle::navigation.fields.all_versions')),
                    ])
                    ->action(fn (array $data): StreamedResponse => $this->downloadAcceptanceEvidence($data)),
                Action::make('export_outstanding_acceptances')
                    ->label(__('capell-document-lifecycle::navigation.actions.export_outstanding_acceptances'))
                    ->icon('heroicon-o-exclamation-triangle')
                    ->action(fn (): StreamedResponse => $this->downloadOutstandingAcceptances()),
            ])
            ->actions([
                Action::make('download_acceptance_certificate')
                    ->label(__('capell-document-lifecycle::navigation.actions.download_acceptance_certificate'))
                    ->icon('heroicon-o-shield-check')
                    ->action(fn (DocumentAcceptance $record): StreamedResponse => $this->downloadAcceptanceCertificate($record)),
            ])
            ->columns([
                TextColumn::make('document_version')
                    ->label(__('capell-document-lifecycle::navigation.fields.version'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document_hash')
                    ->label(__('capell-document-lifecycle::navigation.fields.hash'))
                    ->copyable()
                    ->limit(16)
                    ->searchable(),
                TextColumn::make('context')
                    ->label(__('capell-document-lifecycle::navigation.fields.context'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('acceptor_type')
                    ->label(__('capell-document-lifecycle::navigation.fields.acceptor'))
                    ->formatStateUsing(fn (?string $state): string => class_basename($state ?? 'Unknown')),
                TextColumn::make('accepted_at')
                    ->label(__('capell-document-lifecycle::navigation.fields.accepted_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    #[Override]
    protected static function getPluralModelLabel(): string
    {
        return __('capell-document-lifecycle::navigation.relations.acceptances');
    }

    /**
     * @return array<int, string>
     */
    private function publicationOptions(): array
    {
        /** @var Document $document */
        $document = $this->getOwnerRecord();

        return $document
            ->publications()
            ->latest('published_at')
            ->latest('id')
            ->get()
            ->mapWithKeys(static fn (DocumentPublication $publication): array => [
                $publication->getKey() => $publication->version_label . ' (' . $publication->content_hash . ')',
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function downloadAcceptanceEvidence(array $data): StreamedResponse
    {
        /** @var Document $document */
        $document = $this->getOwnerRecord();
        $publicationId = $data['publication_id'] ?? null;
        $publication = filled($publicationId)
            ? $document->publications()->find((int) $publicationId)
            : null;

        return response()->streamDownload(
            function () use ($document, $publication): void {
                echo BuildDocumentAcceptanceEvidenceCsvAction::run($document, $publication);
            },
            $this->filename($document, $publication),
            ['Content-Type' => 'text/csv'],
        );
    }

    private function filename(Document $document, ?DocumentPublication $publication): string
    {
        $version = $publication instanceof DocumentPublication
            ? '-' . str($publication->version_label)->slug()->toString()
            : '';

        return 'document-acceptance-evidence-' . str($document->key)->slug()->toString() . $version . '-' . now()->format('Y-m-d-His') . '.csv';
    }

    private function downloadOutstandingAcceptances(): StreamedResponse
    {
        /** @var Document $document */
        $document = $this->getOwnerRecord();

        return response()->streamDownload(
            function () use ($document): void {
                echo BuildOutstandingDocumentAcceptancesCsvAction::run($document);
            },
            'document-outstanding-acceptances-' . str($document->key)->slug()->toString() . '-' . now()->format('Y-m-d-His') . '.csv',
            ['Content-Type' => 'text/csv'],
        );
    }

    private function downloadAcceptanceCertificate(DocumentAcceptance $acceptance): StreamedResponse
    {
        return response()->streamDownload(
            function () use ($acceptance): void {
                echo BuildDocumentAcceptanceCertificateAction::run($acceptance);
            },
            'document-acceptance-certificate-' . $acceptance->getKey() . '-' . now()->format('Y-m-d-His') . '.json',
            ['Content-Type' => 'application/json'],
        );
    }
}
