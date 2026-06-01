<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\View\Components\Block;

use Capell\CampaignStudio\Models\CampaignCtaBlock as CampaignCtaBlockModel;
use Capell\Core\Models\Site;
use Capell\Frontend\Facades\Frontend;
use Capell\LayoutBuilder\Models\Widget;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use stdClass;

class CampaignCtaBlock extends Component
{
    public ?CampaignCtaBlockModel $ctaBlock = null;

    /**
     * @param  array<array-key, mixed>  $blockData
     * @param  array<array-key, mixed>  $container
     */
    public function __construct(
        public array $container,
        public string $containerKey,
        public int $blockIndex,
        public stdClass $loop,
        public Widget $block,
        public array $blockData = [],
        public ?int $containerIndex = null,
        public ?int $containerWidth = null,
        public ?int $containerColspan = null,
        public mixed $pageSlot = null,
        public int $occurrence = 1,
    ) {
        $this->ctaBlock = $this->block->relationLoaded('campaignCtaBlock')
            ? $this->block->getRelation('campaignCtaBlock')
            : null;
    }

    /**
     * @param  Collection<int, Widget>  $blocks
     */
    public static function hydrateBlocks(Collection $blocks): void
    {
        $ctaBlockIds = $blocks
            ->map(fn (Widget $block): mixed => $block->getMeta('cta_block_id'))
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        if ($ctaBlockIds->isEmpty()) {
            return;
        }

        $site = Frontend::site();

        $ctaBlocks = CampaignCtaBlockModel::query()
            ->with('campaignGroup:id,slug,site_id')
            ->whereKey($ctaBlockIds->all())
            ->where('is_active', true)
            ->when($site instanceof Site, function (Builder $query) use ($site): void {
                $query->where(function (Builder $query) use ($site): void {
                    $query->whereNull('site_id')
                        ->orWhere('site_id', $site->getKey());
                });
            })
            ->get()
            ->keyBy(fn (CampaignCtaBlockModel $ctaBlock): int => (int) $ctaBlock->getKey());

        $blocks->each(function (Widget $block) use ($ctaBlocks): void {
            $ctaBlockId = $block->getMeta('cta_block_id');

            $block->setRelation(
                'campaignCtaBlock',
                is_numeric($ctaBlockId) ? $ctaBlocks->get((int) $ctaBlockId) : null,
            );
        });
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function render(array $data = []): View|string|Closure
    {
        return view('capell-campaign-studio::components.block.campaign-cta-block', [
            ...$data,
            'ctaBlock' => $this->ctaBlock,
        ]);
    }
}
