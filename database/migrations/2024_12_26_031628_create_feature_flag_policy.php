<?php

declare(strict_types=1);

use App\Models\FeatureFlag;
use App\Models\Policy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('feature_flag_policy', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(FeatureFlag::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Policy::class)->constrained()->cascadeOnDelete();
            $table->integer('order');
            $table->jsonb('values');
            $table->timestamps();

            $table->index('feature_flag_id');
            $table->unique(['feature_flag_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flag_policy');
    }
};
