<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\RenderHooks;

use Capell\Frontend\Data\RenderHookContext;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Enums\RenderHookScenario;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\SeoSuite\Actions\BuildSocialMetaAction;
use Capell\SeoSuite\Actions\SchemaGraphAction;
use Capell\SeoSuite\Enums\MetaSchemaEnum;

class RegisterSeoHeadHooks
{
    /** @param RenderHookRegistry<RenderHookContext> $registry */
    public function __construct(private readonly RenderHookRegistry $registry) {}

    public function register(): void
    {
        $this->registry->register(
            RenderHookLocation::HeadClose,
            function (RenderHookContext $context): string {
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

                if ($this->shouldRenderSchemaGraph($site->meta ?? [])) {
                    $html .= SchemaGraphAction::run($page, $site, $language)->toJsonLdScript();
                }

                return $html;
            },
            scenario: RenderHookScenario::SeoMeta->value,
        );
    }

    /**
     * @param  array<string, mixed>  $siteMeta
     */
    private function shouldRenderSchemaGraph(array $siteMeta): bool
    {
        $metaSchema = $siteMeta['meta_schema'] ?? [];

        return is_array($metaSchema) && in_array(MetaSchemaEnum::Graph->getComponent(), $metaSchema, true);
    }
}
