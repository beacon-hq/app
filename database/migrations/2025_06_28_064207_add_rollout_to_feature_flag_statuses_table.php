<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('feature_flag_statuses', function (Blueprint $table) {
            $table->smallInteger('rollout_percentage')->nullable();
            $table->string('rollout_strategy')->nullable();
            $table->jsonb('rollout_context')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('feature_flag_statuses', function (Blueprint $table) {
            $table->dropColumn('rollout_percentage');
            $table->dropColumn('rollout_strategy');
            $table->dropColumn('rollout_context');
        });
    }
};
