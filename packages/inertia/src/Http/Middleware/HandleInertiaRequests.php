<?php

declare(strict_types=1);

namespace Capell\Inertia\Http\Middleware;

use Capell\Inertia\Actions\ResolveInertiaAdapterKeyAction;
use Capell\Inertia\Actions\ResolveInertiaRootViewAction;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Override;

class HandleInertiaRequests extends Middleware
{
    /** @var string */
    protected $rootView = 'capell-inertia::app';

    #[Override]
    public function rootView(Request $request): string
    {
        return ResolveInertiaRootViewAction::run(config('capell-inertia.root_view', $this->rootView));
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'capell' => [
                'adapter' => ResolveInertiaAdapterKeyAction::run(),
            ],
        ];
    }
}
