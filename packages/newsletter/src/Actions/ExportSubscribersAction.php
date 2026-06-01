<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\LazyCollection;
use Lorisleiva\Actions\Concerns\AsAction;

class ExportSubscribersAction
{
    use AsAction;

    /**
     * @return LazyCollection<int, array<string, mixed>>
     */
    public function handle(int $siteId, ?Segment $segment = null): LazyCollection
    {
        $query = $segment instanceof Segment
            ? EvaluateNewsletterSegmentAction::run($segment)
            : Subscriber::query()->where('site_id', $siteId);

        return $query
            ->orderBy('id')
            ->cursor()
            ->map(static fn (Subscriber $subscriber): array => [
                'email' => $subscriber->email,
                'first_name' => $subscriber->first_name,
                'last_name' => $subscriber->last_name,
                'status' => $subscriber->status->value,
                'subscribed_at' => $subscriber->subscribed_at?->toISOString(),
                'unsubscribed_at' => $subscriber->unsubscribed_at?->toISOString(),
            ]);
    }
}
