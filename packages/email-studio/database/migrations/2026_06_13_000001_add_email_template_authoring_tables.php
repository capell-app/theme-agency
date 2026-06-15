<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $themeTable = $this->tableName('capell-email-studio.tables.template_themes', 'email_template_themes');
        $variantTable = $this->tableName('capell-email-studio.tables.template_variants', 'email_template_variants');
        $messageTable = $this->tableName('capell-email-studio.tables.messages', 'email_messages');
        $registrationTable = $this->tableName('capell-email-studio.tables.template_registrations', 'email_template_registrations');

        if (! Schema::hasTable($themeTable)) {
            Schema::create($themeTable, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
                $table->string('site_scope_key')->default('global')->index();
                $table->string('key')->index();
                $table->string('name');
                $table->boolean('is_default')->default(false)->index();
                $table->string('logo_path')->nullable();
                $table->string('logo_url')->nullable();
                $table->string('screenshot_path')->nullable();
                $table->json('colors')->nullable();
                $table->timestamps();
                $table->unique(['site_scope_key', 'key']);
            });
        }

        if (Schema::hasTable($variantTable)) {
            Schema::table($variantTable, function (Blueprint $table) use ($themeTable, $variantTable): void {
                if (! Schema::hasColumn($variantTable, 'email_template_theme_id')) {
                    $table->foreignId('email_template_theme_id')
                        ->nullable()
                        ->after('email_profile_id')
                        ->constrained($themeTable)
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn($variantTable, 'cc')) {
                    $table->json('cc')->nullable()->after('preview_text');
                }

                if (! Schema::hasColumn($variantTable, 'bcc')) {
                    $table->json('bcc')->nullable()->after('cc');
                }
            });
        }

        if (Schema::hasTable($messageTable) && ! Schema::hasColumn($messageTable, 'attachments')) {
            Schema::table($messageTable, function (Blueprint $table): void {
                $table->json('attachments')->nullable()->after('headers');
            });
        }

        if (Schema::hasTable($registrationTable)) {
            Schema::table($registrationTable, function (Blueprint $table) use ($registrationTable): void {
                if (! Schema::hasColumn($registrationTable, 'default_locale')) {
                    $table->string('default_locale', 12)->default('en')->after('variables');
                }

                if (! Schema::hasColumn($registrationTable, 'is_static_renderable')) {
                    $table->boolean('is_static_renderable')->default(false)->after('default_locale');
                }
            });
        }
    }

    public function down(): void
    {
        $themeTable = $this->tableName('capell-email-studio.tables.template_themes', 'email_template_themes');
        $variantTable = $this->tableName('capell-email-studio.tables.template_variants', 'email_template_variants');
        $messageTable = $this->tableName('capell-email-studio.tables.messages', 'email_messages');
        $registrationTable = $this->tableName('capell-email-studio.tables.template_registrations', 'email_template_registrations');

        if (Schema::hasTable($messageTable) && Schema::hasColumn($messageTable, 'attachments')) {
            Schema::table($messageTable, function (Blueprint $table): void {
                $table->dropColumn('attachments');
            });
        }

        if (Schema::hasTable($variantTable)) {
            Schema::table($variantTable, function (Blueprint $table) use ($variantTable): void {
                if (Schema::hasColumn($variantTable, 'email_template_theme_id')) {
                    $table->dropConstrainedForeignId('email_template_theme_id');
                }

                if (Schema::hasColumn($variantTable, 'cc')) {
                    $table->dropColumn('cc');
                }

                if (Schema::hasColumn($variantTable, 'bcc')) {
                    $table->dropColumn('bcc');
                }
            });
        }

        if (Schema::hasTable($registrationTable)) {
            Schema::table($registrationTable, function (Blueprint $table) use ($registrationTable): void {
                if (Schema::hasColumn($registrationTable, 'is_static_renderable')) {
                    $table->dropColumn('is_static_renderable');
                }

                if (Schema::hasColumn($registrationTable, 'default_locale')) {
                    $table->dropColumn('default_locale');
                }
            });
        }

        Schema::dropIfExists($themeTable);
    }

    private function tableName(string $configKey, string $fallback): string
    {
        $tableName = config($configKey, $fallback);

        return is_string($tableName) && $tableName !== '' ? $tableName : $fallback;
    }
};
