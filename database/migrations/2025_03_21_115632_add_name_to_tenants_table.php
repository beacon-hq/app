<?php

declare(strict_types=1);

namespace App\Models {

    use Illuminate\Database\Eloquent\Concerns\HasUlids;
    use Illuminate\Database\Eloquent\Model;

    if (!class_exists(Tenant::class)) {
        class Tenant extends Model
        {
            use HasUlids;
        }
    }
}

namespace {

    use App\Models\Tenant;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class () extends Migration {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('name')->nullable();
            });

            Tenant::query()->update(['name' => 'Default']);

            Schema::table('tenants', function (Blueprint $table) {
                $table->string('name')->nullable(false)->change();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('tenants', function (Blueprint $table) {
                //
            });
        }
    };
}
