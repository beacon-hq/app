<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('feature_types', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('temporary');
            $table->string('color')->default('#e3e3e3');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_types');
    }
};
