<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets\Concerns;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Page;

trait ResolvesEditPageRecord
{
    public ?Pageable $record = null;

    public ?int $recordKey = null;

    private ?Pageable $resolvedRecord = null;

    public function mountResolvesEditPageRecord(): void
    {
        $this->recordKey ??= $this->initialRecordKey();
    }

    public function hydrateResolvesEditPageRecord(): void
    {
        $this->recordKey ??= $this->initialRecordKey();
    }

    private function pageRecord(): ?Pageable
    {
        if ($this->resolvedRecord instanceof Pageable) {
            return $this->resolvedRecord;
        }

        if ($this->record instanceof Pageable) {
            $this->recordKey ??= (int) $this->record->getKey();
            $this->resolvedRecord = $this->record;

            return $this->resolvedRecord;
        }

        if ($this->recordKey === null) {
            return null;
        }

        $this->resolvedRecord = Page::query()->find($this->recordKey);

        return $this->resolvedRecord;
    }

    private function initialRecordKey(): ?int
    {
        if ($this->record instanceof Pageable) {
            return (int) $this->record->getKey();
        }

        $routeRecord = request()->route('record');

        if ($routeRecord instanceof Pageable) {
            return (int) $routeRecord->getKey();
        }

        if (is_numeric($routeRecord)) {
            return (int) $routeRecord;
        }

        return null;
    }
}
