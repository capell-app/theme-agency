<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Blocks;

use Capell\BlockLibrary\Contracts\BlockRenderer;
use Capell\BlockLibrary\Data\BlockDefinitionData;
use Capell\SocialFeeds\Actions\FetchSocialFeedRenderDataAction;
use Capell\SocialFeeds\Data\SocialFeedWidgetConfigData;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

final class SocialFeedBlockRenderer implements BlockRenderer
{
    /**
     * @param  array<string, mixed>  $state
     */
    public function render(BlockDefinitionData $definition, array $state = []): Htmlable
    {
        $config = SocialFeedWidgetConfigData::fromState(array_replace($definition->defaults, $state));
        $renderData = FetchSocialFeedRenderDataAction::run($config);

        if (! $renderData->shouldRender()) {
            return new HtmlString('');
        }

        return new HtmlString(View::make($definition->publicViewName(), [
            'feed' => $renderData,
        ])->render());
    }
}
