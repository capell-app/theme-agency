<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Database\Factories;

use Capell\CampaignStudio\Data\CampaignCtaActionData;
use Capell\CampaignStudio\Data\UtmData;
use Capell\CampaignStudio\Models\CampaignCtaWidget;
use Capell\CampaignStudio\Models\CampaignGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CampaignCtaWidget>
 */
class CampaignCtaWidgetFactory extends Factory
{
    protected $model = CampaignCtaWidget::class;

    public function definition(): array
    {
        $words = $this->faker->unique()->words(3);
        $name = is_array($words) ? implode(' ', $words) : $words;

        return [
            'campaign_group_id' => CampaignGroup::factory(),
            'site_id' => null,
            'name' => Str::headline($name),
            'key' => Str::slug($name),
            'headline' => $this->faker->sentence(5),
            'body' => $this->faker->paragraph(),
            'actions' => [
                new CampaignCtaActionData(
                    label: 'Get started',
                    url: '/contact',
                    style: 'primary',
                ),
            ],
            'default_utm' => new UtmData(source: 'capell', medium: 'website'),
            'is_active' => true,
        ];
    }
}
