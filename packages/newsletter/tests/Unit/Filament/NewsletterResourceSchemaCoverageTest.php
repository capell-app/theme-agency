<?php

declare(strict_types=1);

use Capell\Newsletter\Enums\AuthType;
use Capell\Newsletter\Enums\ProviderType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Enums\SyncStatus;
use Capell\Newsletter\Filament\Resources\FormMappings\FormMappingResource;
use Capell\Newsletter\Filament\Resources\NewsletterTags\NewsletterTagResource;
use Capell\Newsletter\Filament\Resources\ProviderAudiences\ProviderAudienceResource;
use Capell\Newsletter\Filament\Resources\ProviderConnections\ProviderConnectionResource;
use Capell\Newsletter\Filament\Resources\ProviderInterestMappings\ProviderInterestMappingResource;
use Capell\Newsletter\Filament\Resources\SyncAttempts\SyncAttemptResource;
use Capell\Newsletter\Filament\Settings\NewsletterSettingsSchema;
use Capell\Newsletter\Filament\Widgets\NewsletterOverviewStatsWidget;
use Capell\Newsletter\Models\FormMapping;
use Capell\Newsletter\Models\ProviderAudience;
use Capell\Newsletter\Models\ProviderConnection;
use Capell\Newsletter\Models\ProviderInterestMapping;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Models\SyncAttempt;
use Capell\Tags\Models\Tag;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget\Stat;

it('declares newsletter mapping resource forms, pages, and settings schema', function (): void {
    Tag::factory()->create([
        'name' => ['en' => 'Product updates'],
        'type' => 'newsletter',
    ]);

    $mappingComponents = FormMappingResource::form(Schema::make())->getComponents();
    $settingsComponents = NewsletterSettingsSchema::make(Schema::make());
    $settingsGridComponents = newsletterCoverageGridComponents($settingsComponents[0]);

    expect($mappingComponents)->toHaveCount(15)
        ->and($mappingComponents[2])->toBeInstanceOf(TextInput::class)
        ->and($mappingComponents[10])->toBeInstanceOf(Select::class)
        ->and($mappingComponents[11])->toBeInstanceOf(KeyValue::class)
        ->and($mappingComponents[12])->toBeInstanceOf(Toggle::class)
        ->and(FormMappingResource::getModel())->toBe(FormMapping::class)
        ->and(FormMappingResource::getPages())->toHaveKeys(['index', 'create', 'edit'])
        ->and($settingsComponents)->toHaveCount(1)
        ->and($settingsComponents[0])->toBeInstanceOf(Grid::class)
        ->and($settingsGridComponents)->toHaveCount(2)
        ->and($settingsGridComponents[0])->toBeInstanceOf(Select::class)
        ->and($settingsGridComponents[1])->toBeInstanceOf(KeyValue::class);
});

it('declares newsletter provider resource forms, models, and pages', function (): void {
    $audienceComponents = ProviderAudienceResource::form(Schema::make())->getComponents();
    $interestComponents = ProviderInterestMappingResource::form(Schema::make())->getComponents();

    expect($audienceComponents)->toHaveCount(5)
        ->and($audienceComponents[0])->toBeInstanceOf(Select::class)
        ->and($audienceComponents[1])->toBeInstanceOf(TextInput::class)
        ->and($audienceComponents[3])->toBeInstanceOf(Toggle::class)
        ->and($interestComponents)->toHaveCount(5)
        ->and($interestComponents[0])->toBeInstanceOf(Select::class)
        ->and($interestComponents[2])->toBeInstanceOf(TextInput::class)
        ->and(ProviderAudienceResource::getModel())->toBe(ProviderAudience::class)
        ->and(ProviderInterestMappingResource::getModel())->toBe(ProviderInterestMapping::class)
        ->and(SyncAttemptResource::getModel())->toBe(SyncAttempt::class)
        ->and(NewsletterTagResource::getModel())->toBe(Tag::class)
        ->and(ProviderAudienceResource::getPages())->toHaveKeys(['index', 'create', 'edit'])
        ->and(ProviderInterestMappingResource::getPages())->toHaveKeys(['index', 'create', 'edit'])
        ->and(SyncAttemptResource::getPages())->toHaveKey('index');
});

it('only offers the fake provider in allowed environments', function (): void {
    $providerOptions = newsletterProviderConnectionOptions();

    expect($providerOptions)->toHaveKey(ProviderType::Fake->value);

    app()->detectEnvironment(static fn (): string => 'production');

    try {
        expect(newsletterProviderConnectionOptions())->not->toHaveKey(ProviderType::Fake->value);

        config()->set('capell-newsletter.providers.allow_fake_provider', true);

        expect(newsletterProviderConnectionOptions())->toHaveKey(ProviderType::Fake->value);
    } finally {
        app()->detectEnvironment(static fn (): string => 'testing');
    }
});

it('declares newsletter tag and sync attempt table columns and navigation metadata', function (): void {
    $formMappingTable = FormMappingResource::table(newsletterCoverageTable());
    $providerAudienceTable = ProviderAudienceResource::table(newsletterCoverageTable());
    $providerInterestTable = ProviderInterestMappingResource::table(newsletterCoverageTable());
    $tagTable = NewsletterTagResource::table(newsletterCoverageTable());
    $syncAttemptTable = SyncAttemptResource::table(newsletterCoverageTable());

    expect(array_keys($formMappingTable->getColumns()))->toBe(['name', 'form_handle', 'email_field', 'updated_at'])
        ->and(array_keys($providerAudienceTable->getColumns()))->toBe(['name', 'providerConnection.name', 'remote_id', 'updated_at'])
        ->and(array_keys($providerInterestTable->getColumns()))->toBe([
            'providerAudience.name',
            'tag.name',
            'remote_interest_id',
            'remote_interest_type',
        ])
        ->and(array_keys($tagTable->getColumns()))->toBe(['name', 'slug'])
        ->and(array_keys($syncAttemptTable->getColumns()))->toBe([
            'operation',
            'sync_status',
            'attempts',
            'error_message',
            'last_attempted_at',
        ])
        ->and(NewsletterTagResource::getNavigationGroup())->toBeNull()
        ->and(NewsletterTagResource::getNavigationParentItem())->toBe((string) __('capell-admin::navigation.marketing_studio'))
        ->and(NewsletterTagResource::getNavigationLabel())->toBe('Newsletter Tags')
        ->and(NewsletterTagResource::shouldRegisterNavigation())->toBeTrue()
        ->and(FormMappingResource::getNavigationLabel())->toBe('Form Mappings')
        ->and(FormMappingResource::shouldRegisterNavigation())->toBeFalse()
        ->and(ProviderAudienceResource::getNavigationLabel())->toBe('Provider Audiences')
        ->and(ProviderAudienceResource::shouldRegisterNavigation())->toBeFalse()
        ->and(ProviderInterestMappingResource::getNavigationLabel())->toBe('Provider Interest Mappings')
        ->and(ProviderInterestMappingResource::shouldRegisterNavigation())->toBeFalse()
        ->and(SyncAttemptResource::getNavigationGroup())->toBe('capell-admin::navigation.group_marketing')
        ->and(SyncAttemptResource::getNavigationLabel())->toBe('Sync Attempts')
        ->and(SyncAttemptResource::shouldRegisterNavigation())->toBeFalse();
});

it('summarizes newsletter overview stats for current records', function (): void {
    $site = test()->createNewsletterSite();
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'status' => SubscriberStatus::Subscribed,
    ]);

    Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'status' => SubscriberStatus::Pending,
    ]);

    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Mailchimp',
        'provider' => ProviderType::Mailchimp,
        'auth_type' => AuthType::ApiKey,
        'credentials' => [],
        'is_enabled' => true,
    ]);

    SyncAttempt::query()->create([
        'subscriber_id' => $subscriber->getKey(),
        'provider_connection_id' => $connection->getKey(),
        'operation' => 'sync_subscriber',
        'sync_status' => SyncStatus::Failed,
        'payload_hash' => 'failed-hash',
        'attempts' => 1,
    ]);

    $widget = new class extends NewsletterOverviewStatsWidget
    {
        /**
         * @return array<int, Stat>
         */
        public function statsForTest(): array
        {
            return $this->getStats();
        }
    };

    expect($widget->statsForTest())->toHaveCount(3);
});

/**
 * @return array<int, object>
 */
function newsletterCoverageGridComponents(Grid $grid): array
{
    $reflectionProperty = new ReflectionProperty($grid, 'childComponents');
    $childComponents = $reflectionProperty->getValue($grid);

    return $childComponents['default'] ?? [];
}

function newsletterCoverageTable(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null);

    return Table::make($livewire);
}

/**
 * @return array<array-key, mixed>
 */
function newsletterProviderConnectionOptions(): array
{
    $reflectionMethod = new ReflectionMethod(ProviderConnectionResource::class, 'providerOptions');
    $options = $reflectionMethod->invoke(null);

    return is_array($options) ? $options : [];
}
