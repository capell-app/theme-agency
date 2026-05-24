<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Contracts;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;

interface AgentDeliveryRelatedUrlContributor
{
    public const string TAG = 'capell.agent-delivery.related-url-contributor';

    /**
     * @param  Pageable<Model>  $page
     * @return list<string>
     */
    public function relatedUrls(Pageable $page, Site $site, Language $language): array;
}
