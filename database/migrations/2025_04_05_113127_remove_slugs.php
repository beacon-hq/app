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
            $table->dropColumn('slug');
        });

        Schema::table('environments', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('feature_flags', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('feature_types', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
