<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique('applications_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('environments', function (Blueprint $table) {
            $table->dropUnique('environments_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('feature_flags', function (Blueprint $table) {
            $table->dropUnique('feature_flags_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('feature_types', function (Blueprint $table) {
            $table->dropUnique('feature_types_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropUnique('tenants_owner_id_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('policies', function (Blueprint $table) {
            $table->dropUnique('policies_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique('tags_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique('teams_tenant_id_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
