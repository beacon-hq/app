<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropUnique('policies_name_unique');

            $table->unique(['name', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropUnique(['name', 'team_id']);

            $table->unique('name');
        });
    }
};
