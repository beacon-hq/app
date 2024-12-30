<?php

declare(strict_types=1);

use App\Models\FeatureFlag;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('feature_flag_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FeatureFlag::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tag::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('feature_flag_id');
            $table->unique(['feature_flag_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flag_tag');
    }
};
