<?php

declare(strict_types=1);

use App\Enums\UserStatus;
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
            $table->string('status')->nullable();
        });

        User::query()->update([
            'status' => UserStatus::ACTIVE,
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->nullable(false)->change();
        });
    }
};
