<?php

declare(strict_types=1);

use Capell\Blog\Models\Article;
use Capell\Core\Database\Factories\MediaFactory;
use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Frontend\Contracts\RenderedModelTracker;
use Capell\Frontend\Data\FrontendContext;
use Capell\Frontend\Events\FrontendContextResolved;
use Capell\SeoSuite\Actions\BuildPageImageSchemaAction;
use Capell\SeoSuite\Enums\MetaSchemaEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

it('builds image schema from loaded pageable media and tracks emitted media', function (): void {
    $tracker = new class implements RenderedModelTracker
    {
        /**
         * @var array<int, int>
         */
        public array $tracked = [];

        public function track(Model $model): void
        {
            $this->tracked[] = (int) $model->getKey();
        }

        public function trackByClass(Model $model, string $modelClass): void
        {
            $this->track($model);
        }

        public function tracked(string $modelType): int
        {
            return count($this->tracked);
        }
    };
    app()->instance(RenderedModelTracker::class, $tracker);

    $primary = mediaForSchema(1, 'Primary image', [
        'caption' => 'Primary caption',
        'description' => 'Primary description',
    ]);
    $secondary = mediaForSchema(2, 'Secondary image');
    $extra = mediaForSchema(3, 'Extra image');
    $ignored = mediaForSchema(4, 'Ignored image');
    $article = new Article;
    $article->setRelation('image', $primary);
    $article->setRelation('media', capell_test_collect([$primary, $secondary, $extra, $ignored]));

    $schema = BuildPageImageSchemaAction::run($article);

    expect($schema)->toHaveCount(3)
        ->and($schema[0])->toMatchArray([
            '@type' => 'ImageObject',
            'name' => 'Primary image',
            'caption' => 'Primary caption',
            'description' => 'Primary description',
        ])
        ->and(capell_test_collect($schema)->pluck('name')->all())->toBe([
            'Primary image',
            'Secondary image',
            'Extra image',
        ])
        ->and($tracker->tracked)->toBe([1, 2, 3]);
});

it('returns no image schema when pageable media relations are not loaded', function (): void {
    expect(BuildPageImageSchemaAction::run(new Article))->toBe([]);
});

it('does not lazy-load persisted pageable media relations', function (): void {
    $page = Page::factory()->create();
    MediaFactory::new()->model($page)->image()->create();

    DB::enableQueryLog();

    expect(BuildPageImageSchemaAction::run($page))->toBe([])
        ->and(DB::getQueryLog())->toBe([]);

    DB::disableQueryLog();
});

it('hydrates page image relations when the frontend context is resolved', function (): void {
    app()->instance(RenderedModelTracker::class, new class implements RenderedModelTracker
    {
        public function track(Model $model): void {}

        public function trackByClass(Model $model, string $modelClass): void {}

        public function tracked(string $modelType): int
        {
            return 0;
        }
    });

    $site = Site::factory()->create([
        'meta' => ['meta_schema' => [MetaSchemaEnum::Image->getComponent()]],
    ]);
    $page = Page::factory()->create();
    MediaFactory::new()->model($page)->image()->create();

    event(new FrontendContextResolved(new FrontendContext(
        site: $site,
        language: null,
        page: $page,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    )));

    expect($page->relationLoaded('image'))->toBeTrue()
        ->and($page->relationLoaded('media'))->toBeTrue()
        ->and(BuildPageImageSchemaAction::run($page))->not->toBe([]);
});

it('does not hydrate page image relations when image schema is disabled', function (): void {
    $site = Site::factory()->create([
        'meta' => ['meta_schema' => [MetaSchemaEnum::Webpage->getComponent()]],
    ]);
    $page = Page::factory()->create();
    MediaFactory::new()->model($page)->image()->create();

    event(new FrontendContextResolved(new FrontendContext(
        site: $site,
        language: null,
        page: $page,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    )));

    expect($page->relationLoaded('image'))->toBeFalse()
        ->and($page->relationLoaded('media'))->toBeFalse();
});

/**
 * @param  array<string, mixed>  $customProperties
 */
function mediaForSchema(int $id, string $name, array $customProperties = []): Media
{
    return MediaFactory::new()->make([
        'id' => $id,
        'name' => $name,
        'custom_properties' => $customProperties,
        'created_at' => now(),
    ]);
}
