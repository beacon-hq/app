<?php

declare(strict_types=1);

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->jsonb('definition');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
