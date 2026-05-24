<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Enums\CampaignBlockComponentEnum;
use Capell\CampaignStudio\Filament\Configurators\Blocks\CampaignCtaBlockBlockConfigurator;
use Capell\CampaignStudio\Filament\Configurators\Blocks\CampaignHeroBlockConfigurator;
use Capell\CampaignStudio\Filament\Configurators\Blocks\CampaignLeadFormBlockConfigurator;
use Capell\CampaignStudio\Support\LayoutPresets\CampaignLayoutPreset;
use Capell\CampaignStudio\Support\LayoutPresets\LeadGenerationPreset;
use Capell\CampaignStudio\Support\LayoutPresets\ProductLaunchPreset;
use Capell\CampaignStudio\Support\LayoutPresets\WebinarPreset;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Layout;
use Capell\LayoutBuilder\Enums\LayoutTypeEnum;
use Capell\LayoutBuilder\Filament\Configurators\Types\BlockTypeConfigurator;
use Capell\LayoutBuilder\Models\Widget as Block;
use Lorisleiva\Actions\Concerns\AsAction;

final class InstallCampaignLayoutsAction
{
    use AsAction;

    /**
     * @var array<string, array{name: string, component: CampaignBlockComponentEnum, configurator: class-string, icon: string}>
     */
    private const array WIDGET_DEFINITIONS = [
        'campaign-hero' => [
            'name' => 'Campaign hero',
            'component' => CampaignBlockComponentEnum::CampaignHero,
            'configurator' => CampaignHeroBlockConfigurator::class,
            'icon' => 'heroicon-o-megaphone',
        ],
        'campaign-cta-block' => [
            'name' => 'Campaign CTA block',
            'component' => CampaignBlockComponentEnum::CampaignCtaBlock,
            'configurator' => CampaignCtaBlockBlockConfigurator::class,
            'icon' => 'heroicon-o-cursor-arrow-rays',
        ],
        'campaign-lead-form' => [
            'name' => 'Campaign lead form',
            'component' => CampaignBlockComponentEnum::CampaignLeadForm,
            'configurator' => CampaignLeadFormBlockConfigurator::class,
            'icon' => 'heroicon-o-clipboard-document-list',
        ],
    ];

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(bool $force = false): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($this->presets() as $preset) {
            $layout = Layout::query()->where('key', $preset->key())->first();

            if ($layout instanceof Layout && ! $force) {
                $result['skipped']++;

                continue;
            }

            $blocks = $this->blocksForPreset($preset);

            Layout::query()->updateOrCreate(
                ['key' => $preset->key()],
                [
                    'name' => $preset->name(),
                    'group' => 'CampaignStudio',
                    'containers' => $this->containersForPreset($preset, $blocks),
                    'status' => true,
                    'widgets' => collect($blocks)
                        ->map(fn (Block $block): string => $block->key)
                        ->values()
                        ->all(),
                ],
            );

            $layout instanceof Layout ? $result['updated']++ : $result['created']++;
        }

        return $result;
    }

    /**
     * @return array<int, CampaignLayoutPreset>
     */
    private function presets(): array
    {
        return [
            new LeadGenerationPreset,
            new ProductLaunchPreset,
            new WebinarPreset,
        ];
    }

    /**
     * @return array<string, Block>
     */
    private function blocksForPreset(CampaignLayoutPreset $preset): array
    {
        $blocks = [];
        $type = $this->campaignBlockType();

        foreach ($preset->blocks() as $blockDefinition) {
            $blockType = $blockDefinition['type'] ?? null;
            if (! is_string($blockType)) {
                continue;
            }

            if (! isset(self::WIDGET_DEFINITIONS[$blockType])) {
                continue;
            }

            $definition = self::WIDGET_DEFINITIONS[$blockType];
            $blockKey = $preset->key() . '-' . $blockType;

            $blocks[$blockType] = Block::query()->updateOrCreate(
                ['key' => $blockKey],
                [
                    'name' => $preset->name() . ' - ' . $definition['name'],
                    'blueprint_id' => $type->getKey(),
                    'meta' => [
                        'component' => $definition['component'],
                    ],
                    'admin' => [
                        'configurator' => $definition['configurator']::getKey(),
                        'icon' => $definition['icon'],
                    ],
                    'status' => true,
                ],
            );
        }

        return $blocks;
    }

    /**
     * @param  array<string, Block>  $blocks
     * @return array<string, array{blocks: array<int, array{block_key: string, occurrence: int}>, meta: array<string, mixed>}>
     */
    private function containersForPreset(CampaignLayoutPreset $preset, array $blocks): array
    {
        $containers = [];

        foreach ($preset->containers() as $containerDefinition) {
            $containerKey = $containerDefinition['key'] ?? null;
            if (! is_string($containerKey)) {
                continue;
            }

            if ($containerKey === '') {
                continue;
            }

            $containers[$containerKey] = [
                'widgets' => [],
                'meta' => [
                    'container' => $containerDefinition['width'] ?? null,
                ],
            ];
        }

        foreach ($preset->blocks() as $blockDefinition) {
            $containerKey = $blockDefinition['container'] ?? null;
            $blockType = $blockDefinition['type'] ?? null;
            if (! is_string($containerKey)) {
                continue;
            }

            if (! isset($containers[$containerKey])) {
                continue;
            }

            if (! is_string($blockType)) {
                continue;
            }

            if (! isset($blocks[$blockType])) {
                continue;
            }

            $containers[$containerKey]['widgets'][] = [
                'widget_key' => $blocks[$blockType]->key,
                'occurrence' => 1,
            ];
        }

        return $containers;
    }

    private function campaignBlockType(): Blueprint
    {
        return Blueprint::query()->firstOrCreate(
            [
                'key' => 'campaign',
                'type' => LayoutTypeEnum::Widget,
            ],
            [
                'name' => __('capell-campaign-studio::generic.campaign'),
                'group' => 'campaign-studio',
                'admin' => [
                    'type_configurator' => BlockTypeConfigurator::getKey(),
                    'icon' => 'heroicon-o-megaphone',
                ],
                'meta' => [
                    'component' => CampaignBlockComponentEnum::CampaignHero,
                ],
            ],
        );
    }
}
