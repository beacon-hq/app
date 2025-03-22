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
        // Drop foreign keys first
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        // Rename tables
        Schema::rename('tenants', 'organizations');
        Schema::rename('tenant_user', 'organization_user');

        // Rename columns in pivot table
        Schema::table('organization_user', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'organization_id');
        });

        // Rename columns in teams table
        Schema::table('teams', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'organization_id');
        });

        // Add back foreign keys with new names
        Schema::table('organization_user', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first
        Schema::table('organization_user', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });

        // Rename tables back
        Schema::rename('organizations', 'tenants');
        Schema::rename('organization_user', 'tenant_user');

        // Rename columns back in pivot table
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'tenant_id');
        });

        // Rename columns back in teams table
        Schema::table('teams', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'tenant_id');
        });

        // Add back original foreign keys
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }
};
