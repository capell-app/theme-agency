<?php

declare(strict_types=1);

use Capell\LoginAudit\Filament\Resources\LoginAudits\Tables\LoginAuditsTable;
use Capell\LoginAudit\Filament\Resources\Users\RelationManagers\LoginAuditsRelationManager;
use Capell\LoginAudit\Models\LoginAudit;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

it('builds login audit table columns filters and helper fallbacks', function (): void {
    $columns = invokeLoginAuditTableMethod('getTableColumns');
    $filters = invokeLoginAuditTableMethod('getTableFilters');
    $columnNames = loginAuditComponentNames($columns);
    $filterNames = loginAuditComponentNames($filters);

    $missingRecord = new LoginAudit;
    $namedRecord = new LoginAudit;
    $namedRecord->setRelation('authenticatable', new class extends Model
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        protected $attributes = [
            'name' => 'Ben Johnson',
        ];
    });

    expect($columnNames)->toBe([
        'id',
        'authenticatable',
        'ip_address',
        'user_agent',
        'device_name',
        'is_trusted',
        'login_at',
        'last_activity_at',
        'logout_at',
        'location',
    ])
        ->and($columns[1])->toBeInstanceOf(TextColumn::class)
        ->and($filterNames)->toBe([
            'login_successful',
            'login_at',
            'cleared_by_user',
            'is_trusted',
        ])
        ->and(invokeLoginAuditTableMethod('getAuthenticatableName', [$missingRecord]))->toBe(__('capell-admin::generic.missing'))
        ->and(invokeLoginAuditTableMethod('getAuthenticatableName', [$namedRecord]))->toBe('Ben Johnson')
        ->and(invokeLoginAuditTableMethod('getAuthenticatableUrl', [$missingRecord]))->toBeNull();

    $query = Mockery::mock(Builder::class)->shouldIgnoreMissing();

    $configured = LoginAuditsTable::configure(loginAuditTableForCoverage($query));

    expect($configured->getColumns())->toHaveCount(10)
        ->and($configured->getFilters())->toHaveCount(4)
        ->and($configured->getHeaderActions())->toHaveCount(1);
});

it('builds login audit user relation manager table metadata', function (): void {
    $owner = new class extends Model
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;
    };
    $manager = new LoginAuditsRelationManager;
    $table = $manager->table(loginAuditTableForCoverage(Mockery::mock(Builder::class)->shouldIgnoreMissing()));
    $columnNames = loginAuditComponentNames($table->getColumns());
    $filterNames = loginAuditComponentNames($table->getFilters());

    expect(LoginAuditsRelationManager::getTitle($owner, 'edit'))->toBe(__('capell-login-audit::settings.login_audits'))
        ->and($columnNames)->toBe([
            'login_successful',
            'ip_address',
            'user_agent',
            'device_name',
            'is_trusted',
            'login_at',
            'last_activity_at',
            'cleared_by_user',
        ])
        ->and($filterNames)->toBe(['is_trusted'])
        ->and($table->getHeaderActions())->toHaveCount(1)
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
 * @return list<string>
 */
function loginAuditComponentNames(mixed $components): array
{
    if (! is_array($components)) {
        return [];
    }

    $names = [];

    foreach ($components as $component) {
        if (! is_object($component) || ! method_exists($component, 'getName')) {
            continue;
        }

        $name = $component->getName();

        if (is_string($name)) {
            $names[] = $name;
        }
    }

    return $names;
}

/**
 * @param  list<mixed>  $parameters
 */
function invokeLoginAuditRelationManagerMethod(LoginAuditsRelationManager $manager, string $methodName, array $parameters = []): mixed
{
    $reflectionMethod = new ReflectionMethod($manager, $methodName);

    return $reflectionMethod->invokeArgs($manager, $parameters);
}
