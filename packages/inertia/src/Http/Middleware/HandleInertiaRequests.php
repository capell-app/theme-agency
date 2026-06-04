<?php

declare(strict_types=1);

namespace Capell\Inertia\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /** @var string */
    protected $rootView = 'capell-inertia::app';

    public function rootView(Request $request): string
    {
        $rootView = config('capell-inertia.root_view', $this->rootView);

        return is_string($rootView) && $rootView !== '' ? $rootView : $this->rootView;
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'capell' => [
                'adapter' => config('capell-inertia.adapter', 'vue'),
            ],
        ];
    }
}
