<?php

declare(strict_types=1);

namespace Capell\Comments\Console\Commands;

use Illuminate\Console\Command;

final class InstallCommentsCommand extends Command
{
    protected $signature = 'capell-comments:install';

    protected $description = 'Publish Comments migrations, settings migrations, and config.';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'capell-comments-config',
            '--force' => true,
        ]);

        $this->call('capell:publish-migrations', [
            '--items' => [
                '2026_05_24_000001_create_comment_authors_table',
                '2026_05_24_000002_create_comments_table',
                '2026_05_24_000003_create_comment_tokens_table',
                '2026_05_24_000004_create_comment_moderation_events_table',
            ],
            '--path' => dirname(__DIR__, 3) . '/database/migrations',
        ]);

        $this->call('capell:publish-migrations', [
            '--type' => 'settings',
            '--items' => [
                '2026_05_24_000005_create_comments_settings',
            ],
            '--path' => dirname(__DIR__, 3) . '/database/settings',
        ]);

        $this->info('Capell Comments install files published.');

        return self::SUCCESS;
    }
}
