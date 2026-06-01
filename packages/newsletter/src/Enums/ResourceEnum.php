<?php

declare(strict_types=1);

namespace Capell\Newsletter\Enums;

use Capell\Newsletter\Filament\Resources\FormMappings\FormMappingResource;
use Capell\Newsletter\Filament\Resources\ImportBatches\ImportBatchResource;
use Capell\Newsletter\Filament\Resources\NewsletterSends\NewsletterSendResource;
use Capell\Newsletter\Filament\Resources\NewsletterTags\NewsletterTagResource;
use Capell\Newsletter\Filament\Resources\ProviderAudiences\ProviderAudienceResource;
use Capell\Newsletter\Filament\Resources\ProviderConnections\ProviderConnectionResource;
use Capell\Newsletter\Filament\Resources\ProviderInterestMappings\ProviderInterestMappingResource;
use Capell\Newsletter\Filament\Resources\Segments\SegmentResource;
use Capell\Newsletter\Filament\Resources\Subscribers\SubscriberResource;
use Capell\Newsletter\Filament\Resources\SyncAttempts\SyncAttemptResource;

enum ResourceEnum: string
{
    case Subscriber = SubscriberResource::class;
    case ProviderConnection = ProviderConnectionResource::class;
    case ProviderAudience = ProviderAudienceResource::class;
    case ProviderInterestMapping = ProviderInterestMappingResource::class;
    case FormMapping = FormMappingResource::class;
    case NewsletterTag = NewsletterTagResource::class;
    case Segment = SegmentResource::class;
    case NewsletterSend = NewsletterSendResource::class;
    case ImportBatch = ImportBatchResource::class;
    case SyncAttempt = SyncAttemptResource::class;
}
