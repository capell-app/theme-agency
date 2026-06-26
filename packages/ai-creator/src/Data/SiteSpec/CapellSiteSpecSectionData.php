<?php

declare(strict_types=1);

namespace Capell\AiCreator\Data\SiteSpec;

use Spatie\LaravelData\Data;

/**
 * A single content section within a page. `type` references a section
 * blueprint key (see list_section_types); `content` is the HTML body the
 * agent writes; `meta` carries typed block data (content_block_type, image,
 * video, performance) without imposing a field schema.
 *
 * In v1 sections are dropped, in `order`, into the page type's default-layout
 * primary container — there is no container/placement field yet.
 */
final class CapellSiteSpecSectionData extends Data
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $type,
        public readonly string $content,
        public readonly ?string $title = null,
        public readonly ?string $summary = null,
        public readonly int $order = 0,
        public readonly array $meta = [],
    ) {}

    /**
     * `meta` defaults to empty and must stay optional: Laravel's `required`
     * rule (inferred for a non-nullable array property) rejects an empty `[]`,
     * which would break validating an exported spec whose sections carry no
     * typed block data.
     *
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'meta' => ['sometimes', 'array'],
        ];
    }
}
