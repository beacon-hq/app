<?php

declare(strict_types=1);

use App\Models\FeatureType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('feature_types', function (Blueprint $table) {
            $table->boolean('is_default')->default(false);
            $table->index('is_default');
        });

        FeatureType::where('name', 'Release')->update(['is_default' => true]);
    }

    public function down(): void
    {
        Schema::table('feature_types', function (Blueprint $table) {
            $table->dropIndex(['is_default']);
            $table->dropColumn('is_default');
        });
    }
};
