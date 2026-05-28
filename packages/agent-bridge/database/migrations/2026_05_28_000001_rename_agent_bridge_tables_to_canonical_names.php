<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private const array TABLES = [
        'capell_agent-bridge_tokens' => 'capell_agent_bridge_tokens',
        'capell_agent-bridge_confirmations' => 'capell_agent_bridge_confirmations',
        'capell_agent-bridge_audit_entries' => 'capell_agent_bridge_audit_entries',
        'capell_agent-bridge_saved_prompts' => 'capell_agent_bridge_saved_prompts',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $legacyTable => $canonicalTable) {
            if (Schema::hasTable($legacyTable) && ! Schema::hasTable($canonicalTable)) {
                Schema::rename($legacyTable, $canonicalTable);
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLES) as $legacyTable => $canonicalTable) {
            if (Schema::hasTable($canonicalTable) && ! Schema::hasTable($legacyTable)) {
                Schema::rename($canonicalTable, $legacyTable);
            }
        }
    }
};
