<?php

declare(strict_types=1);

use Capell\Contacts\Actions\AnonymizeContactAction;
use Capell\Contacts\Actions\BuildContactPrivacyExportAction;
use Capell\Contacts\Actions\BuildContactsOverviewStatsAction;
use Capell\Contacts\Actions\SyncContactSourceRecordAction;
use Capell\Contacts\Actions\SyncFormSubmissionContactAction;
use Capell\Contacts\Actions\SyncShopifyCustomerContactAction;
use Capell\Contacts\Filament\Resources\Activities\ContactActivityResource;
use Capell\Contacts\Filament\Resources\Contacts\ContactResource;
use Capell\Contacts\Filament\Resources\Leads\LeadResource;
use Capell\Contacts\Filament\Resources\Organisations\OrganisationResource;
use Capell\Contacts\Filament\Widgets\ContactsOverviewStatsWidget;
use Capell\Contacts\Manifest\ContactsAdminResourcesContribution;
use Capell\Contacts\Manifest\ContactsModelsContribution;
use Capell\Contacts\Manifest\ContactsOverviewWidgetContribution;
use Capell\Contacts\Providers\AdminServiceProvider;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;

require_once __DIR__ . '/../autoload.php';

it('declares the contacts package manifest contract', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $contributions = collect($manifest['contributes']);

    expect($manifest)
        ->toHaveKey('manifest-version', 3)
        ->toHaveKey('name', 'capell-app/contacts')
        ->toHaveKey('namespace', 'Capell\\Contacts')
        ->and($manifest['dependencies']['requires'])->toContain('capell-app/admin', 'capell-app/core')
        ->and($manifest['providers']['admin'])->toContain(AdminServiceProvider::class)
        ->and($manifest['database']['migrations'])->toBeTrue()
        ->and($manifest['database']['requiredTables'])->toBe([
            'contacts',
            'contact_organisations',
            'contact_organisation_memberships',
            'contact_leads',
            'contact_activities',
        ])
        ->and($manifest['permissions'])->toContain(
            'View:Contact',
            'View:Organisation',
            'View:Lead',
            'View:ContactActivity',
        )
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === ContactsAdminResourcesContribution::class
            && ($contribution['resourceClasses'] ?? []) === [
                ContactResource::class,
                OrganisationResource::class,
                LeadResource::class,
                ContactActivityResource::class,
            ]))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'model'
            && ($contribution['class'] ?? null) === ContactsModelsContribution::class))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'dashboard-widget'
            && ($contribution['class'] ?? null) === ContactsOverviewWidgetContribution::class
            && ($contribution['widgetClass'] ?? null) === ContactsOverviewStatsWidget::class))->toBeTrue()
        ->and(class_implements(ContactsOverviewWidgetContribution::class))->toContain(RegistersExtensionWidget::class)
        ->and($manifest['actions']['anonymizeContact'])->toBe(AnonymizeContactAction::class)
        ->and($manifest['actions']['buildContactPrivacyExport'])->toBe(BuildContactPrivacyExportAction::class)
        ->and($manifest['actions']['buildContactsOverviewStats'])->toBe(BuildContactsOverviewStatsAction::class)
        ->and($manifest['actions']['syncContactSourceRecord'])->toBe(SyncContactSourceRecordAction::class)
        ->and($manifest['actions']['syncFormSubmissionContact'])->toBe(SyncFormSubmissionContactAction::class)
        ->and($manifest['actions']['syncShopifyCustomerContact'])->toBe(SyncShopifyCustomerContactAction::class)
        ->and($manifest['capabilities'])->toContain(
            'contacts-access-gate-source-adapter',
            'contacts-campaign-studio-source-adapter',
            'contacts-comments-source-adapter',
            'contacts-events-source-adapter',
            'contacts-source-identity',
            'contacts-source-sync',
            'contacts-form-builder-source-adapter',
            'contacts-newsletter-source-adapter',
            'contacts-shopify-commerce-source-adapter',
            'contacts-dashboard-widget',
            'contacts-deduplication-rules',
            'contacts-privacy-export',
            'contacts-privacy-anonymization',
        )
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([])
        ->and($manifest['performance']['cacheSafety']['sensitiveOutput'])->toBeTrue();
});
