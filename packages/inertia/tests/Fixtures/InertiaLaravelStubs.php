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
    public static string $rootView = 'app';

    public static function setRootView(string $rootView): void
    {
        self::$rootView = $rootView;
    }

    public static function optional(callable $callback): callable
    {
        return $callback;
    }

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

    public function toResponse(Request $request): Response
    {
        if ($request->headers->get('X-Inertia') !== 'true') {
            $page = [
                'component' => $this->component,
                'props' => $this->props,
            ];

            return new Response(
                '<div id="app" data-page="' . htmlspecialchars((string) json_encode($page, JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8') . '"></div>',
                Response::HTTP_OK,
                ['Content-Type' => 'text/html; charset=UTF-8'],
            );
        }

        return new JsonResponse([
            'component' => $this->component,
            'props' => $this->props,
        ]);
    }
}
