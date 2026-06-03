<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Enums;

use Filament\Support\Contracts\HasLabel;

enum SchemaTemplateTypeEnum: string implements HasLabel
{
    case Article = 'Article';
    case WebPage = 'WebPage';
    case FAQ = 'FAQPage';
    case HowTo = 'HowTo';
    case Event = 'Event';
    case LocalBusiness = 'LocalBusiness';
    case Product = 'Product';
    case Video = 'VideoObject';
    case Organization = 'Organization';

    public function getLabel(): string
    {
        return match ($this->name) {
            self::Article->name => __('capell-seo-suite::generic.schema_template_type_article'),
            self::WebPage->name => __('capell-seo-suite::generic.schema_template_type_web_page'),
            self::FAQ->name => __('capell-seo-suite::generic.schema_template_type_f_a_q'),
            self::HowTo->name => __('capell-seo-suite::generic.schema_template_type_how_to'),
            self::Event->name => __('capell-seo-suite::generic.schema_template_type_event'),
            self::LocalBusiness->name => __('capell-seo-suite::generic.schema_template_type_local_business'),
            self::Product->name => __('capell-seo-suite::generic.schema_template_type_product'),
            self::Video->name => __('capell-seo-suite::generic.schema_template_type_video'),
            self::Organization->name => __('capell-seo-suite::generic.schema_template_type_organization'),
        };
    }

    /**
     * @return list<string>
     */
    public function compatibleSchemaTypes(): array
    {
        return match ($this) {
            self::Article => ['Article', 'BlogPosting', 'NewsArticle', 'TechArticle', 'Report'],
            self::Video => ['VideoObject', 'Video'],
            default => [$this->value],
        };
    }

    public function matchesSchemaType(?string $schemaType): bool
    {
        if ($schemaType === null || $schemaType === '') {
            return $this === self::WebPage;
        }

        return in_array($schemaType, $this->compatibleSchemaTypes(), true);
    }
}
