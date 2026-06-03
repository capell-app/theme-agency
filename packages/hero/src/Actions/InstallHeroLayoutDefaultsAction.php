<?php

declare(strict_types=1);

namespace Capell\Hero\Actions;

use Capell\Core\Enums\ContainerWidthEnum;
use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Translation;
use Capell\Core\Support\Creator\LayoutCreator;
use Capell\LayoutBuilder\Actions\CreateHeroWidgetAction;
use Capell\LayoutBuilder\Support\Creator\WidgetCreator;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static array{created: int, updated: int, skipped: int} run(bool $force = false)
 */
final class InstallHeroLayoutDefaultsAction
{
    use AsObject;

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(bool $force = false): array
    {
        resolve(LayoutCreator::class)->setup();
        resolve(WidgetCreator::class)->pageContentWidget();

        $heroWidget = CreateHeroWidgetAction::run(height: 'small', meta: [
            'color' => 'light',
            'content_align' => 'center',
            'content_width' => 'balanced',
            'media_position' => 'right',
        ]);

        $homeLayout = Layout::query()
            ->where('key', LayoutEnum::Home->value)
            ->firstOrFail();

        $this->installNeutralHomeHeroContent();

        $containers = is_array($homeLayout->containers) ? $homeLayout->containers : [];
        $hadHeroContainer = array_key_exists('hero', $containers);

        if ($hadHeroContainer && ! $force) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 1];
        }

        $homeLayout->update([
            'containers' => [
                'hero' => [
                    'meta' => [
                        'colspan' => 12,
                        'container' => ContainerWidthEnum::Full,
                    ],
                    'widgets' => [
                        ['widget_key' => $heroWidget->key],
                    ],
                ],
                ...$this->mainContainer($containers),
            ],
        ]);

        return [
            'created' => $hadHeroContainer ? 0 : 1,
            'updated' => $hadHeroContainer ? 1 : 0,
            'skipped' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $containers
     * @return array<string, mixed>
     */
    private function mainContainer(array $containers): array
    {
        $main = $containers['main'] ?? [];

        if (! is_array($main)) {
            $main = [];
        }

        return [
            'main' => [
                ...$main,
                'widgets' => [
                    ['widget_key' => 'page-content'],
                ],
            ],
        ];
    }

    private function installNeutralHomeHeroContent(): void
    {
        Page::query()
            ->where('layout_id', Layout::query()->where('key', LayoutEnum::Home->value)->value('id'))
            ->with('translations')
            ->get()
            ->each(function (Page $page): void {
                $page->translations->each(function (Model $translation): void {
                    throw_unless($translation instanceof Translation);

                    $meta = is_array($translation->meta) ? $translation->meta : [];
                    $currentHero = $meta['hero'] ?? null;
                    $updates = [];

                    if (! is_string($currentHero) || $currentHero === '' || str_contains(strtolower($currentHero), 'welcome to')) {
                        $meta['hero_title'] = 'Start with a clean foundation.';
                        $meta['hero'] = '<p>Shape this page around your content, navigation, and publishing workflow.</p>';
                        $updates['meta'] = $meta;
                    }

                    if ($this->shouldReplaceDefaultContent($translation->content)) {
                        $updates['content'] = '<p>Add the most important details for this page here. Keep it concise, useful, and easy to scan.</p>';
                    }

                    if ($updates !== []) {
                        $translation->update($updates);
                    }
                });
            });
    }

    private function shouldReplaceDefaultContent(?string $content): bool
    {
        $plainContent = trim(strtolower(strip_tags($content ?? '')));

        return $plainContent === '' || str_contains($plainContent, 'welcome to capell');
    }
}
