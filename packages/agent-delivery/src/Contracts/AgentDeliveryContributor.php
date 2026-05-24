<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Contracts;

use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;

interface AgentDeliveryContributor
{
    public const string TAG = 'capell.agent-delivery.contributor';

    /**
     * @param  Pageable<Model>  $page
     * @return array<string, mixed>
     */
    public function metadata(Pageable $page, Site $site, Language $language): array;

    /**
     * @param  Pageable<Model>  $page
     * @return list<AgentDeliveryChunkData>
     */
    public function chunks(Pageable $page, Site $site, Language $language): array;
}
