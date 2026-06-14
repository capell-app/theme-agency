<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Console\Commands;

use Capell\SocialFeeds\Actions\SyncSocialFeedConnectionsAction;
use Illuminate\Console\Command;
use Override;

final class SyncSocialFeedsCommand extends Command
{
    protected $signature = 'capell:social-feeds:sync
        {--all : Sync all connected social feed connections}
        {--connection-id= : Sync a single social feed connection id}
        {--limit= : Maximum posts to fetch per connection}';

    protected $description = 'Sync connected Social Feeds into the cached item table.';

    #[Override]
    public function getDescription(): string
    {
        return (string) __('capell-social-feeds::package.commands.sync.description');
    }

    public function handle(): int
    {
        $syncedItems = SyncSocialFeedConnectionsAction::run(
            connectionId: $this->integerOption('connection-id'),
            limit: $this->integerOption('limit'),
        );

        $this->components->info((string) trans_choice(
            'capell-social-feeds::package.commands.sync.completed',
            $syncedItems,
            ['count' => $syncedItems],
        ));

        return self::SUCCESS;
    }

    private function integerOption(string $name): ?int
    {
        $value = $this->option($name);

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
