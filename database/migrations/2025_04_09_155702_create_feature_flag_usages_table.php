<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feature_flag_usages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(FeatureFlag::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Application::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Environment::class)->constrained()->cascadeOnDelete();
            $table->boolean('active');
            $table->json('value')->nullable();
            $table->timestamp('evaluated_at');

            // Index for faster querying
            $table->index(['team_id', 'feature_flag_id', 'application_id', 'environment_id']);
            $table->index('team_id', 'evaluated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_flag_usages');
    }
};
