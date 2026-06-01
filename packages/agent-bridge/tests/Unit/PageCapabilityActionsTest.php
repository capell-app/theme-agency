<?php

declare(strict_types=1);

use Capell\AgentBridge\Actions\Pages\CreateDraftPageCapabilityAction;
use Capell\AgentBridge\Actions\Pages\DisablePageCapabilityAction;
use Capell\AgentBridge\Actions\Pages\InspectPagePublishingReadinessCapabilityAction;
use Capell\AgentBridge\Actions\Pages\UpdateDraftPageCapabilityAction;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Tests\Fixtures\FakeCapabilityAction;
use Capell\Core\Models\Page;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

it('previews draft page creation with the validated payload', function (): void {
    $payload = [
        'name' => 'Campaign landing page',
        'site_id' => 1,
        'blueprint_id' => 2,
        'layout_id' => 3,
        'meta' => ['hidden' => true],
    ];

    $result = (new CreateDraftPageCapabilityAction)->preview(pageInvocation($payload));

    expect($result->ok)->toBeTrue()
        ->and($result->message)->toBe('A new unpublished page record will be created.')
        ->and($result->data['page'])->toBe($payload);
});

it('rejects draft page creation payloads without required page fields', function (): void {
    (new CreateDraftPageCapabilityAction)->preview(pageInvocation([
        'name' => 'Missing page relationships',
    ]));
})->throws(ValidationException::class);

it('rejects draft page creation without an authenticated user', function (): void {
    (new CreateDraftPageCapabilityAction)->preview(pageInvocation([
        'name' => 'Campaign landing page',
        'site_id' => 1,
        'blueprint_id' => 2,
        'layout_id' => 3,
    ], user: false));
})->throws(ValidationException::class);

it('rejects draft page creation for unassigned sites', function (): void {
    (new CreateDraftPageCapabilityAction)->preview(pageInvocation([
        'name' => 'Campaign landing page',
        'site_id' => 99,
        'blueprint_id' => 2,
        'layout_id' => 3,
    ], pageCapabilityUser(collect([1]))));
})->throws(ValidationException::class);

it('previews safe draft page updates without exposing the page id as a change', function (): void {
    $page = createAgentBridgeCapabilityPage();

    $result = (new UpdateDraftPageCapabilityAction)->preview(pageInvocation([
        'page_id' => $page->getKey(),
        'name' => 'Updated campaign page',
        'meta' => ['hidden' => false],
    ]));

    expect($result->ok)->toBeTrue()
        ->and($result->message)->toBe('The selected page will be updated with safe editable fields.')
        ->and($result->data['page_id'])->toBe($page->getKey())
        ->and($result->data['changes'])->toBe([
            'name' => 'Updated campaign page',
            'meta' => ['hidden' => false],
        ]);
});

it('rejects draft page update payloads without a page id', function (): void {
    (new UpdateDraftPageCapabilityAction)->preview(pageInvocation([
        'name' => 'Missing page id',
    ]));
})->throws(ValidationException::class);

it('previews disabling a page by ending its visibility window', function (): void {
    $page = createAgentBridgeCapabilityPage();

    $result = (new DisablePageCapabilityAction)->preview(pageInvocation([
        'page_id' => $page->getKey(),
    ]));

    expect($result->ok)->toBeTrue()
        ->and($result->message)->toBe('The page visibility window will be ended immediately.')
        ->and($result->data['page_id'])->toBe($page->getKey())
        ->and($result->data['visible_until'])->toBeString();
});

it('rejects disable page payloads without a page id', function (): void {
    (new DisablePageCapabilityAction)->preview(pageInvocation([]));
})->throws(ValidationException::class);

it('rejects readiness inspection payloads without a page id before querying pages', function (): void {
    (new InspectPagePublishingReadinessCapabilityAction)->preview(pageInvocation([]));
})->throws(ValidationException::class);

it('rejects page capabilities for pages outside the actor assigned sites', function (): void {
    $page = createAgentBridgeCapabilityPage(siteId: 2);
    $user = pageCapabilityUser(collect([1]));

    expect(fn (): CapabilityResultData => (new UpdateDraftPageCapabilityAction)->preview(pageInvocation([
        'page_id' => $page->getKey(),
        'name' => 'Forbidden update',
    ], $user)))->toThrow(ValidationException::class)
        ->and(fn (): CapabilityResultData => (new DisablePageCapabilityAction)->preview(pageInvocation([
            'page_id' => $page->getKey(),
        ], $user)))->toThrow(ValidationException::class)
        ->and(fn (): CapabilityResultData => (new InspectPagePublishingReadinessCapabilityAction)->preview(pageInvocation([
            'page_id' => $page->getKey(),
        ], $user)))->toThrow(ValidationException::class);
});

it('executes page capability lifecycle changes against persisted draft pages', function (): void {
    createAgentBridgePageCapabilityTables();

    $siteId = DB::table('sites')->insertGetId(['name' => 'Agent Site', 'language_id' => 1]);
    $blueprintId = DB::table('blueprints')->insertGetId([
        'name' => 'Page',
        'key' => 'page',
        'type' => 'page',
        'default' => true,
        'admin' => json_encode(['configurator' => 'Default']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $layoutId = DB::table('layouts')->insertGetId([
        'name' => 'Agent Layout',
        'key' => 'agent-layout',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $created = Page::withoutEvents(fn (): mixed => (new CreateDraftPageCapabilityAction)->execute(pageInvocation([
        'name' => 'Agent-created landing page',
        'site_id' => $siteId,
        'blueprint_id' => $blueprintId,
        'layout_id' => $layoutId,
        'meta' => ['source' => 'agent'],
        'admin' => ['notes' => 'created by capability'],
    ], pageCapabilityUser(collect(), isGlobalAdmin: true))));

    $page = Page::query()->findOrFail($created->data['page_id']);
    $page->pageUrls()->create([
        'site_id' => $siteId,
        'language_id' => 1,
        'url' => '/agent-created-landing-page',
        'status' => true,
    ]);

    $updated = Page::withoutEvents(fn (): mixed => (new UpdateDraftPageCapabilityAction)->execute(pageInvocation([
        'page_id' => $page->getKey(),
        'name' => 'Agent-updated landing page',
        'meta' => ['source' => 'agent', 'updated' => true],
    ], pageCapabilityUser(collect([(int) $siteId])))));
    $disabled = Page::withoutEvents(fn (): mixed => (new DisablePageCapabilityAction)->execute(pageInvocation([
        'page_id' => $page->getKey(),
    ], pageCapabilityUser(collect([(int) $siteId])))));
    $readiness = (new InspectPagePublishingReadinessCapabilityAction)->execute(pageInvocation([
        'page_id' => $page->getKey(),
    ], pageCapabilityUser(collect([(int) $siteId]))));

    expect($created->ok)->toBeTrue()
        ->and($created->data['name'])->toBe('Agent-created landing page')
        ->and($updated->data['updated'])->toContain('name', 'meta')
        ->and($page->refresh()->name)->toBe('Agent-updated landing page')
        ->and($disabled->ok)->toBeTrue()
        ->and($disabled->data['visible_until'])->toBeString()
        ->and($readiness->ok)->toBeTrue()
        ->and($readiness->data['checks'])->toMatchArray([
            'has_site' => true,
            'has_type' => true,
            'has_layout' => true,
            'has_page_urls' => true,
        ]);
});

function createAgentBridgeCapabilityPage(int $siteId = 1): Page
{
    createAgentBridgePageCapabilityTables();

    DB::table('sites')->insert([
        'id' => $siteId,
        'name' => 'Agent Site ' . $siteId,
        'language_id' => 1,
    ]);
    $blueprintId = DB::table('blueprints')->insertGetId([
        'name' => 'Page',
        'key' => 'page-' . $siteId,
        'type' => 'page',
        'default' => true,
        'admin' => json_encode(['configurator' => 'Default']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $layoutId = DB::table('layouts')->insertGetId([
        'name' => 'Agent Layout ' . $siteId,
        'key' => 'agent-layout-' . $siteId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $pageId = DB::table('pages')->insertGetId([
        'name' => 'Agent Page ' . $siteId,
        'site_id' => $siteId,
        'blueprint_id' => $blueprintId,
        'layout_id' => $layoutId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Page::query()->findOrFail($pageId);
}

function createAgentBridgePageCapabilityTables(): void
{
    if (! Schema::hasTable('sites')) {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('language_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('blueprints')) {
        Schema::create('blueprints', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key')->nullable();
            $table->string('type')->nullable();
            $table->boolean('default')->default(false);
            $table->string('group')->nullable();
            $table->json('admin')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('layouts')) {
        Schema::create('layouts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('pages')) {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('name');
            $table->foreignId('site_id')->nullable();
            $table->foreignId('blueprint_id')->nullable();
            $table->foreignId('layout_id')->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->json('meta')->nullable();
            $table->json('admin')->nullable();
            $table->timestamp('visible_from')->nullable();
            $table->timestamp('visible_until')->nullable();
            $table->integer('order')->default(0);
            $table->integer('depth')->default(0);
            $table->integer('_lft')->default(0);
            $table->integer('_rgt')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('page_urls')) {
        Schema::create('page_urls', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('language_id');
            $table->nullableMorphs('pageable');
            $table->string('url');
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }
}

/**
 * @param  array<string, mixed>  $payload
 */
function pageInvocation(array $payload, AuthenticatableContract|false|null $user = null): CapabilityInvocationData
{
    if ($user === null) {
        $user = pageCapabilityUser(collect([(int) ($payload['site_id'] ?? 1)]));
    }

    return new CapabilityInvocationData(
        capability: new CapabilityData(
            key: 'capell.pages.test',
            name: 'Page test',
            description: 'Page test capability.',
            scope: 'capell.pages.test',
            server: CapabilityServerEnum::Site,
            risk: CapabilityRiskEnum::High,
            actionClass: FakeCapabilityAction::class,
        ),
        payload: $payload,
        user: $user === false ? null : $user,
    );
}

/**
 * @param  Collection<int, int>  $assignedSiteIds
 */
function pageCapabilityUser(Collection $assignedSiteIds, bool $isGlobalAdmin = false): AuthenticatableContract
{
    return new readonly class($assignedSiteIds, $isGlobalAdmin) implements AuthenticatableContract
    {
        /**
         * @param  Collection<int, int>  $assignedSiteIds
         */
        public function __construct(
            private Collection $assignedSiteIds,
            private bool $isGlobalAdmin,
        ) {}

        /**
         * @return Collection<int, int>
         */
        public function getAssignedSiteIds(): Collection
        {
            return $this->assignedSiteIds;
        }

        public function hasRole(string $role): bool
        {
            return $this->isGlobalAdmin && $role === config('capell.roles.super_admin', 'super_admin');
        }

        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): int
        {
            return 1;
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getAuthPassword(): string
        {
            return '';
        }

        public function getRememberToken(): ?string
        {
            return null;
        }

        public function setRememberToken(mixed $value): void
        {
            //
        }

        public function getRememberTokenName(): string
        {
            return 'remember_token';
        }
    };
}
