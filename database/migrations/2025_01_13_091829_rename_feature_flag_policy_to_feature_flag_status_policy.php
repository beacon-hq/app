<?php

declare(strict_types=1);

use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('feature_flag_policy', function (Blueprint $table) {
            $table->dropIndex('feature_flag_policy_feature_flag_id_index');
            $table->dropUnique(['feature_flag_id', 'order']);
            $table->foreignIdFor(FeatureFlagStatus::class)->constrained()->cascadeOnDelete();

            $table->rename('feature_flag_status_policy');
        });
    }

    public function down(): void
    {
        Schema::table('feature_flag_status_policy', function (Blueprint $table) {
            $table->dropForeignIdFor(FeatureFlagStatus::class);
            $table->dropColumn('feature_flag_status_id');
            $table->foreignIdFor(FeatureFlag::class);

            $table->rename('feature_flag_policy');
        });
    }
};
