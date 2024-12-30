<?php

declare(strict_types=1);

use App\Models\Environment;
use App\Models\FeatureFlag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('environment_feature_flag', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FeatureFlag::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Environment::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('environment_id');
            $table->unique(['feature_flag_id', 'environment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environment_feature_flag');
    }
};
