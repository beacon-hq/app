<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme')->nullable();
        });

        User::query()->update(['theme' => 'system']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('theme')->default('system')->nullable(false)->change();
        });
    }
};
