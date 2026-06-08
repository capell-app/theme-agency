<?php

declare(strict_types=1);

namespace Capell\MediaAI\Console\Commands;

use Capell\MediaAI\Actions\QueueBatchImageDoctorRequestsAction;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class QueueImageDoctorBatchCommand extends Command
{
    protected $signature = 'media-ai:doctor-batch
        {--operation=improve : Image doctor operation}
        {--instructions=Generate accessible alt text and a concise caption. : Provider instructions}
        {--locale= : Locale/language code for generated metadata}
        {--limit= : Maximum media rows to queue}
        {--all-images : Include images that already have localized alt text}
        {--budget-cents= : Optional provider budget hint}
        {--model= : Optional provider model hint}';

    protected $description = 'Queue Media AI image doctor jobs for a batch of image media records.';

    public function handle(): int
    {
        $operation = $this->stringOption('operation') ?? 'improve';

        if (! in_array($operation, ImageDoctorRequest::OPERATIONS, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported Media AI operation [%s].', $operation));
        }

        $queued = QueueBatchImageDoctorRequestsAction::run(
            operation: $operation,
            instructions: $this->stringOption('instructions') ?? '',
            locale: $this->stringOption('locale'),
            limit: $this->integerOption('limit'),
            missingAltOnly: $this->option('all-images') !== true,
            budgetCents: $this->integerOption('budget-cents'),
            model: $this->stringOption('model'),
        );

        $this->components->info($this->translation('capell-media-ai::media-ai.batch_queued', ['count' => $queued]));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $replace
     */
    private function translation(string $key, array $replace = []): string
    {
        $value = __($key, $replace);

        return is_string($value) ? $value : $key;
    }

    private function stringOption(string $name): ?string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function integerOption(string $name): ?int
    {
        $value = $this->option($name);

        return is_numeric($value) ? max(0, (int) $value) : null;
    }
}
