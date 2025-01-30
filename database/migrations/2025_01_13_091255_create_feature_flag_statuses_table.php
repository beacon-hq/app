<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('feature_flag_statuses', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignIdFor(Application::class)->constrained('applications')->cascadeOnDelete();
            $table->foreignIdFor(Environment::class)->constrained('environments')->cascadeOnDelete();
            $table->foreignIdFor(FeatureFlag::class)->constrained('feature_flags')->cascadeOnDelete();
            $table->boolean('status');

            $table->unique(['feature_flag_id', 'application_id', 'environment_id']);

            $table->timestamps();
        });

        Schema::create('feature_flag_feature_flag_status', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignIdFor(FeatureFlag::class)->constrained('feature_flags')->cascadeOnDelete();
            $table->foreignIdFor(FeatureFlagStatus::class)->constrained('feature_flag_statuses')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flag_feature_flag_status');
        Schema::dropIfExists('feature_flag_statuses');
    }
};
