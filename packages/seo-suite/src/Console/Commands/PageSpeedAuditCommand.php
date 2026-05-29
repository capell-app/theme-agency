<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Console\Commands;

use Capell\SeoSuite\Actions\RunPageSpeedAuditAction;
use Capell\SeoSuite\Enums\PageSpeedAuditTriggerEnum;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class PageSpeedAuditCommand extends Command
{
    protected $signature = 'capell:seo-suite:pagespeed-audit
        {--site= : Limit audits to one site ID}
        {--language= : Limit audits to one language ID}
        {--page= : Limit audits to one page ID}
        {--strategy=both : mobile, desktop, or both}
        {--notify : Send the weekly digest notification after the run}
        {--limit= : Maximum number of page URLs to audit}';

    protected $description = 'Run SEO Suite PageSpeed Insights audits for public pages.';

    public function handle(): int
    {
        try {
            $summary = RunPageSpeedAuditAction::run(
                trigger: $this->option('notify') ? PageSpeedAuditTriggerEnum::Scheduled : PageSpeedAuditTriggerEnum::Command,
                siteId: $this->integerOption('site'),
                languageId: $this->integerOption('language'),
                pageId: $this->integerOption('page'),
                strategies: $this->strategies(),
                limit: $this->integerOption('limit'),
                notify: (bool) $this->option('notify'),
            );
        } catch (InvalidArgumentException $invalidArgumentException) {
            $this->error($invalidArgumentException->getMessage());

            return self::FAILURE;
        }

        $this->info(__('capell-seo-suite::generic.pagespeed_command_summary', [
            'pages' => $summary->auditedPages,
            'successful' => $summary->successfulResults,
            'failed' => $summary->failedResults,
        ]));

        return self::SUCCESS;
    }

    private function integerOption(string $key): ?int
    {
        $value = $this->option($key);

        if ($value === null || $value === '') {
            return null;
        }

        if (! ctype_digit((string) $value) || (int) $value < 1) {
            throw new InvalidArgumentException((string) __('capell-seo-suite::generic.pagespeed_invalid_integer_option', ['option' => $key]));
        }

        return (int) $value;
    }

    /**
     * @return list<PageSpeedStrategyEnum>
     */
    private function strategies(): array
    {
        $strategy = (string) $this->option('strategy');

        return match ($strategy) {
            'both' => PageSpeedStrategyEnum::cases(),
            PageSpeedStrategyEnum::Mobile->value => [PageSpeedStrategyEnum::Mobile],
            PageSpeedStrategyEnum::Desktop->value => [PageSpeedStrategyEnum::Desktop],
            default => throw new InvalidArgumentException((string) __('capell-seo-suite::generic.pagespeed_invalid_strategy')),
        };
    }
}
