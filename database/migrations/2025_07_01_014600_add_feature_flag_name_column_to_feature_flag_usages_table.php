<?php

declare(strict_types=1);

use App\Models\FeatureFlagUsage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('feature_flag_usages', function (Blueprint $table) {
            $table->string('feature_flag_name')->nullable();
            $table->string('application_name')->nullable();
            $table->string('environment_name')->nullable();
            $table->ulid('feature_flag_id')->nullable()->change();
            $table->ulid('application_id')->nullable()->change();
            $table->ulid('environment_id')->nullable()->change();
        });

        FeatureFlagUsage::query()
            ->whereNull('feature_flag_name')
            ->get()
            ->each(function (FeatureFlagUsage $featureFlagUsage) {
                $featureFlagUsage->feature_flag_name = $featureFlagUsage->featureFlag->name;
                $featureFlagUsage->application_name = $featureFlagUsage->application?->name;
                $featureFlagUsage->environment_name = $featureFlagUsage->environment?->name;
                $featureFlagUsage->save();
            });

        Schema::table('feature_flag_usages', function (Blueprint $table) {
            $table->string('feature_flag_name')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('feature_flag_usages', function (Blueprint $table) {
            $table->dropColumn('feature_flag_name');
            $table->dropColumn('application_name');
            $table->dropColumn('environment_name');
            $table->ulid('feature_flag_id')->nullable(false)->change();
            $table->ulid('application_id')->nullable(false)->change();
            $table->ulid('environment_id')->nullable(false)->change();
        });
    }
};
