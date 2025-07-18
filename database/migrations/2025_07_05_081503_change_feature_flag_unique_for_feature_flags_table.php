<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('feature_flags', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->timestamp('completed_at')->nullable()->default(null);
        });

        DB::statement(<<<EOQ
            CREATE UNIQUE INDEX feature_flags_name_team_id_unique
               ON "feature_flags"(name, team_id)
               WHERE (completed_at IS NULL);
        EOQ);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_flags', function (Blueprint $table) {
            $table->dropIndex('feature_flags_name_team_id_unique');
            $table->dropColumn('completed_at');
            $table->unique(['name']);
        });
    }
};
