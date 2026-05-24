<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Data;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

final class ResolvedAgentDeliveryPageData extends Data
{
    /**
     * @param  Pageable<Model>|null  $page
     */
    public function __construct(
        public readonly ?Pageable $page,
        public readonly ?Site $site,
        public readonly ?Language $language,
        public readonly ?AgentDeliveryPageData $delivery,
    ) {}

    public function found(): bool
    {
        return $this->page instanceof Pageable
            && $this->site instanceof Site
            && $this->language instanceof Language
            && $this->delivery instanceof AgentDeliveryPageData;
    }
}
