<?php

declare(strict_types=1);

use Capell\Contacts\Enums\ContactStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-contacts.tables.contacts', 'contacts');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->nullableMorphs('source');
            $table->longText('email')->nullable();
            $table->string('email_hash', 64)->nullable();
            $table->longText('phone')->nullable();
            $table->string('phone_hash', 64)->nullable();
            $table->longText('first_name')->nullable();
            $table->longText('last_name')->nullable();
            $table->longText('display_name')->nullable();
            $table->longText('profile')->nullable();
            $table->string('status')->default(ContactStatus::Active->value)->index();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['site_id', 'email_hash']);
            $table->index(['site_id', 'phone_hash']);
            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-contacts.tables.contacts', 'contacts'));
    }
};
