<?php

declare(strict_types=1);

namespace Capell\Comments\Health;

use Capell\Comments\Enums\LivewireComponentEnum;
use Capell\Comments\Livewire\CommentThreadComponent;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentModerationEvent;
use Capell\Comments\Models\CommentToken;
use Capell\Comments\Settings\CommentSettings;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Throwable;

final class CommentsHealthCheck implements ChecksExtensionHealth
{
    private const string THREAD_ROUTE_NAME = 'capell-comments.thread';

    /**
     * @var list<class-string<Model>>
     */
    private const array STORAGE_MODELS = [
        CommentAuthor::class,
        Comment::class,
        CommentToken::class,
        CommentModerationEvent::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->storageTablesCheck(),
            $check->settingsRegistrationCheck(),
            $check->threadRouteCheck(),
            $check->threadComponentCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: 'Comments storage tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'The comment author, comment, token, and moderation event tables are present.'
                : 'Missing tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the Comments storage tables.',
        );
    }

    public function settingsRegistrationCheck(): DoctorCheckResultData
    {
        $registered = $this->settingsAreRegistered();

        return new DoctorCheckResultData(
            label: 'Comments settings registration',
            passed: $registered,
            message: $registered
                ? 'The Comments settings class and schema are registered.'
                : 'The Comments settings class or schema is not registered.',
            remediation: $registered
                ? null
                : 'Ensure CommentsServiceProvider registers CommentSettings with the settings schema registry.',
        );
    }

    public function threadRouteCheck(): DoctorCheckResultData
    {
        $registered = $this->threadRouteIsRegistered();

        return new DoctorCheckResultData(
            label: 'Comments public thread route',
            passed: $registered,
            message: $registered
                ? 'The public comment thread route is registered.'
                : 'The public comment thread route is not registered.',
            remediation: $registered
                ? null
                : 'Ensure CommentsServiceProvider loads the package web routes.',
        );
    }

    public function threadComponentCheck(): DoctorCheckResultData
    {
        $registered = $this->threadComponentIsRegistered();

        return new DoctorCheckResultData(
            label: 'Comments public thread Livewire component',
            passed: $registered,
            message: $registered
                ? 'The public comment thread Livewire component is registered.'
                : 'The public comment thread Livewire component is not registered.',
            remediation: $registered
                ? null
                : 'Ensure FrontendServiceProvider registers the capell-comments::thread Livewire component.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect($this->requiredTableNames())
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all());
    }

    public function settingsAreRegistered(): bool
    {
        if (! app()->bound(SettingsSchemaRegistry::class)) {
            return false;
        }

        try {
            /** @var SettingsSchemaRegistry $registry */
            $registry = resolve(SettingsSchemaRegistry::class);

            return $registry->getSettingsClass(CommentSettings::group()) === CommentSettings::class
                && $registry->hasGroup(CommentSettings::group());
        } catch (Throwable) {
            return false;
        }
    }

    public function threadRouteIsRegistered(): bool
    {
        return Route::has(self::THREAD_ROUTE_NAME);
    }

    public function threadComponentIsRegistered(): bool
    {
        try {
            if (Livewire::exists(LivewireComponentEnum::Thread->value)) {
                return true;
            }

            return (bool) Livewire::exists(CommentThreadComponent::class);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return list<string>
     */
    private function requiredTableNames(): array
    {
        $tableNames = [];

        foreach (self::STORAGE_MODELS as $modelClass) {
            $tableNames[] = (new $modelClass)->getTable();
        }

        return array_values(array_unique($tableNames));
    }
}
