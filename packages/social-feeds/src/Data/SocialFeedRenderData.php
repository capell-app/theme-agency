<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Data;

final readonly class SocialFeedRenderData
{
    /**
     * @param  array<int, SocialFeedRenderItemData>  $items
     */
    public function __construct(
        public SocialFeedWidgetConfigData $config,
        public array $items,
    ) {}

    public function shouldRender(): bool
    {
        return $this->items !== [] || $this->config->emptyState === 'message';
    }
}
