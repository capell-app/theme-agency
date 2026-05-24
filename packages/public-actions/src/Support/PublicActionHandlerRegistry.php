<?php

declare(strict_types=1);

namespace Capell\PublicActions\Support;

use Capell\PublicActions\Contracts\PublicActionHandler;
use InvalidArgumentException;

final class PublicActionHandlerRegistry
{
    /** @var array<string, PublicActionHandler|class-string> */
    private array $handlers = [];

    /**
     * @param  PublicActionHandler|class-string  $handler
     */
    public function register(string $key, PublicActionHandler|string $handler): void
    {
        $resolvedHandler = is_string($handler) ? resolve($handler) : $handler;

        throw_unless($resolvedHandler instanceof PublicActionHandler, InvalidArgumentException::class, 'Public action handlers must implement PublicActionHandler.');

        $this->handlers[$key] = $handler;
    }

    public function resolve(string $key): ?PublicActionHandler
    {
        $handler = $this->handlers[$key] ?? null;

        if ($handler === null) {
            return null;
        }

        return is_string($handler) ? resolve($handler) : $handler;
    }

    /**
     * @return array<string, PublicActionHandler>
     */
    public function all(): array
    {
        return collect($this->handlers)
            ->mapWithKeys(fn (PublicActionHandler|string $handler, string $key): array => [$key => is_string($handler) ? resolve($handler) : $handler])
            ->all();
    }
}
