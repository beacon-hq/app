<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique('applications_name_unique');
            $table->dropUnique('applications_display_name_unique');

            $table->unique(['team_id', 'name']);
            $table->unique(['display_name', 'team_id']);
        });

        Schema::table('environments', function (Blueprint $table) {
            $table->dropUnique('environments_name_unique');

            $table->unique(['name', 'team_id']);
        });

        Schema::table('feature_types', function (Blueprint $table) {
            $table->dropUnique('feature_types_name_unique');

            $table->unique(['name', 'team_id']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique('tags_name_unique');

            $table->unique(['name', 'team_id']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->unique(['name', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'name']);
            $table->dropUnique(['display_name', 'team_id']);

            $table->unique('name');
            $table->unique('display_name');
        });

        Schema::table('environments', function (Blueprint $table) {
            $table->dropUnique(['name', 'team_id']);

            $table->unique('name');
        });

        Schema::table('feature_types', function (Blueprint $table) {
            $table->dropUnique(['name', 'team_id']);

            $table->unique('name');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['name', 'team_id']);

            $table->unique('name');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique(['name', 'organization_id']);
        });
    }
};
