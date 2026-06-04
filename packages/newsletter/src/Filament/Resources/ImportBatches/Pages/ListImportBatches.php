<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Resources\ImportBatches\Pages;

use Capell\Admin\Filament\Components\Forms\SiteSelect;
use Capell\Admin\Support\SiteScope;
use Capell\Newsletter\Actions\ImportSubscribersAction;
use Capell\Newsletter\Actions\ParseSubscriberCsvRowsAction;
use Capell\Newsletter\Filament\Resources\ImportBatches\ImportBatchResource;
use Capell\Newsletter\Models\ImportBatch;
use Capell\Newsletter\Support\NewsletterAdminAccess;
use Capell\Tags\Models\Tag;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Override;

class ListImportBatches extends ListRecords
{
    protected static string $resource = ImportBatchResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            $this->importAction(dryRun: true),
            $this->importAction(dryRun: false),
        ];
    }

    private function importAction(bool $dryRun): Action
    {
        return Action::make($dryRun ? 'dry_run_import' : 'commit_import')
            ->label($dryRun ? __('capell-newsletter::actions.dry_run_import') : __('capell-newsletter::actions.commit_import'))
            ->schema($this->importForm())
            ->action(function (array $data) use ($dryRun): void {
                Gate::authorize('create', ImportBatch::class);
                NewsletterAdminAccess::authorizeSiteId((int) $data['site_id']);

                $contents = $this->csvContents($data);
                $rows = ParseSubscriberCsvRowsAction::run($contents);
                $actor = auth()->user();

                ImportSubscribersAction::run(
                    siteId: (int) $data['site_id'],
                    rows: $rows,
                    consentBasis: (string) $data['consent_basis'],
                    dryRun: $dryRun,
                    tagIds: array_map(static fn (mixed $tagId): int => (int) $tagId, is_array($data['tag_ids'] ?? null) ? $data['tag_ids'] : []),
                    actor: $actor instanceof Model ? $actor : null,
                    filename: is_string($data['csv'] ?? null) ? basename($data['csv']) : null,
                );
            });
    }

    /**
     * @return array<int, mixed>
     */
    private function importForm(): array
    {
        return [
            SiteSelect::make('site_id')->required(),
            FileUpload::make('csv')
                ->label(__('capell-newsletter::form.csv_file'))
                ->disk('local')
                ->maxSize((int) config('capell-newsletter.imports.max_file_kb', 2048))
                ->acceptedFileTypes(['text/csv', 'text/plain']),
            Textarea::make('csv_contents')
                ->label(__('capell-newsletter::form.csv_contents')),
            TextInput::make('consent_basis')
                ->label(__('capell-newsletter::form.consent_basis'))
                ->required(),
            Select::make('tag_ids')
                ->label(__('capell-newsletter::navigation.newsletter_tags'))
                ->multiple()
                ->options(fn (): array => $this->newsletterTagOptions()),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function csvContents(array $data): string
    {
        $inlineContents = $data['csv_contents'] ?? null;

        if (is_string($inlineContents) && trim($inlineContents) !== '') {
            $this->ensureAllowedCsvSize(strlen($inlineContents));

            return $inlineContents;
        }

        $path = $data['csv'] ?? null;

        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            return '';
        }

        $this->ensureAllowedCsvSize(Storage::disk('local')->size($path));

        $contents = Storage::disk('local')->get($path);

        return is_string($contents) ? $contents : '';
    }

    private function ensureAllowedCsvSize(int $bytes): void
    {
        $maxKilobytes = (int) config('capell-newsletter.imports.max_file_kb', 2048);

        if ($maxKilobytes <= 0 || $bytes <= $maxKilobytes * 1024) {
            return;
        }

        throw ValidationException::withMessages([
            'csv' => [__('capell-newsletter::messages.import_file_too_large', ['max' => $maxKilobytes])],
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function newsletterTagOptions(): array
    {
        $query = SiteScope::applyForCurrentActor(Tag::query());

        return $query
            ->where('type', config('capell-newsletter.newsletter_tag_type', 'newsletter'))
            ->get()
            ->mapWithKeys(static function (Tag $tag): array {
                $name = $tag->getAttribute('name');
                $fallbackName = is_array($name) ? reset($name) : null;
                $label = is_array($name)
                    ? (string) ($name[app()->getLocale()] ?? (is_scalar($fallbackName) ? $fallbackName : ''))
                    : (string) $name;

                return [(string) $tag->getKey() => $label];
            })
            ->all();
    }
}
