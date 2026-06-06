<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Http\Controllers;

use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseNavigationAction;
use Capell\KnowledgeBase\Data\PublicKnowledgeBaseNavigationItemData;
use Illuminate\Http\Response;

final class ShowKnowledgeBaseIndexController
{
    public function __invoke(): Response
    {
        $navigation = BuildPublicKnowledgeBaseNavigationAction::run()
            ->map(static fn (PublicKnowledgeBaseNavigationItemData $item): array => $item->toArray())
            ->all();

        return $this->cacheable(response()->view($this->viewName(), [
            'navigation' => $navigation,
        ]));
    }

    private function viewName(): string
    {
        return view()->exists('capell-theme-knowledge::knowledge-base.index')
            ? 'capell-theme-knowledge::knowledge-base.index'
            : 'capell-knowledge-base::index';
    }

    private function cacheable(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'public, max-age=300, stale-while-revalidate=300');

        return $response;
    }
}
