<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Contracts;

use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;

interface AgentDeliveryChunkContributor
{
    public const string TAG = 'capell.agent-delivery.chunk-contributor';

    /**
     * @param  Pageable<Model>  $page
     * @return list<AgentDeliveryChunkData>
     */
    public function chunks(Pageable $page, Site $site, Language $language): array;
}
