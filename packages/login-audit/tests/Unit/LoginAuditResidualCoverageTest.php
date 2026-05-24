<?php

declare(strict_types=1);

use Capell\LoginAudit\Filament\Resources\LoginAudits\Tables\LoginAuditsTable;
use Capell\LoginAudit\Filament\Resources\Users\RelationManagers\LoginAuditsRelationManager;
use Capell\LoginAudit\Models\LoginAudit;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

it('builds login audit table columns filters and helper fallbacks', function (): void {
    $columns = invokeLoginAuditTableMethod('getTableColumns');
    $filters = invokeLoginAuditTableMethod('getTableFilters');

    $missingRecord = new LoginAudit;
    $namedRecord = new LoginAudit;
    $namedRecord->setRelation('authenticatable', new class extends Model
    {
        use HasFactory;

        protected $attributes = [
            'name' => 'Ben Johnson',
        ];
    });

    expect($columns)->toHaveCount(7)
        ->and($columns[1])->toBeInstanceOf(TextColumn::class)
        ->and($filters)->toHaveCount(3)
        ->and(invokeLoginAuditTableMethod('getAuthenticatableName', [$missingRecord]))->toBe(__('capell-admin::generic.missing'))
        ->and(invokeLoginAuditTableMethod('getAuthenticatableName', [$namedRecord]))->toBe('Ben Johnson')
        ->and(invokeLoginAuditTableMethod('getAuthenticatableUrl', [$missingRecord]))->toBeNull();

    $query = Mockery::mock(Builder::class)->shouldIgnoreMissing();

    $configured = LoginAuditsTable::configure(loginAuditTableForCoverage($query));

    expect($configured->getColumns())->toHaveCount(7)
        ->and($configured->getFilters())->toHaveCount(3);
});

it('builds login audit user relation manager table metadata', function (): void {
    $owner = new class extends Model
    {
        use HasFactory;
    };
    $manager = new LoginAuditsRelationManager;

    expect(LoginAuditsRelationManager::getTitle($owner, 'edit'))->toBe(__('capell-login-audit::settings.login_audits'))
        ->and($manager->table(loginAuditTableForCoverage(Mockery::mock(Builder::class)->shouldIgnoreMissing()))->getColumns())->toHaveCount(6)
        ->and(invokeLoginAuditRelationManagerMethod($manager, 'loginSuccessful', [true]))->toBeTrue()
        ->and(invokeLoginAuditRelationManagerMethod($manager, 'loginSuccessful', ['1']))->toBeTrue()
        ->and(invokeLoginAuditRelationManagerMethod($manager, 'loginSuccessful', [false]))->toBeFalse();
});

/**
 * @param  list<mixed>  $parameters
 */
function invokeLoginAuditTableMethod(string $methodName, array $parameters = []): mixed
{
    $reflectionMethod = new ReflectionMethod(LoginAuditsTable::class, $methodName);

    return $reflectionMethod->invokeArgs(null, $parameters);
}

function loginAuditTableForCoverage(mixed $query): Table
{
    throw_unless($query instanceof Builder);

    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null);

    return Table::make($livewire)->query($query);
}

/**
 * @param  list<mixed>  $parameters
 */
function invokeLoginAuditRelationManagerMethod(LoginAuditsRelationManager $manager, string $methodName, array $parameters = []): mixed
{
    $reflectionMethod = new ReflectionMethod($manager, $methodName);

    return $reflectionMethod->invokeArgs($manager, $parameters);
}
