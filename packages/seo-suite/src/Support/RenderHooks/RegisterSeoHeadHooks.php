<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\RenderHooks;

use Capell\Frontend\Contracts\RenderHookExtensionInterface;
use Capell\Frontend\Data\RenderHookContext;
use Capell\Frontend\Facades\Frontend;
use Capell\SeoSuite\Actions\BuildSocialMetaAction;

final class RegisterSeoHeadHooks implements RenderHookExtensionInterface
{
    public function render(RenderHookContext $context): string
    {
        $item = is_array($context->item) ? $context->item : [];
        $page = $item['page'] ?? null;
        $site = $item['site'] ?? null;
        $language = $item['language'] ?? null;

        if ($page === null || $site === null || $language === null) {
            return '';
        }

        $meta = BuildSocialMetaAction::run($page, $site, $language);

        $html = view('capell::head.social-meta', [
            'meta' => $meta,
            'page' => $page,
            'site' => $site,
            'language' => $language,
        ])->render();

        $schemaGraphScript = Frontend::getFrontendData('seo.schema.graph_script');

        if (is_string($schemaGraphScript) && $schemaGraphScript !== '') {
            $html .= $schemaGraphScript;
        }

        return $html;
    }
}
