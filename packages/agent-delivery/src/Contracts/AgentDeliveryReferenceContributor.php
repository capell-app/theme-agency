<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Contracts;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;

interface AgentDeliveryReferenceContributor
{
    public const string TAG = 'capell.agent-delivery.reference-contributor';

    /**
     * @param  Pageable<Model>  $page
     * @return list<array<string, string>>
     */
    public function references(Pageable $page, Site $site, Language $language): array;
}
