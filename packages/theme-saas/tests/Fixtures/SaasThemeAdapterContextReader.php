<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Saas\Tests\Fixtures;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Frontend\Contracts\FrontendContextReader;

final class SaasThemeAdapterContextReader implements FrontendContextReader
{
    public function __construct(
        private readonly ?Pageable $page,
        private readonly ?Site $site,
    ) {}

    public function site(): ?Site
    {
        return $this->site;
    }

    public function language(): ?Language
    {
        return null;
    }

    public function page(): ?Pageable
    {
        return $this->page;
    }

    public function layout(): ?Layout
    {
        return null;
    }

    public function theme(): ?Theme
    {
        return null;
    }

    public function params(): array
    {
        return [];
    }

    public function slug(): ?string
    {
        return null;
    }

    public function isError(): bool
    {
        return false;
    }

    public function setFrontendData(string $key, mixed $value): FrontendContextReader
    {
        unset($key, $value);

        return $this;
    }

    public function getFrontendData(?string $key = null): mixed
    {
        unset($key);

        return [];
    }
}
