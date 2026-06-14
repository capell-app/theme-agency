<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Pages\Tables;

use Capell\Admin\Filament\Components\Tables\Columns\DateColumn;
use Capell\Admin\Filament\Components\Tables\Columns\Page\PageNameColumn;
use Capell\Admin\Filament\Contracts\TableConfigurator;
use Capell\Core\Actions\GetEditPageResourceUrlAction;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\BuildAiReadinessAuditAction;
use Capell\SeoSuite\Actions\DashboardReports\BuildAiDiscoveryPageQueryAction;
use Capell\SeoSuite\Actions\FillAiDiscoveryPageSummaryAction;
use Capell\SeoSuite\Actions\PageIsDiscoverableForAiDiscoveryAction;
use Capell\SeoSuite\Actions\UpdateAiDiscoveryPageInclusionAction;
use Capell\SeoSuite\Actions\UpdateAiDiscoveryPageProfileAction;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use LogicException;
use Throwable;

class AiDiscoveryTable implements TableConfigurator
{
    private const string REQUEST_PROFILE_CACHE_KEY = 'capell.seo-suite.ai-discovery-table.profiles';

    private const string REQUEST_SITE_PROFILE_CACHE_KEY = 'capell.seo-suite.ai-discovery-table.site-profiles';

    private const string REQUEST_READINESS_ISSUE_COUNT_CACHE_KEY = 'capell.seo-suite.ai-discovery-table.readiness-issue-counts';

    private const string REQUEST_MARKDOWN_DISCOVERABILITY_CACHE_KEY = 'capell.seo-suite.ai-discovery-table.markdown-discoverability';

    /**
     * @var array<string, AiDiscoveryPageProfile|null>
     */
    private static array $profiles = [];

    /**
     * @var array<string, AiDiscoverySiteProfile|null>
     */
    private static array $siteProfiles = [];

    /**
     * @var array<string, int>
     */
    private static array $readinessIssueCounts = [];

    /**
     * @var array<string, bool>
     */
    private static array $markdownDiscoverability = [];

    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => BuildAiDiscoveryPageQueryAction::run())
            ->columns(static::getTableColumns())
            ->filters(static::getTableFilters())
            ->recordActions(static::getTableActions())
            ->toolbarActions(static::getBulkActions())
            ->defaultSort('updated_at', 'desc');
    }

    /**
     * @return array<int, TextColumn|PageNameColumn|DateColumn>
     */
    protected static function getTableColumns(): array
    {
        return [
            PageNameColumn::make('name')
                ->label(__('capell-admin::table.name'))
                ->withUrl()
                ->nameUrl(fn (Page $record): ?string => GetEditPageResourceUrlAction::run($record))
                ->size('sm')
                ->searchable()
                ->sortable(),
            TextColumn::make('site.name')
                ->label(__('capell-admin::table.site'))
                ->size('sm')
                ->sortable(),
            TextColumn::make('site.language.name')
                ->label(__('capell-admin::table.language'))
                ->size('sm')
                ->sortable(),
            TextColumn::make('ai_discovery_state')
                ->label(__('capell-seo-suite::generic.ai_discovery_state'))
                ->state(fn (Page $record): string => self::profileFor($record)?->include_in_ai_index ? 'included' : 'excluded')
                ->formatStateUsing(fn (string $state): string => __('capell-seo-suite::generic.ai_discovery_' . $state))
                ->badge()
                ->color(fn (string $state): string => $state === 'included' ? 'success' : 'gray')
                ->sortable(false),
            TextColumn::make('ai_discovery_summary')
                ->label(__('capell-seo-suite::generic.ai_discovery_summary_status'))
                ->state(fn (Page $record): string => trim((string) self::profileFor($record)?->summary) !== '' ? 'ready' : 'missing')
                ->formatStateUsing(fn (string $state): string => __('capell-seo-suite::generic.ai_discovery_summary_' . $state))
                ->badge()
                ->color(fn (string $state): string => $state === 'ready' ? 'success' : 'warning')
                ->sortable(false),
            TextColumn::make('ai_discovery_readiness_issues')
                ->label(__('capell-seo-suite::generic.ai_discovery_readiness_issues'))
                ->state(fn (Page $record): string => self::readinessIssueStateFor($record))
                ->formatStateUsing(fn (string $state): string => $state === 'disabled'
                    ? __('capell-seo-suite::generic.ai_discovery_audit_disabled')
                    : $state)
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'disabled' => 'gray',
                    '0' => 'success',
                    default => 'warning',
                })
                ->sortable(false),
            TextColumn::make('ai_discovery_markdown')
                ->label(__('capell-seo-suite::generic.ai_discovery_snapshot_kind_page_markdown'))
                ->state(fn (Page $record): string => self::markdownStateFor($record))
                ->formatStateUsing(fn (string $state): string => __('capell-seo-suite::generic.ai_discovery_markdown_' . $state))
                ->url(fn (Page $record): ?string => self::markdownUrlFor($record))
                ->openUrlInNewTab()
                ->badge()
                ->color(fn (string $state): string => $state === 'available' ? 'success' : 'gray')
                ->sortable(false),
            DateColumn::make('updated_at')
                ->label(__('capell-admin::table.updated_at'))
                ->size('sm')
                ->sortable(),
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    protected static function getTableFilters(): array
    {
        return [
            SelectFilter::make('site_id')
                ->label(__('capell-admin::table.site'))
                ->relationship('site', 'name'),
            TernaryFilter::make('include_in_ai_index')
                ->label(__('capell-seo-suite::form.ai_discovery_include_in_ai_index'))
                ->trueLabel(__('capell-seo-suite::generic.ai_discovery_included'))
                ->falseLabel(__('capell-seo-suite::generic.ai_discovery_excluded'))
                ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                    true, '1', 1 => self::whereIncluded($query, true),
                    false, '0', 0 => self::whereIncluded($query, false),
                    default => $query,
                }),
            TernaryFilter::make('missing_ai_summary')
                ->label(__('capell-seo-suite::generic.ai_discovery_summary_status'))
                ->trueLabel(__('capell-seo-suite::generic.ai_discovery_summary_missing'))
                ->falseLabel(__('capell-seo-suite::generic.ai_discovery_summary_ready'))
                ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                    true, '1', 1 => self::whereSummaryMissing($query),
                    false, '0', 0 => self::whereSummaryReady($query),
                    default => $query,
                }),
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    protected static function getTableActions(): array
    {
        return [
            Action::make('edit_ai_discovery')
                ->label(__('capell-seo-suite::generic.ai_discovery_edit_settings'))
                ->icon('heroicon-o-adjustments-horizontal')
                ->modalHeading(fn (Page $record): string => __('capell-seo-suite::generic.ai_discovery_edit_settings_for_page', [
                    'page' => $record->name,
                ]))
                ->tooltip(fn (Page $record): string => __('capell-seo-suite::generic.ai_discovery_edit_settings_for_page', [
                    'page' => $record->name,
                ]))
                ->fillForm(fn (Page $record): array => self::profileFormState($record))
                ->schema(self::profileFormSchema())
                ->action(function (Page $record, Action $action, array $data): void {
                    self::updateProfile($record, $data);
                    $action->success();
                })
                ->successNotificationTitle(__('capell-seo-suite::generic.ai_discovery_updated')),
            Action::make('fill_ai_summary')
                ->label(__('capell-seo-suite::generic.ai_discovery_fill_summary'))
                ->icon('heroicon-o-sparkles')
                ->requiresConfirmation(fn (Page $record): bool => trim((string) self::profileFor($record)?->summary) !== '')
                ->modalHeading(__('capell-seo-suite::generic.ai_discovery_replace_summary_heading'))
                ->modalDescription(__('capell-seo-suite::generic.ai_discovery_replace_summary_description'))
                ->action(function (Page $record, Action $action): void {
                    self::fillSummary($record);
                    $action->success();
                })
                ->successNotificationTitle(__('capell-seo-suite::generic.ai_discovery_updated')),
            Action::make('include_ai_index')
                ->label(__('capell-seo-suite::generic.ai_discovery_include'))
                ->icon('heroicon-o-check-circle')
                ->visible(fn (Page $record): bool => ! self::includeInAiIndexFor($record))
                ->action(function (Page $record, Action $action): void {
                    self::updateInclusion($record, true);
                    $action->success();
                })
                ->successNotificationTitle(__('capell-seo-suite::generic.ai_discovery_updated')),
            Action::make('exclude_ai_index')
                ->label(__('capell-seo-suite::generic.ai_discovery_exclude'))
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->visible(fn (Page $record): bool => self::includeInAiIndexFor($record))
                ->requiresConfirmation()
                ->action(function (Page $record, Action $action): void {
                    self::updateInclusion($record, false);
                    $action->success();
                })
                ->successNotificationTitle(__('capell-seo-suite::generic.ai_discovery_updated')),
            Action::make('preview_markdown')
                ->label(__('capell-seo-suite::generic.ai_discovery_preview_markdown'))
                ->icon('heroicon-o-document-text')
                ->url(fn (Page $record): ?string => self::markdownUrlFor($record))
                ->openUrlInNewTab()
                ->visible(fn (Page $record): bool => self::markdownUrlFor($record) !== null),
            Action::make('edit_page')
                ->label(__('capell-seo-suite::generic.ai_discovery_edit_page'))
                ->icon('heroicon-o-pencil-square')
                ->url(fn (Page $record): ?string => GetEditPageResourceUrlAction::run($record)),
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    protected static function getBulkActions(): array
    {
        return [
            BulkAction::make('include_ai_index')
                ->label(__('capell-seo-suite::generic.ai_discovery_include'))
                ->icon('heroicon-o-check-circle')
                ->action(function (EloquentCollection $records): void {
                    $records->each(function (Model $record): void {
                        if ($record instanceof Page) {
                            self::updateInclusion($record, true);
                        }
                    });
                    self::notifyUpdated();
                })
                ->deselectRecordsAfterCompletion(),
            BulkAction::make('exclude_ai_index')
                ->label(__('capell-seo-suite::generic.ai_discovery_exclude'))
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function (EloquentCollection $records): void {
                    $records->each(function (Model $record): void {
                        if ($record instanceof Page) {
                            self::updateInclusion($record, false);
                        }
                    });
                    self::notifyUpdated();
                })
                ->deselectRecordsAfterCompletion(),
        ];
    }

    private static function fillSummary(Page $record): AiDiscoveryPageProfile
    {
        $site = self::siteFor($record);
        $language = self::languageFor($record);

        throw_unless($site instanceof Site && $language instanceof Language, LogicException::class, 'AI Discovery requires a page site and site language.');

        $profile = FillAiDiscoveryPageSummaryAction::run($record, $site, $language);
        self::forgetRecordCache($record);

        return $profile;
    }

    private static function updateInclusion(Page $record, bool $includeInAiIndex): AiDiscoveryPageProfile
    {
        $site = self::siteFor($record);
        $language = self::languageFor($record);

        throw_unless($site instanceof Site && $language instanceof Language, LogicException::class, 'AI Discovery requires a page site and site language.');

        $profile = UpdateAiDiscoveryPageInclusionAction::run($record, $site, $language, $includeInAiIndex);
        self::forgetRecordCache($record);

        return $profile;
    }

    /**
     * @return array<int, Checkbox|TextInput|Textarea>
     */
    private static function profileFormSchema(): array
    {
        return [
            Checkbox::make('include_in_ai_index')
                ->label(__('capell-seo-suite::form.ai_discovery_include_in_ai_index'))
                ->live(),
            TextInput::make('section')
                ->label(__('capell-seo-suite::form.ai_discovery_section'))
                ->placeholder(__('capell-seo-suite::generic.ai_discovery_default_section_placeholder'))
                ->maxLength(255),
            TextInput::make('priority')
                ->label(__('capell-seo-suite::form.ai_discovery_priority'))
                ->numeric()
                ->minValue(0)
                ->maxValue(1000),
            Textarea::make('summary')
                ->label(__('capell-seo-suite::form.ai_discovery_summary'))
                ->rows(3)
                ->columnSpanFull(),
            Textarea::make('markdown_override')
                ->label(__('capell-seo-suite::form.ai_discovery_markdown_override'))
                ->rows(6)
                ->columnSpanFull(),
            TextInput::make('exclude_reason')
                ->label(__('capell-seo-suite::form.ai_discovery_exclude_reason'))
                ->maxLength(255)
                ->visible(fn (Get $get): bool => $get('include_in_ai_index') !== true)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array{include_in_ai_index: bool, section: string, priority: int, summary: ?string, markdown_override: ?string, exclude_reason: ?string}
     */
    private static function profileFormState(Page $record): array
    {
        $profile = self::profileFor($record);
        $siteProfile = self::siteProfileFor($record);

        return [
            'include_in_ai_index' => self::includeInAiIndexFor($record),
            'section' => $profile->section ?? ($siteProfile instanceof AiDiscoverySiteProfile ? $siteProfile->default_section : 'Pages'),
            'priority' => $profile->priority ?? 500,
            'summary' => $profile?->summary,
            'markdown_override' => $profile?->markdown_override,
            'exclude_reason' => $profile?->exclude_reason,
        ];
    }

    /**
     * @param  array{include_in_ai_index?: mixed, section?: mixed, priority?: mixed, summary?: mixed, markdown_override?: mixed, exclude_reason?: mixed}  $data
     */
    private static function updateProfile(Page $record, array $data): AiDiscoveryPageProfile
    {
        $site = self::siteFor($record);
        $language = self::languageFor($record);

        throw_unless($site instanceof Site && $language instanceof Language, LogicException::class, 'AI Discovery requires a page site and site language.');

        $profile = UpdateAiDiscoveryPageProfileAction::run($record, $site, $language, $data);
        self::forgetRecordCache($record);

        return $profile;
    }

    private static function profileFor(Page $record): ?AiDiscoveryPageProfile
    {
        $site = self::siteFor($record);
        $language = self::languageFor($record);
        $cacheKey = self::recordCacheKey($record);
        $cachedProfile = self::cachedProfile($cacheKey);

        if ($cachedProfile['hit']) {
            return $cachedProfile['profile'];
        }

        if (! $site instanceof Site || ! $language instanceof Language) {
            return self::putCachedProfile($cacheKey, null);
        }

        $profile = AiDiscoveryPageProfile::query()
            ->where('page_id', $record->getKey())
            ->where('site_id', $site->getKey())
            ->where('language_id', $language->getKey())
            ->first();

        return self::putCachedProfile($cacheKey, $profile instanceof AiDiscoveryPageProfile ? $profile : null);
    }

    private static function includeInAiIndexFor(Page $record): bool
    {
        $profile = self::profileFor($record);

        if ($profile instanceof AiDiscoveryPageProfile) {
            return $profile->include_in_ai_index;
        }

        $siteProfile = self::siteProfileFor($record);

        return $siteProfile instanceof AiDiscoverySiteProfile ? $siteProfile->default_include_pages : true;
    }

    private static function readinessIssueCountFor(Page $record): int
    {
        $site = self::siteFor($record);
        $language = self::languageFor($record);
        $cacheKey = self::recordCacheKey($record);
        $cachedCount = self::cachedReadinessIssueCount($cacheKey);

        if ($cachedCount['hit']) {
            return $cachedCount['count'];
        }

        if (! $site instanceof Site || ! $language instanceof Language) {
            return self::putCachedReadinessIssueCount($cacheKey, 0);
        }

        return self::putCachedReadinessIssueCount($cacheKey, BuildAiReadinessAuditAction::run($record, $site, $language)->count());
    }

    private static function readinessIssueStateFor(Page $record): string
    {
        if (! self::auditEnabled()) {
            return 'disabled';
        }

        return (string) self::readinessIssueCountFor($record);
    }

    private static function markdownStateFor(Page $record): string
    {
        $profile = self::siteProfileFor($record);

        if (! $profile instanceof AiDiscoverySiteProfile || ! $profile->markdown_pages_enabled) {
            return 'disabled';
        }

        return self::markdownUrlFor($record) !== null ? 'available' : 'missing_url';
    }

    private static function markdownUrlFor(Page $record): ?string
    {
        $profile = self::siteProfileFor($record);

        if (! $profile instanceof AiDiscoverySiteProfile || ! $profile->markdown_pages_enabled) {
            return null;
        }

        if (! self::includeInAiIndexFor($record)) {
            return null;
        }

        if (! self::isDiscoverableForMarkdown($record)) {
            return null;
        }

        $record->loadMissing('pageUrl.siteDomain');

        $url = trim($record->pageUrl->full_url ?? '');

        if ($url === '') {
            return null;
        }

        $trimmedUrl = rtrim($url, '/');
        $path = parse_url($trimmedUrl, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return $trimmedUrl . '/index.md';
        }

        return $trimmedUrl . '.md';
    }

    private static function siteProfileFor(Page $record): ?AiDiscoverySiteProfile
    {
        $site = self::siteFor($record);
        $language = self::languageFor($record);

        if (! $site instanceof Site || ! $language instanceof Language) {
            return null;
        }

        $cacheKey = sprintf('%s:%s', $site->getKey(), $language->getKey());
        $cachedProfile = self::cachedSiteProfile($cacheKey);

        if ($cachedProfile['hit']) {
            return $cachedProfile['profile'];
        }

        $profile = AiDiscoverySiteProfile::query()
            ->where('site_id', $site->getKey())
            ->where('language_id', $language->getKey())
            ->first();

        return self::putCachedSiteProfile($cacheKey, $profile instanceof AiDiscoverySiteProfile ? $profile : null);
    }

    private static function isDiscoverableForMarkdown(Page $record): bool
    {
        $site = self::siteFor($record);
        $language = self::languageFor($record);
        $cacheKey = self::recordCacheKey($record);
        $cachedDiscoverability = self::cachedMarkdownDiscoverability($cacheKey);

        if ($cachedDiscoverability['hit']) {
            return $cachedDiscoverability['discoverable'];
        }

        if (! $site instanceof Site || ! $language instanceof Language) {
            return self::putCachedMarkdownDiscoverability($cacheKey, false);
        }

        return self::putCachedMarkdownDiscoverability($cacheKey, PageIsDiscoverableForAiDiscoveryAction::run($record, $site, $language));
    }

    private static function siteFor(Page $record): ?Site
    {
        $record->loadMissing('site.language');

        return $record->site instanceof Site ? $record->site : null;
    }

    private static function languageFor(Page $record): ?Language
    {
        $site = self::siteFor($record);

        return $site?->language instanceof Language ? $site->language : null;
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private static function whereIncluded(Builder $query, bool $included): Builder
    {
        return $query->where(function (Builder $nestedQuery) use ($included): void {
            self::whereAiProfile($nestedQuery, function (QueryBuilder $profileQuery) use ($included): void {
                $profileQuery->where('include_in_ai_index', $included);
            })->orWhere(function (Builder $missingProfileQuery) use ($included): void {
                $missingProfileQuery
                    ->whereNotExists(self::profileExistsQuery())
                    ->whereExists(self::siteProfileDefaultQuery($included));

                if ($included) {
                    $missingProfileQuery->orWhere(function (Builder $missingSiteProfileQuery): void {
                        $missingSiteProfileQuery
                            ->whereNotExists(self::profileExistsQuery())
                            ->whereNotExists(self::siteProfileExistsQuery());
                    });
                }
            });
        });
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private static function whereSummaryMissing(Builder $query): Builder
    {
        return $query->where(function (Builder $nestedQuery): void {
            self::whereAiProfile($nestedQuery, function (QueryBuilder $profileQuery): void {
                $profileQuery->where(function (QueryBuilder $summaryQuery): void {
                    $summaryQuery
                        ->whereNull('summary')
                        ->orWhere('summary', '');
                });
            })->orWhereNotExists(self::profileExistsQuery());
        });
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private static function whereSummaryReady(Builder $query): Builder
    {
        return self::whereAiProfile($query, function (QueryBuilder $profileQuery): void {
            $profileQuery
                ->whereNotNull('summary')
                ->where('summary', '!=', '');
        });
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private static function whereAiProfile(Builder $query, Closure $constraint): Builder
    {
        return $query->whereExists(function (QueryBuilder $profileQuery) use ($constraint): void {
            self::constrainProfileQuery($profileQuery);
            $constraint($profileQuery);
        });
    }

    private static function profileExistsQuery(): Closure
    {
        return function (QueryBuilder $profileQuery): void {
            self::constrainProfileQuery($profileQuery);
        };
    }

    private static function siteProfileDefaultQuery(bool $included): Closure
    {
        return function (QueryBuilder $siteProfileQuery) use ($included): void {
            self::constrainSiteProfileQuery($siteProfileQuery);
            $siteProfileQuery->where('ai_discovery_site_profiles.default_include_pages', $included);
        };
    }

    private static function siteProfileExistsQuery(): Closure
    {
        return function (QueryBuilder $siteProfileQuery): void {
            self::constrainSiteProfileQuery($siteProfileQuery);
        };
    }

    private static function constrainProfileQuery(QueryBuilder $profileQuery): void
    {
        $profileQuery
            ->selectRaw('1')
            ->from('ai_discovery_page_profiles')
            ->whereColumn('ai_discovery_page_profiles.page_id', 'pages.id')
            ->whereColumn('ai_discovery_page_profiles.site_id', 'pages.site_id')
            ->whereExists(function (QueryBuilder $siteQuery): void {
                $siteQuery
                    ->selectRaw('1')
                    ->from('sites')
                    ->whereColumn('sites.id', 'pages.site_id')
                    ->whereColumn('sites.language_id', 'ai_discovery_page_profiles.language_id');
            });
    }

    private static function constrainSiteProfileQuery(QueryBuilder $siteProfileQuery): void
    {
        $siteProfileQuery
            ->selectRaw('1')
            ->from('ai_discovery_site_profiles')
            ->whereColumn('ai_discovery_site_profiles.site_id', 'pages.site_id')
            ->whereExists(function (QueryBuilder $siteQuery): void {
                $siteQuery
                    ->selectRaw('1')
                    ->from('sites')
                    ->whereColumn('sites.id', 'pages.site_id')
                    ->whereColumn('sites.language_id', 'ai_discovery_site_profiles.language_id');
            });
    }

    private static function forgetRecordCache(Page $record): void
    {
        $cacheKey = self::recordCacheKey($record);

        unset(
            self::$profiles[$cacheKey],
            self::$readinessIssueCounts[$cacheKey],
            self::$markdownDiscoverability[$cacheKey],
        );

        $request = self::currentRequest();

        if (! $request instanceof Request) {
            return;
        }

        self::forgetRequestCacheValue($request, self::REQUEST_PROFILE_CACHE_KEY, $cacheKey);
        self::forgetRequestCacheValue($request, self::REQUEST_READINESS_ISSUE_COUNT_CACHE_KEY, $cacheKey);
        self::forgetRequestCacheValue($request, self::REQUEST_MARKDOWN_DISCOVERABILITY_CACHE_KEY, $cacheKey);
    }

    /**
     * @return array{hit: bool, profile: AiDiscoveryPageProfile|null}
     */
    private static function cachedProfile(string $cacheKey): array
    {
        $request = self::currentRequest();

        if (! $request instanceof Request) {
            return [
                'hit' => array_key_exists($cacheKey, self::$profiles),
                'profile' => self::$profiles[$cacheKey] ?? null,
            ];
        }

        $cache = self::requestProfileCache($request);

        return [
            'hit' => array_key_exists($cacheKey, $cache),
            'profile' => $cache[$cacheKey] ?? null,
        ];
    }

    private static function putCachedProfile(string $cacheKey, ?AiDiscoveryPageProfile $profile): ?AiDiscoveryPageProfile
    {
        $request = self::currentRequest();

        if (! $request instanceof Request) {
            self::$profiles[$cacheKey] = $profile;

            return $profile;
        }

        $cache = self::requestProfileCache($request);
        $cache[$cacheKey] = $profile;
        $request->attributes->set(self::REQUEST_PROFILE_CACHE_KEY, $cache);

        return $profile;
    }

    /**
     * @return array{hit: bool, profile: AiDiscoverySiteProfile|null}
     */
    private static function cachedSiteProfile(string $cacheKey): array
    {
        $request = self::currentRequest();

        if (! $request instanceof Request) {
            return [
                'hit' => array_key_exists($cacheKey, self::$siteProfiles),
                'profile' => self::$siteProfiles[$cacheKey] ?? null,
            ];
        }

        $cache = self::requestSiteProfileCache($request);

        return [
            'hit' => array_key_exists($cacheKey, $cache),
            'profile' => $cache[$cacheKey] ?? null,
        ];
    }

    private static function putCachedSiteProfile(string $cacheKey, ?AiDiscoverySiteProfile $profile): ?AiDiscoverySiteProfile
    {
        $request = self::currentRequest();

        if (! $request instanceof Request) {
            self::$siteProfiles[$cacheKey] = $profile;

            return $profile;
        }

        $cache = self::requestSiteProfileCache($request);
        $cache[$cacheKey] = $profile;
        $request->attributes->set(self::REQUEST_SITE_PROFILE_CACHE_KEY, $cache);

        return $profile;
    }

    /**
     * @return array{hit: bool, count: int}
     */
    private static function cachedReadinessIssueCount(string $cacheKey): array
    {
        $request = self::currentRequest();

        if (! $request instanceof Request) {
            return [
                'hit' => array_key_exists($cacheKey, self::$readinessIssueCounts),
                'count' => self::$readinessIssueCounts[$cacheKey] ?? 0,
            ];
        }

        $cache = self::requestReadinessIssueCountCache($request);

        return [
            'hit' => array_key_exists($cacheKey, $cache),
            'count' => $cache[$cacheKey] ?? 0,
        ];
    }

    private static function putCachedReadinessIssueCount(string $cacheKey, int $count): int
    {
        $request = self::currentRequest();

        if (! $request instanceof Request) {
            self::$readinessIssueCounts[$cacheKey] = $count;

            return $count;
        }

        $cache = self::requestReadinessIssueCountCache($request);
        $cache[$cacheKey] = $count;
        $request->attributes->set(self::REQUEST_READINESS_ISSUE_COUNT_CACHE_KEY, $cache);

        return $count;
    }

    /**
     * @return array{hit: bool, discoverable: bool}
     */
    private static function cachedMarkdownDiscoverability(string $cacheKey): array
    {
        $request = self::currentRequest();

        if (! $request instanceof Request) {
            return [
                'hit' => array_key_exists($cacheKey, self::$markdownDiscoverability),
                'discoverable' => self::$markdownDiscoverability[$cacheKey] ?? false,
            ];
        }

        $cache = self::requestMarkdownDiscoverabilityCache($request);

        return [
            'hit' => array_key_exists($cacheKey, $cache),
            'discoverable' => $cache[$cacheKey] ?? false,
        ];
    }

    private static function putCachedMarkdownDiscoverability(string $cacheKey, bool $discoverable): bool
    {
        $request = self::currentRequest();

        if (! $request instanceof Request) {
            self::$markdownDiscoverability[$cacheKey] = $discoverable;

            return $discoverable;
        }

        $cache = self::requestMarkdownDiscoverabilityCache($request);
        $cache[$cacheKey] = $discoverable;
        $request->attributes->set(self::REQUEST_MARKDOWN_DISCOVERABILITY_CACHE_KEY, $cache);

        return $discoverable;
    }

    /**
     * @return array<string, AiDiscoveryPageProfile|null>
     */
    private static function requestProfileCache(Request $request): array
    {
        $cache = $request->attributes->get(self::REQUEST_PROFILE_CACHE_KEY, []);

        return is_array($cache) ? $cache : [];
    }

    /**
     * @return array<string, AiDiscoverySiteProfile|null>
     */
    private static function requestSiteProfileCache(Request $request): array
    {
        $cache = $request->attributes->get(self::REQUEST_SITE_PROFILE_CACHE_KEY, []);

        return is_array($cache) ? $cache : [];
    }

    /**
     * @return array<string, int>
     */
    private static function requestReadinessIssueCountCache(Request $request): array
    {
        $cache = $request->attributes->get(self::REQUEST_READINESS_ISSUE_COUNT_CACHE_KEY, []);

        return is_array($cache) ? $cache : [];
    }

    /**
     * @return array<string, bool>
     */
    private static function requestMarkdownDiscoverabilityCache(Request $request): array
    {
        $cache = $request->attributes->get(self::REQUEST_MARKDOWN_DISCOVERABILITY_CACHE_KEY, []);

        return is_array($cache) ? $cache : [];
    }

    private static function forgetRequestCacheValue(Request $request, string $attribute, string $cacheKey): void
    {
        $cache = $request->attributes->get($attribute, []);

        if (! is_array($cache)) {
            return;
        }

        unset($cache[$cacheKey]);
        $request->attributes->set($attribute, $cache);
    }

    private static function currentRequest(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request instanceof Request ? $request : null;
    }

    private static function recordCacheKey(Page $record): string
    {
        $siteId = self::siteFor($record)?->getKey() ?? 'none';
        $languageId = self::languageFor($record)?->getKey() ?? 'none';

        return sprintf('%s:%s:%s', $record->getKey(), $siteId, $languageId);
    }

    private static function notifyUpdated(): void
    {
        Notification::make('ai-discovery-updated')
            ->title(__('capell-seo-suite::generic.ai_discovery_updated'))
            ->success()
            ->send();
    }

    private static function auditEnabled(): bool
    {
        try {
            return resolve(SeoSuiteSettings::class)->ai_discovery_audit_enabled;
        } catch (Throwable) {
            return true;
        }
    }
}
