<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tagsTableName = $this->tableName('contact_tags', 'contact_tags');
        $membershipsTableName = $this->tableName('contact_tag_memberships', 'contact_tag_memberships');
        $contactsTableName = $this->tableName('contacts', 'contacts');

        if (! Schema::hasTable($tagsTableName)) {
            Schema::create($tagsTableName, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->timestamps();

                $table->unique(['site_id', 'slug'], 'contact_tags_site_slug_unique');
                $table->index(['site_id', 'name'], 'contact_tags_site_name_index');
            });
        }

        if (Schema::hasTable($membershipsTableName)) {
            return;
        }

        Schema::create($membershipsTableName, function (Blueprint $table) use ($contactsTableName, $tagsTableName): void {
            $table->id();
            $table->foreignId('contact_id')->constrained($contactsTableName)->cascadeOnDelete();
            $table->foreignId('contact_tag_id')->constrained($tagsTableName)->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contact_id', 'contact_tag_id'], 'contact_tag_member_unique');
            $table->index(['contact_tag_id', 'contact_id'], 'contact_tag_member_tag_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName('contact_tag_memberships', 'contact_tag_memberships'));
        Schema::dropIfExists($this->tableName('contact_tags', 'contact_tags'));
    }

    private function tableName(string $key, string $default): string
    {
        $tableName = config('capell-contacts.tables.' . $key, $default);

        return is_string($tableName) && $tableName !== '' ? $tableName : $default;
    }
};
