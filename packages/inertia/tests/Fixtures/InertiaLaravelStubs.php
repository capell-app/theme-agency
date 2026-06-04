<?php

declare(strict_types=1);

namespace Inertia;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Middleware
{
    /** @var string */
    protected $rootView = 'app';

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [];
    }

    public function rootView(Request $request): string
    {
        return $this->rootView;
    }
}

final class Inertia
{
    /**
     * @param  array<string, mixed>  $props
     */
    public static function render(string $component, array $props = []): InertiaResponse
    {
        return new InertiaResponse($component, $props);
    }
}

final class InertiaResponse
{
    /**
     * @param  array<string, mixed>  $props
     */
    public function __construct(
        private readonly string $component,
        private readonly array $props,
    ) {}

    public function toResponse(Request $request): JsonResponse
    {
        return new JsonResponse([
            'component' => $this->component,
            'props' => $this->props,
        ]);
    }
}
