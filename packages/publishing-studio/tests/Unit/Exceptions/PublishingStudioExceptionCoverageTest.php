<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Tests\Unit\Exceptions;

use Capell\PublishingStudio\Checks\PublishCheckResult;
use Capell\PublishingStudio\Checks\PublishCheckSeverity;
use Capell\PublishingStudio\Exceptions\EntityNotInVersionException;
use Capell\PublishingStudio\Exceptions\PublishBlockedByChecksException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @extends TestCase<MockObject> */
final class PublishingStudioExceptionCoverageTest extends TestCase
{
    public function test_entity_not_in_version_exception_identifies_the_missing_manifest_member(): void
    {
        $exception = EntityNotInVersionException::missing(
            modelClass: 'App\\Models\\Page',
            entityUuid: 'page-uuid',
            versionId: 18,
        );

        $this->assertSame('Entity App\\Models\\Page(uuid=page-uuid) is not part of version #18 manifest.', $exception->getMessage());
    }

    public function test_publish_blocked_exception_lists_only_dirty_error_checks(): void
    {
        $exception = new PublishBlockedByChecksException([
            new PublishCheckResult(
                identifier: 'seo',
                label: 'SEO',
                severity: PublishCheckSeverity::Error,
                messages: ['Missing title.'],
            ),
            new PublishCheckResult(
                identifier: 'accessibility',
                label: 'Accessibility',
                severity: PublishCheckSeverity::Error,
            ),
            new PublishCheckResult(
                identifier: 'links',
                label: 'Links',
                severity: PublishCheckSeverity::Warn,
                messages: ['Broken link.'],
            ),
        ]);

        $this->assertSame('Publish blocked by failing checks: seo', $exception->getMessage());
    }
}
