<?php

declare(strict_types=1);

use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\CapellAdminManager;
use Capell\AgentDelivery\Support\SiteDiscovery\AgentDeliveryGeneratedOutputCoverageSource;
use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Actions\LoadPersistedAutomationRulesAction;
use Capell\AutomationStudio\Actions\PersistAutomationTriggerResultsAction;
use Capell\AutomationStudio\Actions\RecordAutomationRunAction;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromAccessApproval;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromWorkspaceStateChanged;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Capell\AutomationStudio\Support\Handlers\DispatchPublicActionAutomationActionHandler;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\CustomerPortal\Contracts\PortalPreferencesProvider;
use Capell\CustomerPortal\Contracts\PortalProfileProvider;
use Capell\CustomerPortal\Data\PortalPreferencesData;
use Capell\CustomerPortal\Data\PortalProfileData;
use Capell\CustomerPortal\Enums\PortalAccountStatus;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Support\PortalPreferencesProviderRegistry;
use Capell\CustomerPortal\Support\PortalProfileProviderRegistry;
use Capell\DemoKit\Actions\BuildDemoPageContentViewDataAction;
use Capell\DemoKit\Console\Commands\Concerns\HasLanguagesOption;
use Capell\DemoKit\Console\Commands\Concerns\HasSitesOption;
use Capell\HtmlCache\Models\CachedModelUrl;
use Capell\HtmlCache\Support\SiteDiscovery\HtmlCacheGeneratedOutputCoverageSource;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Capell\Payments\Enums\ResourceEnum as PaymentResourceEnum;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Capell\Payments\Models\PaymentWebhookEvent;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Policies\CheckoutSessionPolicy;
use Capell\Payments\Policies\PaymentCustomerPolicy;
use Capell\Payments\Policies\PaymentDisputePolicy;
use Capell\Payments\Policies\PaymentIntentPolicy;
use Capell\Payments\Policies\PaymentRefundPolicy;
use Capell\Payments\Policies\PaymentWebhookEventPolicy;
use Capell\Payments\Policies\SubscriptionPolicy;
use Capell\Payments\Providers\AdminServiceProvider as PaymentsAdminServiceProvider;
use Capell\Payments\Providers\PaymentsServiceProvider;
use Capell\PrivacyCenter\Enums\ResourceEnum as PrivacyResourceEnum;
use Capell\PrivacyCenter\Providers\AdminServiceProvider as PrivacyCenterAdminServiceProvider;
use Capell\PrivacyCenter\Providers\PrivacyCenterServiceProvider;
use Capell\PublicActions\Actions\SubmitPublicActionAction;
use Capell\PublicActions\Contracts\PublicActionHandler;
use Capell\PublicActions\Data\PublicActionResultData;
use Capell\PublicActions\Data\PublicActionSubmissionData;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Support\PublicActionHandlerRegistry;
use Capell\Search\Support\SiteDiscovery\SearchGeneratedOutputCoverageSource;
use Capell\SiteDiscovery\Data\PublicUrlRegistryEntryData;
use Capell\SiteDiscovery\Enums\PublicUrlIndexability;
use Capell\SiteDiscovery\Filament\Extenders\Site\SitemapSiteHeaderActionExtender;
use Capell\SiteDiscovery\Filament\Extenders\Site\SitemapSiteRecordActionExtender;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    if (! Schema::hasTable('automation_rules')) {
        Schema::create('automation_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('trigger_type')->index();
            $table->string('status')->index();
            $table->longText('conditions')->nullable();
            $table->longText('actions');
            $table->longText('settings')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('automation_runs')) {
        Schema::create('automation_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('automation_rule_id')->nullable();
            $table->foreignId('site_id')->nullable();
            $table->string('rule_key')->nullable()->index();
            $table->string('action_key')->nullable()->index();
            $table->string('trigger_type')->index();
            $table->string('action_type')->nullable()->index();
            $table->string('source_type')->nullable()->index();
            $table->string('source_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->unsignedSmallInteger('max_attempts')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->string('status')->index();
            $table->text('message')->nullable();
            $table->longText('payload')->nullable();
            $table->longText('context')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('cached_model_urls')) {
        Schema::create('cached_model_urls', function (Blueprint $table): void {
            $table->id();
            $table->string('url', 2048);
            $table->char('url_hash', 64);
            $table->string('path', 2048);
            $table->foreignId('site_id')->nullable();
            $table->foreignId('site_domain_id')->nullable();
            $table->foreignId('language_id')->nullable();
            $table->morphs('cacheable');
            $table->timestamp('cached_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->unique(['url_hash', 'cacheable_type', 'cacheable_id'], 'coverage_cached_model_urls_unique');
        });
    }

    if (! Schema::hasTable('public_actions')) {
        Schema::create('public_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable();
            $table->string('site_scope_key')->default('global');
            $table->string('key');
            $table->string('name');
            $table->string('status')->index();
            $table->string('handler_key');
            $table->string('success_redirect_url', 2048)->nullable();
            $table->string('failure_redirect_url', 2048)->nullable();
            $table->string('success_message')->nullable();
            $table->string('failure_message')->nullable();
            $table->json('payload_schema')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['site_scope_key', 'key']);
        });
    }

    if (! Schema::hasTable('public_action_destinations')) {
        Schema::create('public_action_destinations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('public_action_id');
            $table->string('name');
            $table->string('type');
            $table->string('status')->index();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('public_action_submissions')) {
        Schema::create('public_action_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('public_action_id');
            $table->foreignId('site_id')->nullable();
            $table->string('source_type')->nullable();
            $table->string('source_id')->nullable();
            $table->string('idempotency_key', 128)->nullable();
            $table->longText('payload');
            $table->json('metadata')->nullable();
            $table->string('status')->index();
            $table->timestamp('submitted_at')->index();
            $table->timestamps();
            $table->unique(['public_action_id', 'idempotency_key'], 'coverage_public_action_submission_unique');
        });
    }
});

it('loads active persisted automation rules into the runtime registry for a site scope', function (): void {
    $globalRule = coverageGapAutomationRule('global-form', null, AutomationRuleStatus::Active);
    $siteRule = coverageGapAutomationRule('site-form', 7, AutomationRuleStatus::Active);
    coverageGapAutomationRule('other-site-form', 99, AutomationRuleStatus::Active);
    coverageGapAutomationRule('paused-form', 7, AutomationRuleStatus::Paused);

    $registry = new AutomationRuleRegistry;
    $rules = (new LoadPersistedAutomationRulesAction($registry))->handle(siteId: 7);

    expect($rules)->toHaveCount(2)
        ->and($rules->pluck('key')->all())->toBe(['global-form', 'site-form'])
        ->and($registry->get('global-form')?->key)->toBe(coverageGapAutomationRuleKey($globalRule))
        ->and($registry->get('site-form')?->key)->toBe(coverageGapAutomationRuleKey($siteRule))
        ->and($registry->get('other-site-form'))->toBeNull()
        ->and($registry->get('paused-form'))->toBeNull();
});

it('records automation runs and updates idempotent attempts with result state', function (): void {
    $rule = coverageGapAutomationRule('form-confirmation', 12, AutomationRuleStatus::Active);
    $event = new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::FormSubmitted,
        sourceType: 'form-builder.submission',
        sourceId: 'submission-123',
        payload: ['email' => 'person@example.test'],
    );
    $action = new AutomationRuleActionData(
        key: 'send-confirmation',
        type: AutomationActionType::SendEmail,
        settings: ['template_key' => 'confirmation'],
    );

    $pending = RecordAutomationRunAction::run($event, rule: $rule, action: $action, siteId: 99);

    expect($pending)->toBeInstanceOf(AutomationRun::class)
        ->and($pending->status)->toBe(AutomationRunStatus::Pending)
        ->and($pending->site_id)->toBe(99)
        ->and($pending->finished_at)->toBeNull();

    $failed = RecordAutomationRunAction::run(
        event: $event,
        rule: $rule,
        action: $action,
        result: new AutomationActionResultData(false, 'Provider rejected request.', ['provider' => 'postmark']),
        idempotencyKey: 'forms:submission-123:send-confirmation',
        attemptNumber: 1,
        maxAttempts: 3,
    );

    $succeeded = RecordAutomationRunAction::run(
        event: $event,
        rule: $rule,
        action: $action,
        result: new AutomationActionResultData(true, 'Sent.', ['message_id' => 321]),
        idempotencyKey: 'forms:submission-123:send-confirmation',
        attemptNumber: 2,
        maxAttempts: 3,
    );

    expect($succeeded->getKey())->toBe($failed->getKey())
        ->and($succeeded->refresh()->status)->toBe(AutomationRunStatus::Succeeded)
        ->and($succeeded->attempt_number)->toBe(2)
        ->and($succeeded->max_attempts)->toBe(3)
        ->and($succeeded->message)->toBe('Sent.')
        ->and($succeeded->context)->toBe(['message_id' => 321])
        ->and(AutomationRun::query()->count())->toBe(2);
});

it('persists automation trigger result batches with rule and action idempotency keys', function (): void {
    $rule = coverageGapAutomationRule('lead-capture', 3, AutomationRuleStatus::Active, [
        [
            'key' => 'send-email',
            'type' => AutomationActionType::SendEmail->value,
            'settings' => ['template_key' => 'lead'],
        ],
        [
            'key' => 'tag-contact',
            'type' => AutomationActionType::TagContact->value,
            'settings' => ['tag' => 'lead'],
        ],
    ]);

    $event = new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::FormSubmitted,
        sourceType: 'form-builder.submission',
        sourceId: '44',
        payload: ['email' => 'lead@example.test'],
    );

    PersistAutomationTriggerResultsAction::run(
        event: $event,
        results: [
            new AutomationActionResultData(true, 'Email sent.', [
                'rule_key' => coverageGapAutomationRuleKey($rule),
                'action_key' => 'send-email',
            ]),
            new AutomationActionResultData(false, 'Contact tag failed.', [
                'rule_key' => coverageGapAutomationRuleKey($rule),
                'action_key' => 'tag-contact',
            ]),
            new AutomationActionResultData(true, 'Untracked result.'),
        ],
        idempotencyKey: 'trigger:44',
        siteId: 3,
        attemptNumber: 2,
        maxAttempts: 5,
    );

    $runs = AutomationRun::query()->orderBy('id')->get();

    expect($runs)->toHaveCount(3);

    $firstRun = $runs->get(0);
    $secondRun = $runs->get(1);
    $thirdRun = $runs->get(2);

    expect($firstRun)->toBeInstanceOf(AutomationRun::class)
        ->and($secondRun)->toBeInstanceOf(AutomationRun::class)
        ->and($thirdRun)->toBeInstanceOf(AutomationRun::class);

    if (! $firstRun instanceof AutomationRun || ! $secondRun instanceof AutomationRun || ! $thirdRun instanceof AutomationRun) {
        return;
    }

    expect(coverageGapAutomationRunStringAttribute($firstRun, 'idempotency_key'))->toBe('trigger:44:lead-capture:send-email');
    expect(coverageGapAutomationRunStatus($firstRun))->toBe(AutomationRunStatus::Succeeded);
    expect(coverageGapAutomationRunActionType($firstRun))->toBe(AutomationActionType::SendEmail);
    expect(coverageGapAutomationRunStringAttribute($secondRun, 'idempotency_key'))->toBe('trigger:44:lead-capture:tag-contact');
    expect(coverageGapAutomationRunStatus($secondRun))->toBe(AutomationRunStatus::Failed);
    expect(coverageGapAutomationRunActionType($secondRun))->toBe(AutomationActionType::TagContact);
    expect(coverageGapAutomationRunStringAttribute($thirdRun, 'idempotency_key'))->toBe('trigger:44:unknown-rule:unknown-action');
    expect($thirdRun->getAttribute('automation_rule_id'))->toBeNull();
    expect(coverageGapAutomationRunActionType($thirdRun))->toBeNull();
});

it('builds demo page content view data from page metadata translations type structure and seeded asset sections', function (): void {
    $page = new Page;
    $page->setRawAttributes([
        'id' => 44,
        'name' => 'Platform Architecture',
        'meta' => json_encode(['show_hero' => false], JSON_THROW_ON_ERROR),
    ]);
    $page->exists = true;
    $page->setRelation('translation', new Translation(['content' => '<p>Portable content.</p>']));
    $pageType = new class extends Model {};
    $pageType->setRawAttributes(['content_structure' => 'article']);
    $page->setRelation('type', $pageType);

    $block = new Widget;
    $block->setRelation('assets', new EloquentCollection([
        coverageGapDemoWidgetAsset($page, 'main', 2, 20, [
            'demo_kit_seed' => true,
            'variant' => 'metrics',
            'title' => 'Platform metrics',
            'items' => [
                ['label' => 'Deploys', 'value' => '42'],
            ],
        ]),
        coverageGapDemoWidgetAsset($page, 'main', 1, 10, [
            'demo_kit_seed' => true,
            'variant' => 'ignored',
            'title' => 'Wrong occurrence',
        ]),
        coverageGapDemoWidgetAsset($page, 'main', 2, 30, [
            'demo_kit_seed' => false,
            'variant' => 'ignored',
            'title' => 'Not seeded',
        ]),
    ]));

    $data = BuildDemoPageContentViewDataAction::run($page, $block, 'main', ['occurrence' => 2]);

    expect($data->pageName)->toBe('Platform Architecture')
        ->and($data->pageSlug)->toBe('platform-architecture')
        ->and($data->hasVisibleHero)->toBeFalse()
        ->and($data->content)->toBe('<p>Portable content.</p>')
        ->and($data->contentStructure)->toBe('article')
        ->and($data->occurrence)->toBe(2)
        ->and($data->hasAssetSections)->toBeTrue()
        ->and($data->assetSections)->toHaveCount(1)
        ->and($data->assetSections[0]['variant'])->toBe('metrics')
        ->and($data->assetSections[0]['items'])->toBe([[
            'label' => 'Deploys',
            'title' => '',
            'copy' => '',
            'value' => '42',
            'href' => '',
        ]])
        ->and($data->isContactPage)->toBeFalse();
});

it('normalizes special demo page names and suppresses blog asset sections', function (): void {
    $page = new Page;
    $page->setRawAttributes([
        'id' => 45,
        'name' => 'faq',
        'meta' => json_encode(['show_hero' => true], JSON_THROW_ON_ERROR),
    ]);
    $page->exists = true;

    $faq = BuildDemoPageContentViewDataAction::run($page, new Widget, 'main', []);

    $blog = new Page;
    $blog->setRawAttributes([
        'id' => 46,
        'name' => 'Blog',
        'meta' => json_encode([], JSON_THROW_ON_ERROR),
    ]);
    $blog->exists = true;

    $block = new Widget;
    $block->setRelation('assets', new EloquentCollection([
        coverageGapDemoWidgetAsset($blog, 'main', 1, 10, [
            'demo_kit_seed' => true,
            'variant' => 'cards',
            'title' => 'Latest',
        ]),
    ]));

    $blogData = BuildDemoPageContentViewDataAction::run($blog, $block, 'main', []);

    expect($faq->pageName)->toBe('FAQ')
        ->and($faq->pageSlug)->toBe('faq')
        ->and($blogData->assetSections)->toHaveCount(1)
        ->and($blogData->hasAssetSections)->toBeFalse();
});

it('returns indexable URLs from search generated output coverage only once', function (): void {
    $source = new SearchGeneratedOutputCoverageSource;

    $urls = $source->coveredUrls(collect([
        new PublicUrlRegistryEntryData(
            canonicalUrl: 'https://example.test/about',
            sourcePackage: 'core',
            siteKey: 1,
            languageKey: 'en',
        ),
        new PublicUrlRegistryEntryData(
            canonicalUrl: 'https://example.test/about',
            sourcePackage: 'core',
            siteKey: 1,
            languageKey: 'en',
        ),
        new PublicUrlRegistryEntryData(
            canonicalUrl: 'https://example.test/private',
            sourcePackage: 'core',
            siteKey: 1,
            languageKey: 'en',
            indexability: PublicUrlIndexability::NoIndex,
        ),
    ]));

    expect($source->key())->toBe('search')
        ->and($urls->all())->toBe(['https://example.test/about']);
});

it('reports generated output coverage from html cache records after normalizing unusable URLs', function (): void {
    CachedModelUrl::query()->create([
        'url' => 'https://example.test/about',
        'url_hash' => CachedModelUrl::hashUrl('https://example.test/about'),
        'path' => '/about',
        'cacheable_type' => Page::class,
        'cacheable_id' => 1001,
    ]);
    CachedModelUrl::query()->create([
        'url' => 'https://example.test/about',
        'url_hash' => CachedModelUrl::hashUrl('https://example.test/about'),
        'path' => '/about',
        'cacheable_type' => Page::class,
        'cacheable_id' => 1002,
    ]);
    CachedModelUrl::query()->create([
        'url' => '',
        'url_hash' => CachedModelUrl::hashUrl(''),
        'path' => '/',
        'cacheable_type' => Page::class,
        'cacheable_id' => 1003,
    ]);

    $source = new HtmlCacheGeneratedOutputCoverageSource;

    expect($source->key())->toBe('html_cache')
        ->and($source->coveredUrls(collect())->all())->toBe(['https://example.test/about']);
});

it('reports agent delivery coverage only for active page urls that are indexable and ai eligible', function (): void {
    $language = Language::query()->create([
        'name' => 'English',
        'locale' => 'en',
        'code' => 'en',
        'flag' => 'gb-eng',
        'status' => true,
        'default' => true,
        'order' => 1,
    ]);

    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, siteDomainData: [
            'scheme' => 'https',
            'domain' => 'example.test',
            'path' => null,
        ])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, ['title' => 'Covered'], slug: 'covered')
        ->create();
    $inactivePage = Page::factory()
        ->site($site)
        ->withTranslations($language, ['title' => 'Inactive'], slug: 'inactive-page')
        ->create();

    PageUrl::query()->create([
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'status' => true,
        'type' => null,
        'url' => '/covered',
        'pageable_type' => Page::class,
        'pageable_id' => $page->getKey(),
    ]);
    PageUrl::query()->create([
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'status' => false,
        'type' => null,
        'url' => '/inactive',
        'pageable_type' => Page::class,
        'pageable_id' => $inactivePage->getKey(),
    ]);

    $source = new AgentDeliveryGeneratedOutputCoverageSource;
    $urls = $source->coveredUrls(collect([
        new PublicUrlRegistryEntryData(
            canonicalUrl: 'https://example.test/covered',
            sourcePackage: 'core',
            siteKey: (int) $site->getKey(),
            languageKey: (int) $language->getKey(),
            siteId: (int) $site->getKey(),
            languageId: (int) $language->getKey(),
            isAiDiscoveryEligible: true,
        ),
        new PublicUrlRegistryEntryData(
            canonicalUrl: 'https://example.test/inactive',
            sourcePackage: 'core',
            siteKey: (int) $site->getKey(),
            languageKey: (int) $language->getKey(),
            siteId: (int) $site->getKey(),
            languageId: (int) $language->getKey(),
            isAiDiscoveryEligible: true,
        ),
        new PublicUrlRegistryEntryData(
            canonicalUrl: 'https://example.test/private',
            sourcePackage: 'core',
            siteKey: (int) $site->getKey(),
            languageKey: (int) $language->getKey(),
            siteId: (int) $site->getKey(),
            languageId: (int) $language->getKey(),
            indexability: PublicUrlIndexability::NoIndex,
            isAiDiscoveryEligible: true,
        ),
    ]));

    expect($source->key())->toBe('agent_delivery')
        ->and($urls->all())->toBe(['https://example.test/covered']);
});

it('dispatches configured automation public actions with event source context', function (): void {
    config()->set('capell-public-actions.spam_protection.enabled', []);

    app()->forgetInstance(PublicActionHandlerRegistry::class);
    app()->forgetInstance(SubmitPublicActionAction::class);
    app()->singleton(PublicActionHandlerRegistry::class);
    resolve(PublicActionHandlerRegistry::class)->register('coverage-gap.public-action', new CoverageGapPublicActionHandler);

    PublicAction::factory()->create([
        'key' => 'send-download',
        'handler_key' => 'coverage-gap.public-action',
        'success_redirect_url' => 'https://example.test/thanks',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'type' => 'email', 'required' => true],
                ['key' => 'plan', 'type' => 'text', 'required' => true],
                ['key' => 'source_type', 'type' => 'text', 'required' => true],
                ['key' => 'source_id', 'type' => 'text', 'required' => true],
            ],
        ],
    ]);

    $result = (new DispatchPublicActionAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.submission',
            sourceId: 'submission-900',
            payload: ['email' => 'person@example.test', 'plan' => 'starter', 'ignored' => 'from-event'],
        ),
        action: new AutomationRuleActionData(
            key: 'dispatch-public-action',
            type: AutomationActionType::PublicAction,
            settings: [
                'public_action_key' => 'send-download',
                'payload' => ['plan' => 'enterprise', 'ignored' => null],
            ],
        ),
    );

    expect($result->success)->toBeTrue()
        ->and($result->message)->toBe('person@example.test:enterprise')
        ->and($result->context)->toMatchArray([
            'public_action_key' => 'send-download',
            'redirect_url' => 'https://example.test/thanks',
        ]);
});

it('fails automation public action dispatch when no action key is configured', function (): void {
    $result = (new DispatchPublicActionAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.submission',
            sourceId: 'submission-901',
            payload: [],
        ),
        action: new AutomationRuleActionData(
            key: 'dispatch-public-action',
            type: AutomationActionType::PublicAction,
            settings: ['public_action_key' => ''],
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe(__('capell-automation-studio::generic.dispatcher.public_action_key_required'));
});

it('registers payment and privacy admin resources only when their packages are installed', function (): void {
    CapellAdmin::clearAdminSurfaceContributions();
    app()->singleton(CapellAdminManager::class, fn (): CapellAdminManager => new CapellAdminManager);

    CapellCore::forcePackageInstalled(PaymentsServiceProvider::$packageName);
    CapellCore::forcePackageInstalled(PrivacyCenterServiceProvider::$packageName);

    (new PaymentsAdminServiceProvider(app()))->register();
    (new PrivacyCenterAdminServiceProvider(app()))->register();

    $resources = CapellAdmin::getAdminSurfaceRegistry()->resources();

    expect($resources)->toContain(...array_map(
        static fn (PaymentResourceEnum $resource): string => $resource->value,
        PaymentResourceEnum::cases(),
    ))
        ->and($resources)->toContain(...array_map(
            static fn (PrivacyResourceEnum $resource): string => $resource->value,
            PrivacyResourceEnum::cases(),
        ))
        ->and(Gate::getPolicyFor(PaymentCustomer::class))->toBeInstanceOf(PaymentCustomerPolicy::class)
        ->and(Gate::getPolicyFor(CheckoutSession::class))->toBeInstanceOf(CheckoutSessionPolicy::class)
        ->and(Gate::getPolicyFor(PaymentIntent::class))->toBeInstanceOf(PaymentIntentPolicy::class)
        ->and(Gate::getPolicyFor(Subscription::class))->toBeInstanceOf(SubscriptionPolicy::class)
        ->and(Gate::getPolicyFor(PaymentWebhookEvent::class))->toBeInstanceOf(PaymentWebhookEventPolicy::class)
        ->and(Gate::getPolicyFor(PaymentRefund::class))->toBeInstanceOf(PaymentRefundPolicy::class)
        ->and(Gate::getPolicyFor(PaymentDispute::class))->toBeInstanceOf(PaymentDisputePolicy::class);
});

it('resolves customer portal profile and preference providers from instances and container classes', function (): void {
    $preferencesInstance = new class implements PortalPreferencesProvider
    {
        public function preferencesFor(PortalAccount $portalAccount): PortalPreferencesData
        {
            return new PortalPreferencesData(['email_updates' => true]);
        }
    };
    $profileInstance = new class implements PortalProfileProvider
    {
        public function profileFor(PortalAccount $portalAccount): PortalProfileData
        {
            return new PortalProfileData(
                accountId: 5,
                siteId: 9,
                email: 'person@example.test',
                displayName: 'Person',
                status: PortalAccountStatus::Active,
            );
        }
    };

    $preferenceRegistry = new PortalPreferencesProviderRegistry(app());
    $profileRegistry = new PortalProfileProviderRegistry(app());

    $preferenceRegistry
        ->register('instance', $preferencesInstance)
        ->register('class', CoverageGapPortalPreferencesProvider::class);
    $profileRegistry
        ->register('instance', $profileInstance)
        ->register('class', CoverageGapPortalProfileProvider::class);

    expect($preferenceRegistry->providers())->toHaveCount(2)
        ->and($preferenceRegistry->providers()[0])->toBe($preferencesInstance)
        ->and($preferenceRegistry->providers()[1])->toBeInstanceOf(CoverageGapPortalPreferencesProvider::class)
        ->and($profileRegistry->providers())->toHaveCount(2)
        ->and($profileRegistry->providers()[0])->toBe($profileInstance)
        ->and($profileRegistry->providers()[1])->toBeInstanceOf(CoverageGapPortalProfileProvider::class)
        ->and(fn () => $preferenceRegistry->register('', $preferencesInstance))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $profileRegistry->register('', $profileInstance))->toThrow(InvalidArgumentException::class);
});

it('rejects customer portal provider classes that do not implement the provider contracts', function (): void {
    $preferenceRegistry = new PortalPreferencesProviderRegistry(app());
    $profileRegistry = new PortalProfileProviderRegistry(app());

    (new ReflectionMethod($preferenceRegistry, 'register'))->invoke($preferenceRegistry, 'broken', stdClass::class);
    (new ReflectionMethod($profileRegistry, 'register'))->invoke($profileRegistry, 'broken', stdClass::class);

    expect(fn () => $preferenceRegistry->providers())->toThrow(InvalidArgumentException::class)
        ->and(fn () => $profileRegistry->providers())->toThrow(InvalidArgumentException::class);
});

it('builds sitemap site actions that preserve the selected site id', function (): void {
    $site = new Site;
    $site->forceFill(['id' => 123]);

    $headerAction = (new SitemapSiteHeaderActionExtender)->actions()[0];
    $recordAction = (new SitemapSiteRecordActionExtender)->actions()[0];

    expect($headerAction->getName())->toBe('sitemap')
        ->and($recordAction->getName())->toBe('sitemap')
        ->and($headerAction->getLabel())->toBe(__('capell-admin::button.sitemap'))
        ->and($recordAction->getLabel())->toBe(__('capell-admin::button.sitemap'))
        ->and($headerAction->record($site)->getUrl())->toContain('site_id=123')
        ->and($recordAction->record($site)->getUrl())->toContain('site_id=123');
});

it('parses demo kit site and language command options without prompting', function (): void {
    $command = new CoverageGapDemoKitOptionsCommand([
        'sites' => ' Main Site, Second Site ,,',
        'languages' => ' en, fr ,,',
    ]);

    expect($command->demoSites())->toBe(['Main Site', 'Second Site'])
        ->and($command->demoLanguages())->toBe(['en', 'fr']);
});

it('dispatches access approval automation payloads from registration events', function (): void {
    $handler = new CoverageGapCapturingAutomationHandler;
    $rules = new AutomationRuleRegistry;
    $actions = new AutomationActionRegistry;
    $rules->register(new AutomationRuleData(
        key: 'access-approved',
        name: 'Access approved',
        triggerType: AutomationTriggerType::AccessApproved,
        actions: [
            new AutomationRuleActionData('send-email', AutomationActionType::SendEmail),
        ],
    ));
    $actions->registerHandler(AutomationActionType::SendEmail, $handler);

    $registration = new class extends Model {};
    $registration->forceFill([
        'id' => 88,
        'email' => 'approved@example.test',
        'area_id' => 12,
    ]);
    $registration->exists = true;

    (new DispatchAutomationFromAccessApproval(
        new DispatchAutomationTriggerAction($rules, $actions),
    ))->handle((object) ['registration' => $registration]);

    expect($handler->events)->toHaveCount(1)
        ->and($handler->events[0]->triggerType)->toBe(AutomationTriggerType::AccessApproved)
        ->and($handler->events[0]->sourceType)->toBe('access-gate.registration')
        ->and($handler->events[0]->sourceId)->toBe('88')
        ->and($handler->events[0]->payload)->toMatchArray([
            'registration_id' => 88,
            'email' => 'approved@example.test',
            'area_id' => 12,
        ]);
});

it('dispatches workspace publication automation only for published transitions', function (): void {
    $handler = new CoverageGapCapturingAutomationHandler;
    $rules = new AutomationRuleRegistry;
    $actions = new AutomationActionRegistry;
    $rules->register(new AutomationRuleData(
        key: 'workspace-published',
        name: 'Workspace published',
        triggerType: AutomationTriggerType::PagePublished,
        actions: [
            new AutomationRuleActionData('send-email', AutomationActionType::SendEmail),
        ],
    ));
    $actions->registerHandler(AutomationActionType::SendEmail, $handler);

    $listener = new DispatchAutomationFromWorkspaceStateChanged(
        new DispatchAutomationTriggerAction($rules, $actions),
    );

    $workspace = new class extends Model {};
    $workspace->forceFill(['id' => 44]);
    $workspace->exists = true;

    $listener->handle((object) [
        'transition' => 'drafted',
        'workspace' => $workspace,
        'newStatus' => 'draft',
    ]);
    $listener->handle((object) [
        'transition' => 'published',
        'workspace' => $workspace,
        'newStatus' => 'published',
    ]);

    expect($handler->events)->toHaveCount(1)
        ->and($handler->events[0]->triggerType)->toBe(AutomationTriggerType::PagePublished)
        ->and($handler->events[0]->sourceType)->toBe('publishing-studio.workspace')
        ->and($handler->events[0]->sourceId)->toBe('44')
        ->and($handler->events[0]->payload)->toBe([
            'workspace_id' => 44,
            'transition' => 'published',
            'status' => 'published',
        ]);
});

/**
 * @param  list<array<string, mixed>>|null  $actions
 */
function coverageGapAutomationRule(
    string $key,
    ?int $siteId,
    AutomationRuleStatus $status,
    ?array $actions = null,
): AutomationRule {
    /** @var AutomationRule $rule */
    $rule = AutomationRule::query()->create([
        'site_id' => $siteId,
        'key' => $key,
        'name' => str($key)->headline()->toString(),
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => $status,
        'conditions' => [],
        'actions' => $actions ?? [[
            'key' => 'send-email',
            'type' => AutomationActionType::SendEmail->value,
            'settings' => ['template_key' => 'welcome'],
        ]],
        'settings' => [],
    ]);

    return $rule;
}

function coverageGapAutomationRuleKey(AutomationRule $rule): string
{
    return (string) $rule->getAttribute('key');
}

function coverageGapAutomationRunStringAttribute(AutomationRun $run, string $key): ?string
{
    $value = $run->getAttribute($key);

    return is_string($value) ? $value : null;
}

function coverageGapAutomationRunStatus(AutomationRun $run): ?AutomationRunStatus
{
    $value = $run->getAttribute('status');

    return $value instanceof AutomationRunStatus ? $value : null;
}

function coverageGapAutomationRunActionType(AutomationRun $run): ?AutomationActionType
{
    $value = $run->getAttribute('action_type');

    return $value instanceof AutomationActionType ? $value : null;
}

/**
 * @param  array<string, mixed>  $meta
 */
function coverageGapDemoWidgetAsset(Page $page, string $container, int $occurrence, int $order, array $meta): WidgetAsset
{
    $asset = new WidgetAsset;
    $asset->forceFill([
        'pageable_type' => $page->getMorphClass(),
        'pageable_id' => $page->getKey(),
        'container' => $container,
        'occurrence' => $occurrence,
        'order' => $order,
        'meta' => $meta,
    ]);

    return $asset;
}

final class CoverageGapDemoKitOptionsCommand extends Command
{
    use HasLanguagesOption;
    use HasSitesOption;

    protected $signature = 'coverage-gap:demo-kit-options';

    /** @param array<string, mixed> $options */
    public function __construct(private readonly array $options)
    {
        parent::__construct();
    }

    /**
     * @param  string|null  $key
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function option($key = null)
    {
        return $key === null ? $this->options : ($this->options[$key] ?? null);
    }

    /** @return array<int, string> */
    public function demoSites(): array
    {
        return $this->getDemoSites();
    }

    /** @return array<int, string> */
    public function demoLanguages(): array
    {
        return $this->getDemoLanguages();
    }
}

final class CoverageGapPortalPreferencesProvider implements PortalPreferencesProvider
{
    public function preferencesFor(PortalAccount $portalAccount): PortalPreferencesData
    {
        return new PortalPreferencesData(['timezone' => 'Europe/London']);
    }
}

final class CoverageGapPortalProfileProvider implements PortalProfileProvider
{
    public function profileFor(PortalAccount $portalAccount): PortalProfileData
    {
        return new PortalProfileData(
            accountId: 1,
            siteId: 1,
            email: 'container@example.test',
            displayName: 'Container',
            status: PortalAccountStatus::Active,
        );
    }
}

final class CoverageGapCapturingAutomationHandler implements AutomationActionHandler
{
    /** @var list<AutomationTriggerEventData> */
    public array $events = [];

    /** @var list<AutomationRuleActionData> */
    public array $actions = [];

    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
    {
        $this->events[] = $event;
        $this->actions[] = $action;

        return new AutomationActionResultData(true, 'captured');
    }
}

final class CoverageGapPublicActionHandler implements PublicActionHandler
{
    public function handle(PublicActionSubmissionData $submission): PublicActionResultData
    {
        $payload = $submission->payload->values;

        return new PublicActionResultData(
            success: true,
            message: sprintf('%s:%s', $payload['email'] ?? '', $payload['plan'] ?? ''),
            redirectUrl: 'https://example.test/thanks',
        );
    }
}
