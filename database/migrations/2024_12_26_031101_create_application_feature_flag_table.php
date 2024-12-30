<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\FeatureFlag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('application_feature_flag', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Application::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(FeatureFlag::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('application_id');
            $table->unique(['application_id', 'feature_flag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_feature_flag');
    }
};
