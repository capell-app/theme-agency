<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Support\Assets;

use Capell\Frontend\Contracts\FrontendAssetContributor;
use Capell\Frontend\Data\FrontendAssetContextData;
use Capell\Frontend\Data\FrontendAssetRequirementData;

final class FoundationThemeAssetContributor implements FrontendAssetContributor
{
    public function requirements(FrontendAssetContextData $context): array
    {
        $requirements = [
            new FrontendAssetRequirementData(
                handle: 'foundation-theme:css',
                kind: FrontendAssetRequirementData::KIND_CSS,
                source: $this->frontendCssPath(),
                buildPath: $this->frontendCssBuildPath($context),
            ),
        ];

        if ($this->shouldLoadRuntimeJavaScript($context)) {
            $requirements[] = new FrontendAssetRequirementData(
                handle: 'foundation-theme:runtime',
                kind: FrontendAssetRequirementData::KIND_JS,
                source: 'resources/js/capell-frontend.js',
                buildPath: 'vendor/capell-foundation-theme',
                defer: true,
            );
        }

        return $requirements;
    }

    private function frontendCssPath(): string
    {
        $path = config('capell-foundation-theme.tailwind.output_css', 'resources/css/capell/frontend.css');

        return is_string($path) && $path !== '' ? $path : 'resources/css/capell/frontend.css';
    }

    private function frontendCssBuildPath(FrontendAssetContextData $context): string
    {
        $buildPath = $context->theme?->getMeta('assets_path', 'build');

        return is_string($buildPath) && $buildPath !== '' ? $buildPath : 'build';
    }

    private function shouldLoadRuntimeJavaScript(FrontendAssetContextData $context): bool
    {
        return $context->runtime->usesIslands
            || $context->runtime->usesLivewire
            || ($context->runtime->modules['foundation-theme-runtime'] ?? false);
    }
}
