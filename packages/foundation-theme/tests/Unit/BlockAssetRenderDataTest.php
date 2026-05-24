<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\MediaFactory;
use Capell\Core\Enums\ContentStructure;
use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Models\Page;
use Capell\Core\Models\Translation;
use Capell\FoundationTheme\Actions\BuildBlockAssetRenderDataAction;
use Capell\FoundationTheme\Actions\BuildHeroRailItemsRenderDataAction;
use Capell\FoundationTheme\Actions\BuildPageContentRenderDataAction;
use Capell\LayoutBuilder\Models\Block;
use Capell\LayoutBuilder\Models\BlockAsset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

it('builds block asset render data from loaded relations only', function (): void {
    $media = MediaFactory::new()->make([
        'collection_name' => MediaCollectionEnum::Image->value,
    ]);
    $translation = new Translation;
    $translation->setRawAttributes([
        'title' => 'North Star',
        'content' => '<p>Guidance copy.</p>',
        'label' => 'North Star label',
    ]);
    $linkedPage = new Page;
    $asset = new class extends Model
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        protected $guarded = [];

        public function getMeta(string $key, mixed $default = null): mixed
        {
            return data_get($this->getAttribute('meta'), $key, $default);
        }
    };

    $asset->setRawAttributes([
        'meta' => [
            'icon' => 'heroicon-o-star',
            'position' => 'right',
            'social' => ['website' => 'https://example.test'],
            'tags' => ['Featured'],
        ],
    ]);
    $asset->setRelation('media', new Collection([$media]));
    $asset->setRelation('translation', $translation);
    $asset->setRelation('linkedPage', $linkedPage);

    $blockAsset = new BlockAsset(['asset_type' => Page::class]);
    $blockAsset->setRelation('asset', $asset);

    DB::enableQueryLog();

    $renderData = BuildBlockAssetRenderDataAction::run($blockAsset);

    expect($renderData->image)->toBe($media)
        ->and($renderData->linkedPage)->toBe($linkedPage)
        ->and($renderData->title)->toBe('North Star')
        ->and($renderData->alt)->toBe('North Star')
        ->and($renderData->content)->toBe('<p>Guidance copy.</p>')
        ->and($renderData->icon)->toBe('heroicon-o-star')
        ->and($renderData->position)->toBe('right')
        ->and($renderData->social)->toBe(['website' => 'https://example.test'])
        ->and($renderData->tags)->toBe(['Featured'])
        ->and(DB::getQueryLog())->toBe([]);

    DB::disableQueryLog();
});

it('builds page content render data from loaded relations only', function (): void {
    $page = Page::factory()->make();
    $translation = new Translation;
    $translation->setRawAttributes([
        'title' => 'Loaded page title',
        'content' => '<p>Loaded page content.</p>',
    ]);

    $page->setRelation('translation', $translation);

    DB::enableQueryLog();

    $renderData = BuildPageContentRenderDataAction::run($page, ['content'], true);

    expect($renderData->title)->toBe('Loaded page title')
        ->and($renderData->content)->toBe('<p>Loaded page content.</p>')
        ->and($renderData->contentStructure)->toBe(ContentStructure::Html)
        ->and($renderData->hasContent)->toBeTrue()
        ->and($renderData->hasTitle)->toBeFalse()
        ->and(DB::getQueryLog())->toBe([]);

    DB::disableQueryLog();
});

it('builds hero rail items from loaded explicit hero assets only', function (): void {
    $block = new Block;
    $blockAsset = heroRailBlockAsset('block-card', 'Block card');
    $page = new Page;
    $pageHeroAsset = heroRailBlockAsset('hero-card', 'Hero card');
    $pageGenericAsset = heroRailBlockAsset('card', 'Generic card');

    $block->setRelation('assets', new Collection([$blockAsset]));
    $page->setRelation('assets', new Collection([$pageHeroAsset, $pageGenericAsset]));

    DB::enableQueryLog();

    $pageItems = BuildHeroRailItemsRenderDataAction::run($block, $page, 'page');
    $mixedItems = BuildHeroRailItemsRenderDataAction::run($block, $page, 'mixed');
    $blockItems = BuildHeroRailItemsRenderDataAction::run($block, $page, 'block');

    expect($pageItems)->toHaveCount(1)
        ->and($pageItems[0]->caption)->toBe('Hero card')
        ->and($mixedItems)->toHaveCount(2)
        ->and($mixedItems[0]->caption)->toBe('Hero card')
        ->and($mixedItems[1]->caption)->toBe('Block card')
        ->and($blockItems)->toHaveCount(1)
        ->and($blockItems[0]->caption)->toBe('Block card')
        ->and(DB::getQueryLog())->toBe([]);

    DB::disableQueryLog();
});

function heroRailBlockAsset(string $role, string $caption): BlockAsset
{
    $asset = new class extends Model
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        protected $guarded = [];

        public function getMeta(string $key, mixed $default = null): mixed
        {
            return data_get($this->getAttribute('meta'), $key, $default);
        }
    };
    $asset->setRawAttributes([
        'meta' => [
            'caption' => $caption,
            'role' => $role,
        ],
    ]);

    $blockAsset = new BlockAsset(['asset_type' => Page::class]);
    $blockAsset->setRelation('asset', $asset);

    return $blockAsset;
}
