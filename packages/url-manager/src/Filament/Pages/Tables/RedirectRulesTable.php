<?php

declare(strict_types=1);

namespace Capell\UrlManager\Filament\Pages\Tables;

use Capell\UrlManager\Actions\BuildRedirectRulesCsvAction;
use Capell\UrlManager\Actions\BuildRedirectRulesCsvTemplateAction;
use Capell\UrlManager\Actions\DeleteRedirectRuleAction;
use Capell\UrlManager\Actions\ImportRedirectRulesAction;
use Capell\UrlManager\Actions\ParseRedirectRulesCsvAction;
use Capell\UrlManager\Actions\PreviewRedirectRulesImportAction;
use Capell\UrlManager\Actions\ResolveRedirectRulesCsvContentsAction;
use Capell\UrlManager\Actions\SetRedirectRuleStatusAction;
use Capell\UrlManager\Actions\UpdateRedirectRuleAction;
use Capell\UrlManager\Actions\UpsertRedirectRuleAction;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Capell\UrlManager\Enums\UrlManagerPermission;
use Capell\UrlManager\Filament\Pages\Schemas\RedirectRuleForm;
use Capell\UrlManager\Models\RedirectRule;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class RedirectRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => RedirectRule::query())
            ->columns([
                TextColumn::make('source_url')
                    ->label(__('capell-url-manager::table.source_url'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('target_url')
                    ->label(__('capell-url-manager::table.target_url'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('status_code')
                    ->label(__('capell-url-manager::table.status_code'))
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('match_type')
                    ->label(__('capell-url-manager::table.match_type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('capell-url-manager::table.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('hit_count')
                    ->label(__('capell-url-manager::table.hit_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_hit_at')
                    ->label(__('capell-url-manager::table.last_hit_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-url-manager::table.status'))
                    ->options(RedirectRuleStatus::class),
            ])
            ->recordActions([
                EditAction::make('edit')
                    ->label(__('capell-url-manager::action.edit_redirect'))
                    ->schema(fn (Schema $schema): Schema => RedirectRuleForm::configure($schema))
                    ->using(fn (RedirectRule $record, array $data): RedirectRule => UpdateRedirectRuleAction::run($record, self::redirectRuleDataFromFormData($data)))
                    ->authorize(fn (): bool => self::canManageRedirectRules())
                    ->successNotificationTitle(__('capell-url-manager::action.redirect_updated')),
                ActionGroup::make([
                    self::statusAction('activateRedirectRule', __('capell-url-manager::action.activate_redirect'), RedirectRuleStatus::Active),
                    self::statusAction('disableRedirectRule', __('capell-url-manager::action.disable_redirect'), RedirectRuleStatus::Inactive),
                    DeleteAction::make('delete')
                        ->label(__('capell-url-manager::action.delete_redirect'))
                        ->authorize(fn (): bool => self::canManageRedirectRules())
                        ->using(function (RedirectRule $record): void {
                            DeleteRedirectRuleAction::run($record);
                        }),
                ])
                    ->color('gray'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('capell-url-manager::action.create_redirect'))
                    ->model(RedirectRule::class)
                    ->schema(fn (Schema $schema): Schema => RedirectRuleForm::configure($schema))
                    ->using(fn (array $data): RedirectRule => UpsertRedirectRuleAction::run(self::redirectRuleDataFromFormData($data)))
                    ->authorize(fn (): bool => self::canManageRedirectRules())
                    ->successNotificationTitle(__('capell-url-manager::action.redirect_created')),
                Action::make('importRedirectRules')
                    ->label(__('capell-url-manager::action.import_redirects'))
                    ->authorize(fn (): bool => self::canManageRedirectRules())
                    ->schema(self::importSchema())
                    ->action(function (array $data): void {
                        $csv = ResolveRedirectRulesCsvContentsAction::run($data);
                        $rows = ParseRedirectRulesCsvAction::run($csv);
                        $preview = PreviewRedirectRulesImportAction::run($rows);
                        $result = $preview->skipped === 0 ? ImportRedirectRulesAction::run($rows) : $preview;

                        Notification::make()
                            ->status($result->skipped === 0 ? 'success' : 'warning')
                            ->title(__(
                                'capell-url-manager::action.redirect_imported',
                                [
                                    'imported' => $result->imported,
                                    'skipped' => $result->skipped,
                                ],
                            ))
                            ->send();
                    }),
                Action::make('previewRedirectImport')
                    ->label(__('capell-url-manager::action.preview_import'))
                    ->authorize(fn (): bool => self::canManageRedirectRules())
                    ->schema(self::importSchema())
                    ->action(function (array $data): void {
                        $csv = ResolveRedirectRulesCsvContentsAction::run($data);
                        $result = PreviewRedirectRulesImportAction::run(ParseRedirectRulesCsvAction::run($csv));

                        Notification::make()
                            ->status($result->skipped === 0 ? 'success' : 'warning')
                            ->title(__(
                                'capell-url-manager::action.redirect_import_previewed',
                                [
                                    'valid' => $result->imported,
                                    'invalid' => $result->skipped,
                                ],
                            ))
                            ->body(implode(PHP_EOL, array_slice($result->errors, 0, 5)))
                            ->send();
                    }),
                Action::make('exportRedirectRules')
                    ->label(__('capell-url-manager::action.export_redirects'))
                    ->authorize(fn (): bool => self::canViewRedirectRules())
                    ->action(fn (): StreamedResponse => response()->streamDownload(
                        function (): void {
                            echo BuildRedirectRulesCsvAction::run();
                        },
                        'redirect-rules-' . now()->format('Y-m-d-His') . '.csv',
                        ['Content-Type' => 'text/csv'],
                    )),
                Action::make('downloadRedirectImportTemplate')
                    ->label(__('capell-url-manager::action.download_import_template'))
                    ->authorize(fn (): bool => self::canViewRedirectRules())
                    ->action(fn (): StreamedResponse => response()->streamDownload(
                        function (): void {
                            echo BuildRedirectRulesCsvTemplateAction::run();
                        },
                        'redirect-rules-template.csv',
                        ['Content-Type' => 'text/csv'],
                    )),
            ])
            ->toolbarActions([
                self::bulkStatusAction('activateRedirectRules', __('capell-url-manager::action.activate_redirects'), RedirectRuleStatus::Active),
                self::bulkStatusAction('disableRedirectRules', __('capell-url-manager::action.disable_redirects'), RedirectRuleStatus::Inactive),
                BulkAction::make('deleteRedirectRules')
                    ->label(__('capell-url-manager::action.delete_redirects'))
                    ->requiresConfirmation()
                    ->authorize(fn (): bool => self::canManageRedirectRules())
                    ->action(function (Collection $records): void {
                        $records->each(fn (RedirectRule $redirectRule): mixed => DeleteRedirectRuleAction::run($redirectRule));
                    }),
            ])
            ->defaultSort('hit_count', 'desc');
    }

    private static function statusAction(string $name, string $label, RedirectRuleStatus $status): Action
    {
        return Action::make($name)
            ->label($label)
            ->authorize(fn (): bool => self::canManageRedirectRules())
            ->visible(fn (RedirectRule $record): bool => $record->status !== $status)
            ->action(fn (RedirectRule $record): int => SetRedirectRuleStatusAction::run($record, $status));
    }

    private static function bulkStatusAction(string $name, string $label, RedirectRuleStatus $status): BulkAction
    {
        return BulkAction::make($name)
            ->label($label)
            ->requiresConfirmation()
            ->authorize(fn (): bool => self::canManageRedirectRules())
            ->action(fn (Collection $records): int => SetRedirectRuleStatusAction::run($records, $status));
    }

    /**
     * @return array<int, mixed>
     */
    private static function importSchema(): array
    {
        return [
            FileUpload::make('csv')
                ->label(__('capell-url-manager::table.csv_file'))
                ->disk('local')
                ->maxSize(2048)
                ->acceptedFileTypes(['text/csv', 'text/plain']),
            Textarea::make('csv_contents')
                ->label(__('capell-url-manager::table.csv_contents'))
                ->rows(12)
                ->columnSpanFull(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function redirectRuleDataFromFormData(array $data): RedirectRuleData
    {
        return new RedirectRuleData(
            sourceUrl: (string) ($data['source_url'] ?? ''),
            targetUrl: (string) ($data['target_url'] ?? ''),
            siteId: self::nullableInt($data['site_id'] ?? null),
            languageId: self::nullableInt($data['language_id'] ?? null),
            statusCode: self::nullableInt($data['status_code'] ?? null) ?? 301,
            matchType: RedirectMatchType::from((string) ($data['match_type'] ?? RedirectMatchType::Exact->value)),
            status: RedirectRuleStatus::from((string) ($data['status'] ?? RedirectRuleStatus::Active->value)),
            preserveQuery: (bool) ($data['preserve_query'] ?? true),
            notes: is_string($data['notes'] ?? null) && trim($data['notes']) !== '' ? $data['notes'] : null,
        );
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private static function canViewRedirectRules(): bool
    {
        if (self::userCan(UrlManagerPermission::ViewRedirectRulesPage)) {
            return true;
        }

        return self::canManageRedirectRules();
    }

    private static function canManageRedirectRules(): bool
    {
        return self::userCan(UrlManagerPermission::ManageRedirectRules);
    }

    private static function userCan(UrlManagerPermission $permission): bool
    {
        if (Gate::allows($permission->value)) {
            return true;
        }

        $user = auth()->user();

        return $user?->can($permission->value) === true;
    }
}
