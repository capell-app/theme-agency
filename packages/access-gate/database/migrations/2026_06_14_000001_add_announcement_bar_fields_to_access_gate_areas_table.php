<?php

declare(strict_types=1);

use Capell\AccessGate\Support\AccessGateSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $builder = AccessGateSchema::builder();

        if ($builder->hasColumn('access_gate_areas', 'announcement_enabled')) {
            return;
        }

        $builder->table('access_gate_areas', function (Blueprint $table): void {
            $table->boolean('announcement_enabled')->default(false)->after('discount_metadata');
            $table->text('announcement_message')->nullable()->after('announcement_enabled');
            $table->string('announcement_short_message')->nullable()->after('announcement_message');
            $table->string('announcement_link_label')->nullable()->after('announcement_short_message');
            $table->string('announcement_link_short_label')->nullable()->after('announcement_link_label');
            $table->string('announcement_link_url')->nullable()->after('announcement_link_short_label');
            $table->json('announcement_path_patterns')->nullable()->after('announcement_link_url');
        });
    }

    public function down(): void
    {
        $builder = AccessGateSchema::builder();

        if (! $builder->hasColumn('access_gate_areas', 'announcement_enabled')) {
            return;
        }

        $builder->table('access_gate_areas', function (Blueprint $table): void {
            $table->dropColumn([
                'announcement_enabled',
                'announcement_message',
                'announcement_short_message',
                'announcement_link_label',
                'announcement_link_short_label',
                'announcement_link_url',
                'announcement_path_patterns',
            ]);
        });
    }
};
