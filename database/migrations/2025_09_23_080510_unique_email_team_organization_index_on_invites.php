<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->dropUnique(['email', 'organization_id']);
            $table->unique(['email', 'team_id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->dropUnique(['email', 'team_id', 'organization_id']);
            $table->unique(['email', 'organization_id']);
        });
    }
};
