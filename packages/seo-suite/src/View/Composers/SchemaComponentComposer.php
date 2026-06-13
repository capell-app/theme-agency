<?php

declare(strict_types=1);

namespace Capell\SeoSuite\View\Composers;

use Capell\Frontend\Facades\Frontend;
use Illuminate\View\View;

final readonly class SchemaComponentComposer
{
    public function compose(View $view): void
    {
        $view->with('schemaJson', match ($view->name()) {
            'capell::components.schema.breadcrumb' => $this->preparedSchema('seo.schema.breadcrumb'),
            'capell::components.schema.image' => $this->preparedSchema('seo.schema.image'),
            'capell::components.schema.organization' => $this->preparedSchema('seo.schema.organization'),
            'capell::components.schema.webpage' => $this->preparedSchema('seo.schema.webpage'),
            default => [],
        });
    }

    /**
     * @return array<array-key, mixed>
     */
    private function preparedSchema(string $key): array
    {
        $schema = Frontend::getFrontendData($key);

        return is_array($schema) ? $schema : [];
    }
}
